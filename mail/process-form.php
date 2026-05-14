<?php
// process-form.php

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function env_value(string $key, string $default = ''): string
{
    $value = getenv($key);

    if ($value === false || $value === null || $value === '') {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    return is_string($value) ? trim($value) : $default;
}

function json_response(bool $success, string $message, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    json_response(false, 'Método não permitido.', 405);
}

// Sanitização
$name = trim(strip_tags($_POST['name'] ?? ''));
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone = trim(strip_tags($_POST['phone'] ?? ''));
$service = trim(strip_tags($_POST['service'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

// Validações
if (empty($name) || empty($email) || empty($message)) {
    json_response(false, 'Preencha os campos obrigatórios.', 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'E-mail inválido.', 400);
}

// SMTP configuration
$smtpHost = 'smtp.hostinger.com';
$smtpPort = 587;
$smtpUsername = 'contato@splweb.com.br';
$smtpPassword = 'Pray1ndt2021#';
$smtpEncryption = PHPMailer::ENCRYPTION_STARTTLS;
$fromEmail = 'contato@splweb.com.br';
$fromName = 'SPL WEB';
$to = 'contato@splweb.com.br';

if ($smtpHost === '' || $smtpUsername === '' || $smtpPassword === '') {
    json_response(false, 'SMTP não configurado. Defina SMTP_HOST, SMTP_USERNAME e SMTP_PASSWORD.', 500);
}

$body = <<<TEXT
Novo formulário recebido:

Nome: {$name}
E-mail: {$email}
Telefone: {$phone}
Serviço: {$service}

Mensagem:
{$message}
TEXT;

$mailer = new PHPMailer(true);

try {
    $mailer->CharSet = PHPMailer::CHARSET_UTF8;
    $mailer->isSMTP();
    $mailer->Host = $smtpHost;
    $mailer->SMTPAuth = true;
    $mailer->Username = $smtpUsername;
    $mailer->Password = $smtpPassword;
    $mailer->Port = $smtpPort;

    if ($smtpEncryption === PHPMailer::ENCRYPTION_SMTPS || $smtpEncryption === PHPMailer::ENCRYPTION_STARTTLS) {
        $mailer->SMTPSecure = $smtpEncryption;
    }

    $mailer->setFrom($fromEmail, $fromName);
    $mailer->addAddress($to);
    $mailer->addReplyTo($email, $name);
    $mailer->Subject = 'Novo contato do site';
    $mailer->isHTML(false);
    $mailer->Body = $body;

    $mailer->send();

    json_response(true, 'Mensagem enviada com sucesso!');
} catch (Exception $e) {
    json_response(false, 'Erro ao enviar a mensagem via SMTP.', 500);
}
?>
