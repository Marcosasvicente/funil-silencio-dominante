<?php
// Conexão com o banco de dados via Variáveis de Ambiente do Railway
$host = getenv('MYSQLHOST') ?: 'localhost';
$dbname = getenv('MYSQLDATABASE') ?: 'seu_banco';
$user = getenv('MYSQLUSER') ?: 'seu_usuario';
$pass = getenv('MYSQLPASSWORD') ?: 'sua_senha';
$port = getenv('MYSQLPORT') ?: '3306';

$mensagem = "";
$status = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if ($nome && $email) {
        try {
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Verifica se o lead já está no sistema
            $stmt = $pdo->prepare("SELECT id FROM leads WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $mensagem = "Você já está cadastrado! Verifique sua caixa de entrada.";
                $status = "error";
            } else {
                // Cadastra o lead e define o dia como 0 (Envio imediato)
                $stmt = $pdo->prepare("INSERT INTO leads (nome, email, dia_atual) VALUES (?, ?, 0)");
                $stmt->execute([$nome, $email]);

                // Chama o motor de envio que criamos no passo anterior
                include_once 'enviador_funcoes.php';
                enviarEmailSequencia($nome, $email, 0);

                $mensagem = "Sucesso! O eBook foi enviado para o seu e-mail agora mesmo.";
                $status = "success";
            }
        } catch (PDOException $e) {
            $mensagem = "Erro de conexão. Tente novamente em instantes.";
            $status = "error";
        }
    } else {
        $mensagem = "Preencha os campos corretamente.";
        $status = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>O Poder do Silêncio Dominante | Download Grátis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: radial-gradient(circle at center, #1a1a1a 0%, #000 100%); color: #e0e0e0; }
        .gold-border { border-color: #d4af37; }
        .gold-text { color: #d4af37; }
        .gold-btn { background: linear-gradient(90deg, #d4af37 0%, #f2d472 100%); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">

    <div class="max-w-5xl w-full grid md:grid-cols-2 gap-12 items-center bg-black/40 p-8 rounded-3xl border border-white/10 backdrop-blur-sm">
        
        <div class="flex justify-center">
            <div class="relative group">
                <div class="absolute -inset-1 bg-gold rounded-lg blur opacity-25 group-hover:opacity-50 transition duration-1000"></div>
                <img src="1001158764.png" alt="Capa do eBook" class="relative rounded-lg shadow-2xl w-72 md:w-80 transform hover:scale-105 transition duration-500">
            </div>
        </div>

        <div>
            <h1 class="text-4xl md:text-5xl font-black leading-tight text-white mb-6">
                Domine a Arte do <span class="gold-text uppercase">Silêncio Estratégico</span>
            </h1>
            
            <p class="text-lg text-gray-400 mb-8 leading-relaxed">
                Descubra como o silêncio pode ser sua ferramenta mais poderosa para recuperar a autoridade e o valor em qualquer relação. Baixe agora o guia definitivo.
            </p>

            <?php if ($mensagem): ?>
                <div class="mb-6 p-4 rounded-lg text-sm border <?php echo $status === 'success' ? 'bg-green-500/10 border-green-500/50 text-green-400' : 'bg-red-500/10 border-red-500/50 text-red-400'; ?>">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST" class="space-y-4">
                <input type="text" name="nome" placeholder="Seu primeiro nome" required 
                       class="w-full p-4 bg-white/5 border border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-yellow-600 transition text-white">
                
                <input type="email" name="email" placeholder="Seu melhor e-mail" required 
                       class="w-full p-4 bg-white/5 border border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-yellow-600 transition text-white">
                
                <button type="submit" class="gold-btn w-full p-4 rounded-xl text-black font-black uppercase tracking-widest hover:brightness-110 transition shadow-lg shadow-yellow-600/20">
                    Quero o Livro Agora
                </button>
            </form>
            
            <p class="mt-6 text-center text-xs text-gray-600 uppercase tracking-tighter">
                🔒 Acesso imediato e 100% gratuito.
            </p>
        </div>
    </div>

</body>
</html>

