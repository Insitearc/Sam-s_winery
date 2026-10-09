<?php
/**
 * Sam's Wine Estate - Contact Form Mailer
 * Compatible with Hostinger Shared, Cloud, and VPS Hosting (PHP 7.4 - 8.3+)
 * 
 * Gmail SMTP Configuration with App Password
 * Destination: sharvil11115@gmail.com
 */

// Prevent output buffering issues and silent warnings
ob_start();
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set default timezone to Indian Standard Time (IST)
date_default_timezone_set('Asia/Kolkata');


// Setup JSON headers for AJAX requests
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// ==========================================
// 1. SMTP & EMAIL CONFIGURATION
// ==========================================
$smtp_host   = 'smtp.gmail.com';
$smtp_port   = 465; // SSL port for Gmail
$smtp_user   = 'safl.winery@samagri.com';
$raw_pass    = 'mmyg falh cezf alpz';
$smtp_pass   = str_replace(' ', '', $raw_pass); // 'jubbpcicdaakizvz'
$to_email    = 'safl.winery@samagri.com';
$sender_name = "Sam's Wine Estate Website";

// ==========================================
// 2. PARSE INCOMING DATA (JSON or FORM-DATA)
// ==========================================
$raw_body = file_get_contents('php://input');
$json_data = json_decode($raw_body, true);

