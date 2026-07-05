<?php
// API endpoint JSON para envío. Requiere API key activo.
header('Content-Type: application/json; charset=utf-8');
require __DIR__.'/functions.php';
$config = load_config();
$apiKeysFile = $config['API_KEYS_FILE'];

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error'=>'JSON inválido']);
    exit;
}

$api_key = $input['api_key'] ?? '';
$keys = load_keys($apiKeysFile);
if (!isset($keys[$api_key]) || !$keys[$api_key]['active']){
    http_response_code(402);
    echo json_encode(['error'=>'API key inválida o no activada. Paga para activar.']);
    exit;
}

$from_email = $input['from_email'] ?? '';
$from_name = $input['from_name'] ?? '';
$to_list = $input['to_list'] ?? '';
$cc = $input['cc'] ?? '';
$bcc = $input['bcc'] ?? '';
$subject = $input['subject'] ?? '';
$message = $input['message'] ?? '';
$attachments = $input['attachments'] ?? []; // attachments via URLs no implementado

if (empty($to_list) || empty($from_email) || empty($subject) || empty($message)){
    http_response_code(400);
    echo json_encode(['error'=>'Faltan campos obligatorios']);
    exit;
}

$recipients = preg_split('/[\r\n,;]+/', $to_list);
$recipients = array_map('trim', $recipients);
$recipients = array_filter($recipients);

$sent = 0; $failed = 0;
foreach ($recipients as $to){
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $tn = normalize_email_for_sending($to);
        if (!$tn) { $failed++; continue; }
        $to = $tn;
    }
    $html = $message;
    // No attachments handling in API for now (posible mejora)
    $ok = send_mail_with_attachments($to, $subject, $html, $from_email, $from_name, $cc, $bcc, []);
    if ($ok) $sent++; else $failed++;
    usleep($config['RATE_DELAY_MS'] * 1000);
}

echo json_encode(['sent'=>$sent,'failed'=>$failed]);
