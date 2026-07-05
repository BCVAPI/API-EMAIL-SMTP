<?php
require __DIR__.'/functions.php';
$config = load_config();
$apiKeysFile = $config['API_KEYS_FILE'];

// Endpoint que procesa el formulario web y usa la API key
if ($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(405);
    echo "Método no permitido";
    exit;
}

$api_key = $_POST['api_key'] ?? '';
$keys = load_keys($apiKeysFile);
if (!isset($keys[$api_key]) || !$keys[$api_key]['active']){
    http_response_code(403);
    echo "API key inválida o no activada. Sigue las instrucciones en admin.php";
    exit;
}

$from_email = $_POST['from_email'] ?? '';
$from_name = $_POST['from_name'] ?? '';
$to_list = $_POST['to_list'] ?? '';
$cc = $_POST['cc'] ?? '';
$bcc = $_POST['bcc'] ?? '';
$subject = $_POST['subject'] ?? '';
$message = $_POST['message'] ?? '';

// Attachments handling
$attachments = [];
if (!empty($_FILES['attachments'])){
    foreach ($_FILES['attachments']['tmp_name'] as $i => $tmpName){
        if ($tmpName && is_uploaded_file($tmpName)){
            $attachments[] = [
                'tmp_name' => $tmpName,
                'name' => $_FILES['attachments']['name'][$i]
            ];
        }
    }
}

// Parse recipients
$recipients = preg_split('/[\r\n,;]+/', $to_list);
$recipients = array_map('trim', $recipients);
$recipients = array_filter($recipients);

$sent = 0; $failed = 0;
foreach ($recipients as $to){
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) { $failed++; continue; }
    $html = $message;
    $ok = send_mail_with_attachments($to, $subject, $html, $from_email, $from_name, $cc, $bcc, $attachments);
    if ($ok) $sent++; else $failed++;
    // Rate limit
    usleep($config['RATE_DELAY_MS'] * 1000);
}

echo "Envío terminado. Enviados: $sent. Fallidos: $failed.";
