<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// O Railway cria essa pasta automaticamente graças ao composer.json
require 'vendor/autoload.php';

function enviarEmailSequencia($nome, $email, $dia) {
    // Aponta para a pasta /emails e busca o arquivo do dia correspondente (ex: 0.php, 1.php...)
    $arquivoEmail = __DIR__ . "/emails/{$dia}.php";

    if (!file_exists($arquivoEmail)) {
        return false; // Se o arquivo do dia ainda não existir, ignora silenciosamente para não travar o sistema
    }

    // Captura o conteúdo do arquivo PHP do e-mail sem exibi-lo na tela
    ob_start();
    include $arquivoEmail;
    $conteudoBruto = ob_get_clean();

    // Substitui a tag {nome} pelo nome real do lead cadastrado
    $corpoEmail = str_replace('{nome}', $nome, $conteudoBruto);

    // Definição dos Assuntos. Aqui você define o título que aparece na caixa de entrada para cada dia:
    $assuntos = [
        0 => "Aqui está o seu acesso: O Poder do Silêncio Dominante",
        1 => "O primeiro erro estratégico que você deve evitar hoje",
        2 => "A psicologia por trás do distanciamento",
        3 => "O que o silêncio faz na mente de quem ignora você?",
        // Você pode continuar adicionando os assuntos dos dias seguintes aqui...
    ];
    
    // Se o dia não estiver listado acima, ele usa este assunto padrão automático
    $assunto = isset($assuntos[$dia]) ? $assuntos[$dia] : "Sua atualização diária - Estratégia Masculina";

    $mail = new PHPMailer(true);

    try {
        // Configurações do Servidor SMTP (Buscadas das variáveis de ambiente que configuraremos no Railway)
        $mail->isSMTP();
        $mail->Host       = getenv('SMTP_HOST'); 
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USER'); 
        $mail->Password   = getenv('SMTP_PASS'); 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = getenv('SMTP_PORT') ?: 587;
        $mail->CharSet    = 'UTF-8';

        // Quem está enviando o e-mail
        $mail->setFrom(getenv('SMTP_FROM_EMAIL'), getenv('SMTP_FROM_NAME'));
        
        // Quem vai receber (O Lead)
        $mail->addAddress($email, $nome);

        // Define que o e-mail aceita formatação HTML (links, negritos, cores)
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $corpoEmail;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Retorna falso caso ocorra algum erro no envio (como falha de conexão com o SMTP)
        return false;
    }
}

