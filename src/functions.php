<?php
// Funciones auxiliares para envío con mail() y manejo de attachments

function load_config(){
    return include __DIR__ . '/config.php';
}

// Normalize and validate an email address, supporting IDN (punycode) for the domain part
function normalize_email_for_sending(string $email){
    $email = trim($email);
    // prevent header injection
    $email = str_replace(["\r","\n"], '', $email);
    if (!strpos($email, '@')) return false;
    list($local, $domain) = explode('@', $email, 2);
    // Convert domain to ASCII/Punycode if intl is available
    if (function_exists('idn_to_ascii')) {
        $variant = defined('INTL_IDNA_VARIANT_UTS46') ? INTL_IDNA_VARIANT_UTS46 : INTL_IDNA_VARIANT_2003;
        $domain_ascii = @idn_to_ascii($domain, IDNA_DEFAULT, $variant);
        if ($domain_ascii !== false && $domain_ascii !== null) {
            $domain = $domain_ascii;
        }
    }
    $candidate = $local . '@' . $domain;
    return filter_var($candidate, FILTER_VALIDATE_EMAIL) ? $candidate : false;
}

// Build a safe From header with MIME-encoded display name when needed
function build_from_header(string $email, string $name = ''){
    $emailNorm = normalize_email_for_sending($email);
    if (!$emailNorm) return false;
    $name = trim(str_replace(["\r","\n"], '', $name));
    if ($name !== '') {
        // Ensure mbstring is available for encoding
        if (!function_exists('mb_encode_mimeheader')) {
            // fallback: remove non-ascii
            $safeName = preg_replace('/[^\x20-\x7E]/', '', $name);
            $encoded = $safeName;
        } else {
            $encoded = mb_encode_mimeheader($name, 'UTF-8', 'B');
        }
        return sprintf('%s <%s>', $encoded, $emailNorm);
    }
    return $emailNorm;
}

// Sanitize header values (Cc, Bcc) to avoid CRLF injection and keep a comma-separated list
function sanitize_address_list(string $list){
    $list = trim($list);
    $list = str_replace(["\r","\n"], ' ', $list);
    // split on common separators and rejoin
    $parts = preg_split('/[;,\s]+/', $list);
    $parts = array_filter(array_map('trim', $parts));
    $valid = [];
    foreach ($parts as $p){
        if (filter_var($p, FILTER_VALIDATE_EMAIL)) $valid[] = $p;
        else {
            // try normalize (IDN domains)
            $n = normalize_email_for_sending($p);
            if ($n) $valid[] = $n;
        }
    }
    return implode(', ', $valid);
}

function send_mail_with_attachments($to, $subject, $htmlMessage, $fromEmail, $fromName, $cc = '', $bcc = '', $attachments = []){
    $eol = PHP_EOL;
    $separator = md5(time());

    // Validate and normalize recipient
    $toNorm = normalize_email_for_sending($to);
    if (!$toNorm) return false;

    // Build From header
    $fromHeader = build_from_header($fromEmail, $fromName);
    if (!$fromHeader) return false;

    // Sanitize CC/BCC lists
    $ccList = sanitize_address_list($cc);
    $bccList = sanitize_address_list($bcc);

    // Headers
    $headers = 'From: ' . $fromHeader . $eol;
    if (!empty($ccList)) $headers .= 'Cc: '.$ccList.$eol;
    if (!empty($bccList)) $headers .= 'Bcc: '.$bccList.$eol;
    $headers .= 'MIME-Version: 1.0'.$eol;
    $headers .= 'Content-Type: multipart/mixed; boundary="' . $separator . '"'.$eol;

    // Message Body
    $body = "--".$separator.$eol;
    $body .= 'Content-Type: text/html; charset="UTF-8"'.$eol;
    $body .= 'Content-Transfer-Encoding: 7bit'.$eol.$eol;
    $body .= $htmlMessage.$eol.$eol;

    // Attachments
    foreach ($attachments as $file){
        if (!isset($file['tmp_name']) || !file_exists($file['tmp_name'])) continue;
        $fileContent = chunk_split(base64_encode(file_get_contents($file['tmp_name'])));
        $body .= "--".$separator.$eol;
        $body .= 'Content-Type: application/octet-stream; name="'.basename($file['name']).'"'.$eol;
        $body .= 'Content-Transfer-Encoding: base64'.$eol;
        $body .= 'Content-Disposition: attachment; filename="'.basename($file['name']).'"'.$eol.$eol;
        $body .= $fileContent.$eol.$eol;
    }

    $body .= "--".$separator."--".$eol;

    // Use mail() and return boolean
    return mail($toNorm, $subject, $body, $headers);
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

?>
