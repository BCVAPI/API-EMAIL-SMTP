<?php
// Funciones auxiliares para envío con mail() y manejo de attachments

function load_config(){
    return include __DIR__ . '/config.php';
}

function send_mail_with_attachments($to, $subject, $htmlMessage, $fromEmail, $fromName, $cc = '', $bcc = '', $attachments = []){
    $eol = PHP_EOL;
    $separator = md5(time());

    // Headers
    $headers = 'From: ' . filter_var($fromName, FILTER_SANITIZE_STRING) . " <".filter_var($fromEmail, FILTER_SANITIZE_EMAIL).">".$eol;
    if (!empty($cc)) $headers .= 'Cc: '.$cc.$eol;
    if (!empty($bcc)) $headers .= 'Bcc: '.$bcc.$eol;
    $headers .= 'MIME-Version: 1.0'.$eol;
    $headers .= 'Content-Type: multipart/mixed; boundary="' . $separator . '"'.$eol;

    // Message Body
    $body = "--".$separator.$eol;
    $body .= 'Content-Type: text/html; charset="UTF-8"'.$eol;
    $body .= 'Content-Transfer-Encoding: 7bit'.$eol.$eol;
    $body .= $htmlMessage.$eol.$eol;

    // Attachments
    foreach ($attachments as $file){
        if (!file_exists($file['tmp_name'])) continue;
        $fileContent = chunk_split(base64_encode(file_get_contents($file['tmp_name'])));
        $body .= "--".$separator.$eol;
        $body .= 'Content-Type: application/octet-stream; name="'.basename($file['name']).'"'.$eol;
        $body .= 'Content-Transfer-Encoding: base64'.$eol;
        $body .= 'Content-Disposition: attachment; filename="'.basename($file['name']).'"'.$eol.$eol;
        $body .= $fileContent.$eol.$eol;
    }

    $body .= "--".$separator."--".$eol;

    // Use mail() and return boolean
    return mail($to, $subject, $body, $headers);
}

function generate_api_key($len = 48){
    return bin2hex(random_bytes($len/2));
}

function load_keys($file){
    if (!file_exists($file)) return [];
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function save_keys($file, $data){
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

