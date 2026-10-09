<?php

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        "status" => "error",
        "message" => "Method Not Allowed"
    ]);

    exit;
}


/* ================= SMTP CONFIG ================= */

$smtp_host = 'smtp.gmail.com';
$smtp_port = 465;

$smtp_user = 'safl.wineryshop@samagri.com';
$smtp_pass = 'fmcf bjhj hshb ochg';

$to_email = [
    'safl.wineryshop@samagri.com',
    'dhonevaibhav785@gmail.com'
];

$sender_name = "Sam's Winery";


/* ================= READ FORM DATA ================= */

$raw_body = file_get_contents('php://input');

$data = json_decode($raw_body, true);

if (!is_array($data)) {

    http_response_code(400);

    echo json_encode([
        "status" => "error",
        "message" => "Invalid form data."
    ]);

    exit;
}


/* ================= FORM DATA ================= */

$form_type = trim(strip_tags($data['form_type'] ?? 'Wine Club Sign Up'));

$name = trim(strip_tags($data['name'] ?? ''));

$email = trim(
    filter_var(
        $data['email'] ?? '',
        FILTER_SANITIZE_EMAIL
    )
);

$mobile = trim(strip_tags($data['mobile'] ?? ''));

$dob = trim(strip_tags($data['dob'] ?? ''));

$couponCode = trim(
    strip_tags(
        $data['couponCode'] ?? ''
    )
);

$signupDate = trim(
    strip_tags(
        $data['signupDate'] ?? ''
    )
);

$message = trim(
    strip_tags(
        $data['message'] ?? ''
    )
);


/* ================= VALIDATION ================= */

if (
    empty($name) ||
    empty($email) ||
    empty($mobile) ||
    empty($dob)
) {

    http_response_code(422);

    echo json_encode([
        "status" => "error",
        "message" => "Required signup information is missing."
    ]);

    exit;
}


/* ================= SUBJECT ================= */

$subject = "New Wine Club Signup - " . $name;


/* ================= HTML EMAIL ================= */

$html_body = "

<!DOCTYPE html>

<html>

<head>

<meta charset='UTF-8'>

<title>New Sam's Winery Signup</title>

</head>

<body style='margin:0;padding:30px;background:#080808;font-family:Arial,sans-serif;'>

<div style='
max-width:650px;
margin:auto;
background:#121212;
border:1px solid #d4af37;
border-radius:12px;
overflow:hidden;
'>

<div style='
padding:30px;
text-align:center;
border-bottom:1px solid #d4af37;
'>

<h1 style='
margin:0;
color:#d4af37;
font-size:30px;
letter-spacing:4px;
'>
SAM'S WINERY
</h1>

<p style='
margin:10px 0 0;
color:#aaa;
font-size:16px;
'>
NEW WINE CLUB SIGNUP
</p>

</div>


<div style='padding:30px;color:#fff;'>

<table style='width:100%;border-collapse:collapse;'>

<tr>
<td style='padding:12px 0;color:#d4af37;font-weight:bold;'>
Full Name
</td>

<td style='padding:12px 0;color:#fff;'>
" . htmlspecialchars($name) . "
</td>
</tr>


<tr>
<td style='padding:12px 0;color:#d4af37;font-weight:bold;'>
Email Address
</td>

<td style='padding:12px 0;color:#fff;'>
" . htmlspecialchars($email) . "
</td>
</tr>


<tr>
<td style='padding:12px 0;color:#d4af37;font-weight:bold;'>
Mobile Number
</td>

<td style='padding:12px 0;color:#fff;'>
" . htmlspecialchars($mobile) . "
</td>
</tr>


<tr>
<td style='padding:12px 0;color:#d4af37;font-weight:bold;'>
Date of Birth
</td>

<td style='padding:12px 0;color:#fff;'>
" . htmlspecialchars($dob) . "
</td>
</tr>


<tr>
<td style='padding:12px 0;color:#d4af37;font-weight:bold;'>
Voucher Code
</td>

<td style='padding:12px 0;color:#fff;'>
" . htmlspecialchars($couponCode) . "
</td>
</tr>


<tr>
<td style='padding:12px 0;color:#d4af37;font-weight:bold;'>
Signup Date
</td>

<td style='padding:12px 0;color:#fff;'>
" . htmlspecialchars($signupDate) . "
</td>
</tr>

</table>


<div style='
margin-top:25px;
padding:18px;
background:#181818;
border:1px solid #333;
border-radius:8px;
color:#ccc;
'>

<strong style='color:#d4af37;'>
Message:
</strong>

<br><br>

" . htmlspecialchars($message) . "

</div>

</div>


<div style='
padding:18px;
text-align:center;
border-top:1px solid #333;
color:#777;
font-size:12px;
'>

Received from Sam's Winery Website

</div>

</div>

</body>

</html>
";


/* ================= SMTP FUNCTION ================= */

function send_smtp_email(
    $host,
    $port,
    $username,
    $password,
    $to,
    $reply_email,
    $subject,
    $body,
    $sender_name
) {

    $socket = fsockopen(
        "ssl://" . $host,
        $port,
        $errno,
        $errstr,
        15
    );

    if (!$socket) {
        return false;
    }


    $read = function() use ($socket) {

        $response = '';

        while ($line = fgets($socket, 512)) {

            $response .= $line;

            if (
                strlen($line) >= 4 &&
                $line[3] === ' '
            ) {
                break;
            }
        }

        return $response;
    };


    $send = function($command) use ($socket) {

        fputs(
            $socket,
            $command . "\r\n"
        );
    };


    $read();

    $send("EHLO samsfruitwines.com");
    $read();

    $send("AUTH LOGIN");
    $read();

    $send(base64_encode($username));
    $read();

    $send(base64_encode($password));

    $auth_response = $read();


    if (strpos($auth_response, '235') === false) {

        fclose($socket);

        return false;
    }


$send("MAIL FROM:<" . $username . ">");
$read();


/* SEND TO ALL RECIPIENTS */

foreach ($to as $recipient) {

    $recipient = trim($recipient);

    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        continue;
    }

    $send("RCPT TO:<" . $recipient . ">");
    $recipient_response = $read();

    if (
        strpos($recipient_response, '250') === false &&
        strpos($recipient_response, '251') === false
    ) {
        fclose($socket);
        return false;
    }
}


$send("DATA");
$read();


/* EMAIL HEADERS */

$headers  = "From: " . $sender_name . " <" . $username . ">\r\n";
$headers .= "To: " . implode(', ', $to) . "\r\n";
$headers .= "Reply-To: " . $reply_email . "\r\n";
$headers .= "Subject: " . $subject . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";


$mail = $headers .
        "\r\n" .
        $body .
        "\r\n.";


$send($mail);

    $response = $read();

    $send("QUIT");

    fclose($socket);


    return strpos($response, '250') !== false;
}


/* ================= SEND EMAIL ================= */

$sent = send_smtp_email(
    $smtp_host,
    $smtp_port,
    $smtp_user,
    $smtp_pass,
    $to_email,
    $email,
    $subject,
    $html_body,
    $sender_name
);


/* ================= RESPONSE ================= */

if ($sent) {

    echo json_encode([
        "status" => "success",
        "message" => "Signup email sent successfully."
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Signup email could not be sent."
    ]);
}

?>