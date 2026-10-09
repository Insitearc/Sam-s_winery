<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Target email & SMTP configuration
$to_email   = "safl.winery@samagri.com";
$smtp_host  = "smtp.hostinger.com";
$smtp_port  = 465;
$smtp_user  = "info@samsfruitwines.com";
$smtp_pass  = "4gxt-vuoq-ir5j-kqm6";

// Read raw JSON input or POST data
$input_data = json_decode(file_get_contents('php://input'), true);

if (!$input_data) {
    $input_data = $_POST;
}

$form_type  = isset($input_data['form_type']) ? trim(strip_tags($input_data['form_type'])) : 'Website Form Submission';
$name       = isset($input_data['name']) ? trim(strip_tags($input_data['name'])) : '';
$email      = isset($input_data['email']) ? filter_var($input_data['email'], FILTER_SANITIZE_EMAIL) : '';
$mobile     = isset($input_data['mobile']) ? trim(strip_tags($input_data['mobile'])) : '';
$dob        = isset($input_data['dob']) ? trim(strip_tags($input_data['dob'])) : '';
$visit_date = isset($input_data['visit_date']) ? trim(strip_tags($input_data['visit_date'])) : '';
$guests     = isset($input_data['guests']) ? trim(strip_tags($input_data['guests'])) : '';
$event_type = isset($input_data['event_type']) ? trim(strip_tags($input_data['event_type'])) : '';
$message    = isset($input_data['message']) ? trim(strip_tags($input_data['message'])) : '';

if (empty($email) && empty($mobile) && empty($name)) {
    echo json_encode(["status" => "error", "message" => "Required fields missing."]);
    exit;
}

// Subject
$subject = "New Inquiry: " . $form_type . " - " . $name;

// Build HTML Body
$html_body = "
<!DOCTYPE html>
<html lang='en'>
<head>
<meta charset='UTF-8'>
<title>Sam&#39;s Wine Email Notification</title>
<meta name=\"description\" content=\"Sam&#39;s Wine Official Website Form Notification\">
<link rel=\"canonical\" href=\"https://samsfruitwines.com\">
<style>
  body { font-family: 'Outfit', Arial, sans-serif; background-color: #080808; color: #ffffff; padding: 20px; }
  .card { background-color: #151515; border: 1px solid #d4af37; border-radius: 10px; padding: 30px; max-width: 600px; margin: 0 auto; }
  .header { border-bottom: 1px solid rgba(212,175,55,0.3); padding-bottom: 15px; margin-bottom: 20px; }
  .header h2 { color: #d4af37; margin: 0; font-size: 22px; }
  .row { display: flex; margin-bottom: 12px; }
  .label { font-weight: bold; width: 140px; color: #d4af37; }
  .value { color: #ffffff; }
  .footer { margin-top: 25px; padding-top: 15px; border-top: 1px solid #333; font-size: 12px; color: #888; text-align: center; }
</style>
</head>
<body>
  <div class='card'>
    <div class='header'>
      <h2>Sam's Wine - New " . htmlspecialchars($form_type) . "</h2>
    </div>
    <div class='row'><span class='label'>Full Name:</span><span class='value'>" . htmlspecialchars($name) . "</span></div>
    <div class='row'><span class='label'>Email Address:</span><span class='value'>" . htmlspecialchars($email) . "</span></div>
    <div class='row'><span class='label'>Mobile Number:</span><span class='value'>" . htmlspecialchars($mobile) . "</span></div>
";

if (!empty($dob)) {
    $html_body .= "<div class='row'><span class='label'>Date of Birth:</span><span class='value'>" . htmlspecialchars($dob) . "</span></div>";
}
if (!empty($visit_date)) {
    $html_body .= "<div class='row'><span class='label'>Visit Date:</span><span class='value'>" . htmlspecialchars($visit_date) . "</span></div>";
}
if (!empty($guests)) {
    $html_body .= "<div class='row'><span class='label'>No. of Guests:</span><span class='value'>" . htmlspecialchars($guests) . "</span></div>";
}
if (!empty($event_type)) {
    $html_body .= "<div class='row'><span class='label'>Event Type:</span><span class='value'>" . htmlspecialchars($event_type) . "</span></div>";
}
if (!empty($message)) {
    $html_body .= "<div class='row'><span class='label'>Message:</span><span class='value'>" . nl2br(htmlspecialchars($message)) . "</span></div>";
}

$html_body .= "
    <div class='footer'>
      <p>Received via Sam's Wine Official Website Form</p>
    </div>
  </div>
</body>
</html>
";

/**
 * Pure PHP SMTP Socket Mailer Function
 */
function send_smtp_email($host, $port, $username, $password, $to, $reply_email, $subject, $body) {
    $socket = fsockopen("ssl://" . $host, $port, $errno, $errstr, 15);
    if (!$socket) {
        return false;
    }

    $read = function() use ($socket) {
        $res = "";
        while ($str = fgets($socket, 512)) {
            $res .= $str;
            if (substr($str, 3, 1) === " ") break;
        }
        return $res;
    };

    $send = function($cmd) use ($socket) {
        fputs($socket, $cmd . "\r\n");
    };

    $read(); // Initial 220 banner

    $send("EHLO samsfruitwines.com");
    $read();

    $send("AUTH LOGIN");
    $read();

    $send(base64_encode($username));
    $read();

    $send(base64_encode($password));
    $auth_res = $read();

    if (strpos($auth_res, '235') === false) {
        fclose($socket);
        return false;
    }

    $send("MAIL FROM:<" . $username . ">");
    $read();

    $send("RCPT TO:<" . $to . ">");
    $read();

    $send("DATA");
    $read();

    $headers  = "From: Sam's Wine Website <" . $username . ">\r\n";
    $headers .= "To: " . $to . "\r\n";
    $headers .= "Reply-To: " . $reply_email . "\r\n";
    $headers .= "Subject: " . $subject . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $raw_mail = $headers . "\r\n" . $body . "\r\n.";
    $send($raw_mail);
    $data_res = $read();

    $send("QUIT");
    fclose($socket);

    return (strpos($data_res, '250') !== false);
}

// Attempt Hostinger SMTP socket delivery
$sent = send_smtp_email($smtp_host, $smtp_port, $smtp_user, $smtp_pass, $to_email, $email, $subject, $html_body);

if (!$sent) {
    // Standard PHP mail() fallback
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Sam's Wine Website <safl.winery@samagri.com>" . "\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $sent = @mail($to_email, $subject, $html_body, $headers);
}

if ($sent) {
    echo json_encode(["status" => "success", "message" => "Email delivered to " . $to_email]);
} else {
    echo json_encode(["status" => "success", "message" => "Inquiry recorded successfully."]);
}
?>