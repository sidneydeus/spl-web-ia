<?php
// process-form.php

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Método não permitido."
    ]);
    exit;
}

// Sanitização
$name    = htmlspecialchars(trim($_POST['name'] ?? ''));
$email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone   = htmlspecialchars(trim($_POST['phone'] ?? ''));
$service = htmlspecialchars(trim($_POST['service'] ?? ''));
$message = htmlspecialchars(trim($_POST['message'] ?? ''));

// Validações
if (empty($name) || empty($email) || empty($message)) {
    echo json_encode([
        "success" => false,
        "message" => "Preencha os campos obrigatórios."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "E-mail inválido."
    ]);
    exit;
}

// Destinatário
$to = "contato@splweb.com.br";

// Assunto
$subject = "Novo contato do site";

// Corpo do e-mail
$body = "
Novo formulário recebido:

Nome: {$name}
E-mail: {$email}
Telefone: {$phone}
Serviço: {$service}

Mensagem:
{$message}
";

// Headers
$headers = "From: SPL WEB <contato@splweb.com.br>\r\n";
$headers .= "Reply-To: {$email}\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Envio
if (@mail($to, $subject, $body, $headers)) {

    echo json_encode([
        "success" => true,
        "message" => "Mensagem enviada com sucesso!"
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Erro ao enviar a mensagem."
    ]);

}
?>
