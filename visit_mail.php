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

/*
 * Winery account used to SEND visit booking emails.
 * Put the App Password of this Gmail account here.
 */
$smtp_user = 'safl.winery@samagri.com';
$smtp_pass = 'mmyg falh cezf alpz';


/*
 * Winery booking emails
 */
$to_email = [
    'safl.winery@samagri.com',
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
        "message" => "Invalid booking data."
    ]);

    exit;
}


/* ================= FORM DATA ================= */

$name = trim(strip_tags($data['name'] ?? ''));

$email = trim(
    filter_var(
        $data['email'] ?? '',
        FILTER_SANITIZE_EMAIL
    )
);

$mobile = trim(strip_tags($data['mobile'] ?? ''));

$visitDate = trim(
    strip_tags(
        $data['visit_date'] ?? ''
    )
);

$guests = trim(
    strip_tags(
        $data['guests'] ?? ''
    )
);

$experience = trim(
    strip_tags(
        $data['event_type'] ?? ''
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
    empty($visitDate)
) {

    http_response_code(422);

    echo json_encode([
        "status" => "error",
        "message" => "Please fill all required booking fields."
    ]);

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(422);

    echo json_encode([
        "status" => "error",
        "message" => "Invalid email address."
    ]);

    exit;
}


/* ================= SUBJECT ================= */

$subject = "New Winery Visit Reservation - " . $name;


/* ================= SAFE HTML ================= */

$safeName = htmlspecialchars(
    $name,
    ENT_QUOTES,
    'UTF-8'
);

$safeEmail = htmlspecialchars(
    $email,
    ENT_QUOTES,
    'UTF-8'
);

$safeMobile = htmlspecialchars(
    $mobile,
    ENT_QUOTES,
    'UTF-8'
);

$safeVisitDate = htmlspecialchars(
    $visitDate,
    ENT_QUOTES,
    'UTF-8'
);

$safeGuests = htmlspecialchars(
    $guests,
    ENT_QUOTES,
    'UTF-8'
);

$safeExperience = htmlspecialchars(
    $experience,
    ENT_QUOTES,
    'UTF-8'
);

$safeMessage = nl2br(
    htmlspecialchars(
        $message,
        ENT_QUOTES,
        'UTF-8'
    )
);


/* ================= HTML EMAIL ================= */

$html_body = "

<!DOCTYPE html>

<html>

<head>

<meta charset='UTF-8'>

<meta name='viewport'
content='width=device-width, initial-scale=1.0'>

<title>Winery Visit Reservation</title>

</head>

<body style='
margin:0;
padding:20px 10px;
background:#f4f4f4;
font-family:Arial,Helvetica,sans-serif;
'>

<table width='100%'
cellpadding='0'
cellspacing='0'
border='0'>

<tr>

<td align='center'>

<table width='100%'
cellpadding='0'
cellspacing='0'
border='0'
style='
max-width:650px;
background:#ffffff;
border-radius:12px;
overflow:hidden;
'>

<!-- HEADER -->

<tr>

<td style='
background:#111111;
padding:30px 25px;
text-align:center;
'>

<div style='
color:#d4af37;
font-size:12px;
letter-spacing:3px;
font-weight:bold;
margin-bottom:8px;
'>
SAM'S WINE
</div>

<div style='
color:#ffffff;
font-size:26px;
font-weight:500;
'>
Winery Visit Reservation
</div>

<div style='
color:#cccccc;
font-size:13px;
margin-top:8px;
'>
New reservation request received
</div>

</td>

</tr>


<!-- CONTENT -->

<tr>

<td style='padding:30px 25px;'>

<h2 style='
margin:0 0 20px 0;
color:#222222;
font-size:20px;
'>
Booking Details
</h2>


<table width='100%'
cellpadding='0'
cellspacing='0'
border='0'>


<tr>

<td style='
padding:12px 0;
border-bottom:1px solid #eeeeee;
color:#777777;
width:38%;
font-size:14px;
'>
Full Name
</td>

<td style='
padding:12px 0;
border-bottom:1px solid #eeeeee;
color:#222222;
font-weight:600;
font-size:14px;
'>
$safeName
</td>

</tr>


<tr>

<td style='
padding:12px 0;
border-bottom:1px solid #eeeeee;
color:#777777;
font-size:14px;
'>
Email
</td>

<td style='
padding:12px 0;
border-bottom:1px solid #eeeeee;
font-size:14px;
'>

<a href='mailto:$safeEmail'
style='
color:#9b7b18;
text-decoration:none;
'>
$safeEmail
</a>

</td>

</tr>


<tr>

<td style='
padding:12px 0;
border-bottom:1px solid #eeeeee;
color:#777777;
font-size:14px;
'>
Mobile
</td>

<td style='
padding:12px 0;
border-bottom:1px solid #eeeeee;
font-size:14px;
'>

<a href='tel:$safeMobile'
style='
color:#9b7b18;
text-decoration:none;
font-weight:600;
'>
$safeMobile
</a>

</td>

</tr>


<tr>

<td style='
padding:12px 0;
border-bottom:1px solid #eeeeee;
color:#777777;
font-size:14px;
'>
Visit Date
</td>

<td style='
padding:12px 0;
border-bottom:1px solid #eeeeee;
color:#222222;
font-weight:600;
font-size:14px;
'>
$safeVisitDate
</td>

</tr>


<tr>

<td style='
padding:12px 0;
border-bottom:1px solid #eeeeee;
color:#777777;
font-size:14px;
'>
No. of Guests
</td>

<td style='
padding:12px 0;
border-bottom:1px solid #eeeeee;
color:#222222;
font-size:14px;
'>
$safeGuests
</td>

</tr>


<tr>

<td style='
padding:12px 0;
border-bottom:1px solid #eeeeee;
color:#777777;
font-size:14px;
'>
Experience
</td>

<td style='
padding:12px 0;
border-bottom:1px solid #eeeeee;
color:#222222;
font-weight:600;
font-size:14px;
'>
$safeExperience
</td>

</tr>


</table>


<!-- NOTES -->

<div style='
margin-top:25px;
background:#faf8f1;
border-left:4px solid #d4af37;
padding:18px;
border-radius:6px;
'>

<div style='
color:#8b6f1d;
font-size:12px;
font-weight:bold;
letter-spacing:1px;
margin-bottom:8px;
'>
ADDITIONAL REQUESTS / NOTES
</div>

<div style='
color:#333333;
font-size:14px;
line-height:1.6;
'>
" . ($safeMessage ?: "No additional notes provided.") . "
</div>

</div>


<div style='
margin-top:25px;
text-align:center;
'>

<a href='tel:$safeMobile'
style='
display:inline-block;
background:#111111;
color:#ffffff;
text-decoration:none;
padding:12px 22px;
border-radius:6px;
font-size:13px;
margin:4px;
'>
Call Customer
</a>


<a href='mailto:$safeEmail'
style='
display:inline-block;
background:#d4af37;
color:#000000;
text-decoration:none;
padding:12px 22px;
border-radius:6px;
font-size:13px;
margin:4px;
'>
Email Customer
</a>

</div>

</td>

</tr>


<!-- FOOTER -->

<tr>

<td style='
background:#111111;
padding:20px;
text-align:center;
'>

<div style='
color:#d4af37;
font-size:12px;
letter-spacing:2px;
'>
SAM'S WINE
</div>

<div style='
color:#999999;
font-size:11px;
margin-top:7px;
line-height:1.5;
'>
Winery & Tasting Room · Nashik
</div>

</td>

</tr>


</table>

</td>

</tr>

</table>

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
        "message" => "Winery visit booking email sent successfully."
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Winery visit email could not be sent."
    ]);
}

?>