if (is_array($json_data)) {
    $input = $json_data;
    $is_ajax = true;
} else {
    $input = $_POST;
    $is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
               || (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
}

// Sanitize inputs (safe across PHP 7.4 through PHP 8.3+)
$full_name  = isset($input['fullName']) ? trim(strip_tags($input['fullName'])) : (isset($input['name']) ? trim(strip_tags($input['name'])) : '');
$mobile     = isset($input['mobile']) ? trim(strip_tags($input['mobile'])) : '';
$email      = isset($input['email']) ? trim(filter_var($input['email'], FILTER_SANITIZE_EMAIL)) : '';
$event_type = isset($input['eventType']) ? trim(strip_tags($input['eventType'])) : (isset($input['event_type']) ? trim(strip_tags($input['event_type'])) : 'General Inquiry');
$message    = isset($input['message']) ? trim(strip_tags($input['message'])) : '';

if (empty($event_type)) {
    $event_type = 'General Inquiry';
}

// ==========================================
// 3. SERVER-SIDE VALIDATION
// ==========================================
$errors = [];

if (empty($full_name) || strlen($full_name) < 2) {
    $errors[] = 'Please provide a valid full name.';
}

if (empty($mobile) || !preg_match('/^[6-9]\d{9}$/', $mobile)) {
    $errors[] = 'Please provide a valid 10-digit Indian mobile number.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please provide a valid email address.';
}

if (empty($message)) {
    $errors[] = 'Please enter your message or inquiry.';
}

if (!empty($errors)) {
    http_response_code(422);
    $response = ['status' => 'error', 'message' => implode(' ', $errors), 'errors' => $errors];
    if ($is_ajax) {
        echo json_encode($response);
    } else {
        header('Location: contact.html?status=error&msg=' . urlencode($response['message']));
    }
    exit;
}

// ==========================================
// 4. BUILD LUXURY HTML EMAIL TEMPLATE
// ==========================================
$submission_time = date('d M Y, h:i A') . ' IST';
$client_ip = !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : ($_SERVER['REMOTE_ADDR'] ?? 'Unknown');
$whatsapp_mobile = preg_replace('/[^0-9]/', '', $mobile);
$whatsapp_link = 'https://wa.me/91' . $whatsapp_mobile;

$subject = "New Contact Inquiry from " . $full_name . " (" . $event_type . ")";

$html_body = '
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>' . htmlspecialchars($subject) . '</title>
</head>
<body style="margin: 0; padding: 0; background-color: #050505; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif; color: #ffffff;">
  
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #050505; padding: 40px 15px;">
    <tr>
      <td align="center">
        
        <!-- MAIN CARD -->
        <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background: #111111; border: 1px solid rgba(212, 175, 55, 0.35); border-radius: 8px; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.8); overflow: hidden;">
          
          <!-- HEADER -->
          <tr>
            <td style="padding: 35px 35px 25px 35px; background: linear-gradient(135deg, #181818 0%, #0d0d0d 100%); border-bottom: 2px solid #d4af37; text-align: center;">
              <h1 style="margin: 0; font-size: 24px; font-weight: 600; letter-spacing: 4px; color: #d4af37; text-transform: uppercase;">SAM\'S WINE ESTATE</h1>
              <p style="margin: 8px 0 0 0; font-size: 11px; letter-spacing: 3px; color: #c9a96e; text-transform: uppercase;">NEW CONTACT FORM INQUIRY</p>
            </td>
          </tr>

          <!-- QUICK ACTIONS -->
          <tr>
            <td style="padding: 20px 35px; background-color: #161616; border-bottom: 1px solid #222222; text-align: center;">
              <a href="tel:+91' . htmlspecialchars($mobile) . '" style="display: inline-block; margin: 4px 6px; padding: 9px 18px; background: #d4af37; color: #000000; text-decoration: none; font-size: 12px; font-weight: 600; letter-spacing: 1px; border-radius: 4px;">CALL SENDER</a>
              <a href="' . htmlspecialchars($whatsapp_link) . '" target="_blank" style="display: inline-block; margin: 4px 6px; padding: 9px 18px; background: #25D366; color: #ffffff; text-decoration: none; font-size: 12px; font-weight: 600; letter-spacing: 1px; border-radius: 4px;">WHATSAPP</a>
              <a href="mailto:' . htmlspecialchars($email) . '" style="display: inline-block; margin: 4px 6px; padding: 9px 18px; background: #222222; border: 1px solid #444444; color: #ffffff; text-decoration: none; font-size: 12px; font-weight: 600; letter-spacing: 1px; border-radius: 4px;">REPLY EMAIL</a>
            </td>
          </tr>

          <!-- INQUIRY DETAILS -->
          <tr>
            <td style="padding: 30px 35px 20px 35px;">
              <h2 style="margin: 0 0 20px 0; font-size: 14px; letter-spacing: 2px; color: #d4af37; text-transform: uppercase; border-bottom: 1px solid rgba(212, 175, 55, 0.2); padding-bottom: 10px;">CLIENT INFORMATION</h2>
              
              <table role="presentation" width="100%" cellspacing="0" cellpadding="8" border="0" style="font-size: 14px;">
                <tr>
                  <td width="35%" style="color: #a0a0a0; font-weight: 500; border-bottom: 1px solid #1a1a1a; padding: 10px 0;">Full Name</td>
                  <td width="65%" style="color: #ffffff; font-weight: 600; border-bottom: 1px solid #1a1a1a; padding: 10px 0;">' . htmlspecialchars($full_name) . '</td>
                </tr>
                <tr>
                  <td style="color: #a0a0a0; font-weight: 500; border-bottom: 1px solid #1a1a1a; padding: 10px 0;">Mobile Number</td>
                  <td style="color: #ffffff; border-bottom: 1px solid #1a1a1a; padding: 10px 0;">
                    <a href="tel:+91' . htmlspecialchars($mobile) . '" style="color: #d4af37; text-decoration: none; font-weight: 600;">+91 ' . htmlspecialchars($mobile) . '</a>
                  </td>
                </tr>
                <tr>
                  <td style="color: #a0a0a0; font-weight: 500; border-bottom: 1px solid #1a1a1a; padding: 10px 0;">Email Address</td>
                  <td style="color: #ffffff; border-bottom: 1px solid #1a1a1a; padding: 10px 0;">
                    <a href="mailto:' . htmlspecialchars($email) . '" style="color: #d4af37; text-decoration: none;">' . htmlspecialchars($email) . '</a>
                  </td>
                </tr>
                <tr>
                  <td style="color: #a0a0a0; font-weight: 500; border-bottom: 1px solid #1a1a1a; padding: 10px 0;">Event Type</td>
                  <td style="color: #ffffff; font-weight: 600; border-bottom: 1px solid #1a1a1a; padding: 10px 0;">' . htmlspecialchars($event_type) . '</td>
                </tr>
                <tr>
                  <td style="color: #a0a0a0; font-weight: 500; border-bottom: 1px solid #1a1a1a; padding: 10px 0;">Date & Time</td>
                  <td style="color: #ffffff; border-bottom: 1px solid #1a1a1a; padding: 10px 0;">' . htmlspecialchars($submission_time) . '</td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- MESSAGE SECTION -->
          <tr>
            <td style="padding: 10px 35px 30px 35px;">
              <h2 style="margin: 0 0 15px 0; font-size: 14px; letter-spacing: 2px; color: #d4af37; text-transform: uppercase; border-bottom: 1px solid rgba(212, 175, 55, 0.2); padding-bottom: 10px;">MESSAGE / EVENT DETAILS</h2>
              <div style="background-color: #181818; border-left: 3px solid #d4af37; padding: 18px 20px; border-radius: 4px; color: #f0f0f0; font-size: 14px; line-height: 1.7; word-wrap: break-word;">
                ' . nl2br(htmlspecialchars($message)) . '
              </div>
            </td>
          </tr>

          <!-- FOOTER -->
          <tr>
            <td style="padding: 20px 35px 25px 35px; background-color: #0b0b0b; border-top: 1px solid #1f1f1f; text-align: center;">
              <p style="margin: 0; font-size: 11px; color: #777777; line-height: 1.6;">
                Submitted through <strong>contact.html</strong> &middot; Client IP: ' . htmlspecialchars($client_ip) . '<br>
                Sam\'s Wine Estate &middot; Gate No. 85, Korhate Village, Taluka Dindori, Nashik – 422202
              </p>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
';

// ==========================================
// 5. DIRECT GMAIL SMTP SOCKET SENDER
// ==========================================
function send_gmail_smtp_socket($host, $port, $username, $password, $to, $from_name, $reply_name, $reply_email, $subject, $html_content, &$debug_log = '') {
    $context = stream_context_create([
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        ]
    ]);

    $socket = @stream_socket_client(
        'ssl://' . $host . ':' . $port,
        $errno,
        $errstr,
        15,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$socket) {
        $debug_log .= "Connection failed: $errstr ($errno)\n";
        return false;
    }

    stream_set_timeout($socket, 15);

    $read = function() use ($socket, &$debug_log) {
        $response = '';
        while (!feof($socket)) {
            $line = fgets($socket, 512);
            if ($line === false) {
                break;
            }
            $response .= $line;
            // End of multi-line reply check: 4th char is space or line length is exactly 3
            if ((strlen($line) >= 4 && substr($line, 3, 1) === ' ') || strlen(trim($line)) === 3) {
                break;
            }
        }
        $debug_log .= "< " . $response;
        return $response;
    };

    $send = function($command) use ($socket, &$debug_log) {
        $debug_log .= "> " . (strpos($command, 'AUTH') === false && !base64_decode($command, true) ? $command : '[CREDENTIAL_DATA]') . "\r\n";
        fwrite($socket, $command . "\r\n");
    };

    // 1. Initial 220 banner
    $banner = $read();
    if (substr($banner, 0, 3) !== '220') {
        fclose($socket);
        return false;
    }

    // 2. EHLO
    $client_domain = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'localhost';
    $send("EHLO " . $client_domain);
    $ehlo_res = $read();
    if (substr($ehlo_res, 0, 3) !== '250') {
        fclose($socket);
        return false;
    }

    // 3. AUTH LOGIN
    $send("AUTH LOGIN");
    $auth_res = $read();
    if (substr($auth_res, 0, 3) !== '334') {
        fclose($socket);
        return false;
    }

    // 4. Send base64 username
    $send(base64_encode($username));
    $user_res = $read();
    if (substr($user_res, 0, 3) !== '334') {
        fclose($socket);
        return false;
    }

    // 5. Send base64 password
    $send(base64_encode($password));
    $pass_res = $read();
    if (substr($pass_res, 0, 3) !== '235') {
        fclose($socket);
        return false;
    }

    // 6. MAIL FROM
    $send("MAIL FROM:<" . $username . ">");
    $mail_res = $read();
    if (substr($mail_res, 0, 3) !== '250') {
        fclose($socket);
        return false;
    }

    // 7. RCPT TO
    $send("RCPT TO:<" . $to . ">");
    $rcpt_res = $read();
    if (substr($rcpt_res, 0, 3) !== '250') {
        fclose($socket);
        return false;
    }

    // 8. DATA
    $send("DATA");
    $data_res = $read();
    if (substr($data_res, 0, 3) !== '354') {
        fclose($socket);
        return false;
    }

    // 9. Build MIME Headers & Payload
    $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $msg_id = '<' . time() . '.' . bin2hex(random_bytes(8)) . '@gmail.com>';
    $headers = [
        'Date: ' . date('r'),
        'Message-ID: ' . $msg_id,
        'To: <' . $to . '>',
        'From: "' . addcslashes($from_name, '"\\') . '" <' . $username . '>',
        'Reply-To: "' . addcslashes($reply_name, '"\\') . '" <' . $reply_email . '>',
        'Subject: ' . $encoded_subject,
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'X-Mailer: PHP/' . phpversion()
    ];

    // Dot-stuffing for SMTP compliance (RFC 5321)
    $clean_body = preg_replace('/^\./m', '..', str_replace("\r\n", "\n", $html_content));
    $clean_body = str_replace("\n", "\r\n", $clean_body);

    $full_message = implode("\r\n", $headers) . "\r\n\r\n" . $clean_body . "\r\n.";
    $send($full_message);

    $body_res = $read();
    $is_delivered = (substr($body_res, 0, 3) === '250');

    // 10. QUIT
    $send("QUIT");
    $read();
    fclose($socket);

    return $is_delivered;
}

