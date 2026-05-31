<?php
// Script de Automação Diária
include_once 'enviador_funcoes.php';

// Conexão com o banco (Railway)
$host = getenv('MYSQLHOST') ?: 'localhost';
$dbname = getenv('MYSQLDATABASE') ?: 'seu_banco';
$user = getenv('MYSQLUSER') ?: 'seu_usuario';
$pass = getenv('MYSQLPASSWORD') ?: 'sua_senha';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Seleciona todos os leads que estão entre o Dia 1 e o Dia 30
    // O Dia 0 não entra aqui porque é enviado no momento do cadastro (index.php)
    $stmt = $pdo->query("SELECT id, nome, email, dia_atual FROM leads WHERE dia_atual >= 1 AND dia_atual <= 30");
    $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($leads as $lead) {
        // Tenta enviar o e-mail do dia correspondente
        $enviado = enviarEmailSequencia($lead['nome'], $lead['email'], $lead['dia_atual']);

        if ($enviado) {
            // Se o e-mail foi enviado com sucesso, avança o lead para o próximo dia
            $proximoDia = $lead['dia_atual'] + 1;
            $update = $pdo->prepare("UPDATE leads SET dia_atual = ? WHERE id = ?");
            $update->execute([$proximoDia, $lead['id']]);
        }
    }

    // 2. Lógica de Transição:
    // Pega quem acabou de entrar (Dia 0) e marca como Dia 1 para o Cron de amanhã
    $pdo->query("UPDATE leads SET dia_atual = 1 WHERE dia_atual = 0");

    echo "Automação finalizada: Sequência processada.";

} catch (PDOException $e) {
    // Log de erro simples
    error_log("Erro no Cron: " . $e->getMessage());
    echo "Erro ao processar disparos.";
}

