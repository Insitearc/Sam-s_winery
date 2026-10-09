<?php
/**
 * Sam's Fruit Wines - Central Mail Configuration & Mailer Function
 * Supports direct SSL SMTP communication with attachments.
 */

define('MAIL_SMTP_HOST', 'smtp.gmail.com');
define('MAIL_SMTP_PORT', 465);
define('MAIL_SMTP_USER', 'safl.wineryshop@samagri.com');
define('MAIL_SMTP_PASS', 'fmcf bjhj hshb ochg');
define('MAIL_SENDER_NAME', "Sam's Fruit Wines");
define('MAIL_ADMIN_EMAILS', [
    'safl.wineryshop@samagri.com',
    'dhonevaibhav785@gmail.com'
]);

/**
 * Send an email via secure SMTP socket connection.
 * Supports HTML body and file attachments.
 *
 * @param array|string $recipients Email address(es)
 * @param string $subject Email subject
 * @param string $html_body HTML formatted body
 * @param array $attachments Array of file paths to attach: [['path' => '...', 'name' => '...']]
 * @param string|null $reply_to Optional reply-to address
 * @return bool True on success, false on failure
 */
function send_smtp_email($recipients, $subject, $html_body, $attachments = [], $reply_to = null) {
    if (!is_array($recipients)) {
        $recipients = [$recipients];
    }

    $valid_recipients = [];
    foreach ($recipients as $email) {
        $clean = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        if ($clean) {
            $valid_recipients[] = $clean;
        }
    }

    if (empty($valid_recipients)) {
        error_log("Mail error: No valid recipients provided.");
        return false;
    }

    $socket = @fsockopen(
        'ssl://' . MAIL_SMTP_HOST,
        MAIL_SMTP_PORT,
        $errno,
        $errstr,
        25
    );

    if (!$socket) {
        error_log("SMTP Connection failed: $errstr ($errno)");
        return false;
    }

    $read = function() use ($socket) {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') {
                break;
            }
        }
        return $response;
    };

    $send = function($command) use ($socket) {
        fputs($socket, $command . "\r\n");
    };

    $read();
    $send("EHLO localhost");
    $read();

    $send("AUTH LOGIN");
    $read();
    $send(base64_encode(MAIL_SMTP_USER));
    $read();
    $send(base64_encode(MAIL_SMTP_PASS));
    $auth_res = $read();

    if (strpos($auth_res, '235') === false) {
        error_log("SMTP Authentication failed: " . $auth_res);
        fclose($socket);
        return false;
    }

    $send("MAIL FROM:<" . MAIL_SMTP_USER . ">");
    $read();

    foreach ($valid_recipients as $to) {
        $send("RCPT TO:<" . $to . ">");
        $rcpt_res = $read();
        if (strpos($rcpt_res, '250') === false && strpos($rcpt_res, '251') === false) {
            error_log("SMTP RCPT failed for $to: " . $rcpt_res);
        }
    }

    $send("DATA");
    $read();

    $boundary_rel = "==_Part_Rel_" . md5(uniqid(time(), true));
    $boundary_alt = "==_Part_Alt_" . md5(uniqid(time(), true));

    $reply_header = $reply_to ? "Reply-To: <$reply_to>\r\n" : "";

    $headers = "From: " . MAIL_SENDER_NAME . " <" . MAIL_SMTP_USER . ">\r\n";
    $headers .= "To: " . implode(', ', $valid_recipients) . "\r\n";
    $headers .= $reply_header;
    $headers .= "Subject: " . $subject . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";

    if (!empty($attachments)) {
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary_rel\"\r\n";
        
        $body = "--$boundary_rel\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($html_body)) . "\r\n";

        foreach ($attachments as $att) {
            $path = is_array($att) ? $att['path'] : $att;
            $name = is_array($att) && !empty($att['name']) ? $att['name'] : basename($path);

            if (file_exists($path)) {
                $file_content = file_get_contents($path);
                $mime_type = mime_content_type($path) ?: 'application/octet-stream';

                $body .= "--$boundary_rel\r\n";
                $body .= "Content-Type: $mime_type; name=\"$name\"\r\n";
                $body .= "Content-Disposition: attachment; filename=\"$name\"\r\n";
                $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
                $body .= chunk_split(base64_encode($file_content)) . "\r\n";
            }
        }
        $body .= "--$boundary_rel--\r\n";
    } else {
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Content-Transfer-Encoding: base64\r\n";
        $body = chunk_split(base64_encode($html_body));
    }

    $send($headers . "\r\n" . $body . "\r\n.");
    $final_res = $read();

    $send("QUIT");
    fclose($socket);

    return strpos($final_res, '250') !== false;
}