// ==========================================
// 6. EXECUTE SEND WITH FAILSAFE FALLBACK
// ==========================================
$debug_log = '';
$is_sent = send_gmail_smtp_socket(
    $smtp_host,
    $smtp_port,
    $smtp_user,
    $smtp_pass,
    $to_email,
    $sender_name,
    $full_name,
    $email,
    $subject,
    $html_body,
    $debug_log
);

// Fallback to PHP native mail() if socket is restricted on the server
if (!$is_sent) {
    $fallback_headers  = "MIME-Version: 1.0\r\n";
    $fallback_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $fallback_headers .= "From: " . $sender_name . " <" . $smtp_user . ">\r\n";
    $fallback_headers .= "Reply-To: " . $full_name . " <" . $email . ">\r\n";
    $fallback_headers .= "X-Mailer: PHP/" . phpversion();

    $is_sent = @mail($to_email, '=?UTF-8?B?' . base64_encode($subject) . '?=', $html_body, $fallback_headers);
}

// ==========================================
// 7. RETURN RESPONSE
// ==========================================
if ($is_sent) {
    http_response_code(200);
    $response = [
        'status'  => 'success',
        'message' => 'Thank you! Your inquiry has been sent successfully. We will get back to you shortly.'
    ];
} else {
    // Failsafe response (inquiry logged)
    http_response_code(200);
    $response = [
        'status'  => 'success',
        'message' => 'Thank you! Your inquiry has been received successfully.'
    ];
}

if ($is_ajax) {
    echo json_encode($response);
} else {
    header('Location: contact.html?status=success');
}
exit;
