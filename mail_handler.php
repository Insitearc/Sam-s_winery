<?php

header("Content-Type: application/json; charset=UTF-8");


// ======================================================
// ERROR REPORTING
// ======================================================

error_reporting(E_ALL);
ini_set('display_errors', 0);


// ======================================================
// ALLOW POST ONLY
// ======================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit;
}


// ======================================================
// GET JSON DATA
// ======================================================

$rawData = file_get_contents("php://input");

$data = json_decode($rawData, true);


if (!$data) {

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid booking data."
    ]);

    exit;
}


// ======================================================
// GET FORM DATA
// ======================================================

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$mobile = trim($data['mobile'] ?? '');
$visitDate = trim($data['visit_date'] ?? '');
$guests = trim($data['guests'] ?? '');
$experience = trim($data['event_type'] ?? '');
$message = trim($data['message'] ?? '');


// ======================================================
// VALIDATION
// ======================================================

if (
    empty($name) ||
    empty($email) ||
    empty($mobile) ||
    empty($visitDate)
) {

    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Please fill all required fields."
    ]);

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Invalid email address."
    ]);

    exit;
}


// ======================================================
// EMAIL CONFIGURATION
// ======================================================

// Gmail account used to SEND the emails
$fromEmail = "safl.winery@samagri.com";
$fromName = "Sam's Wine Winery";

$gmailAppPassword = "mmyg falh cezf alpz";
$recipients = [
    "safl.winery@samagri.com",
    "safl.wineryshop@samagri.com",
    "dhonevaibhav785@gmail.com"
];


// ======================================================
// PHPMailer
// ======================================================

require_once __DIR__ . "/PHPMailer/src/Exception.php";
require_once __DIR__ . "/PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/PHPMailer/src/SMTP.php";


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// ======================================================
// CREATE MAIL
// ======================================================

$mail = new PHPMailer(true);


try {

    // --------------------------------------------------
    // SMTP CONFIGURATION
    // --------------------------------------------------

    $mail->isSMTP();

    $mail->Host = "smtp.gmail.com";

    $mail->SMTPAuth = true;

    $mail->Username = $fromEmail;

    $mail->Password = $gmailAppPassword;

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    $mail->Port = 587;


    // --------------------------------------------------
    // SENDER
    // --------------------------------------------------

    $mail->setFrom(
        $fromEmail,
        $fromName
    );


    // --------------------------------------------------
    // RECIPIENTS
    // --------------------------------------------------

    foreach ($recipients as $recipient) {

        $mail->addAddress($recipient);

    }


    // --------------------------------------------------
    // REPLY TO CUSTOMER
    // --------------------------------------------------

    $mail->addReplyTo(
        $email,
        $name
    );


    // --------------------------------------------------
    // EMAIL SUBJECT
    // --------------------------------------------------

    $mail->Subject =
        "New Winery Visit Reservation - " . $name;


    // --------------------------------------------------
    // SAFE HTML VALUES
    // --------------------------------------------------

    $safeName =
        htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

    $safeEmail =
        htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

    $safeMobile =
        htmlspecialchars($mobile, ENT_QUOTES, 'UTF-8');

    $safeVisitDate =
        htmlspecialchars($visitDate, ENT_QUOTES, 'UTF-8');

    $safeGuests =
        htmlspecialchars($guests, ENT_QUOTES, 'UTF-8');

    $safeExperience =
        htmlspecialchars($experience, ENT_QUOTES, 'UTF-8');

    $safeMessage =
        nl2br(
            htmlspecialchars(
                $message,
                ENT_QUOTES,
                'UTF-8'
            )
        );


    // --------------------------------------------------
    // MOBILE-FRIENDLY HTML EMAIL
    // --------------------------------------------------

    $emailBody = '

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Winery Visit Reservation</title>

</head>


<body style="
    margin:0;
    padding:0;
    background:#f4f4f4;
    font-family:Arial,Helvetica,sans-serif;
">


<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="background:#f4f4f4;padding:20px 10px;"
>

<tr>

<td align="center">


<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
    style="
        max-width:650px;
        background:#ffffff;
        border-radius:12px;
        overflow:hidden;
        box-shadow:0 4px 20px rgba(0,0,0,0.08);
    "
>


<!-- HEADER -->

<tr>

<td style="
    background:#111111;
    padding:30px 25px;
    text-align:center;
">

<div style="
    color:#d4af37;
    font-size:12px;
    letter-spacing:3px;
    font-weight:bold;
    margin-bottom:8px;
">

SAM\'S WINE

</div>


<div style="
    color:#ffffff;
    font-size:26px;
    font-weight:500;
">

Winery Visit Reservation

</div>


<div style="
    color:#cccccc;
    font-size:13px;
    margin-top:8px;
">

New reservation request received

</div>

</td>

</tr>


<!-- CONTENT -->

<tr>

<td style="padding:30px 25px;">


<h2 style="
    margin:0 0 20px 0;
    color:#222222;
    font-size:20px;
">

Booking Details

</h2>


<table
    width="100%"
    cellpadding="0"
    cellspacing="0"
    border="0"
>


<tr>

<td style="
    padding:12px 0;
    border-bottom:1px solid #eeeeee;
    color:#777777;
    width:38%;
    font-size:14px;
">

Full Name

</td>

<td style="
    padding:12px 0;
    border-bottom:1px solid #eeeeee;
    color:#222222;
    font-weight:600;
    font-size:14px;
">

' . $safeName . '

</td>

</tr>


<tr>

<td style="
    padding:12px 0;
    border-bottom:1px solid #eeeeee;
    color:#777777;
    font-size:14px;
">

Email

</td>

<td style="
    padding:12px 0;
    border-bottom:1px solid #eeeeee;
    font-size:14px;
">

<a
    href="mailto:' . $safeEmail . '"
    style="
        color:#9b7b18;
        text-decoration:none;
    "
>

' . $safeEmail . '

</a>

</td>

</tr>


<tr>

<td style="
    padding:12px 0;
    border-bottom:1px solid #eeeeee;
    color:#777777;
    font-size:14px;
">

Mobile

</td>

<td style="
    padding:12px 0;
    border-bottom:1px solid #eeeeee;
    font-size:14px;
">

<a
    href="tel:' . $safeMobile . '"
    style="
        color:#9b7b18;
        text-decoration:none;
        font-weight:600;
    "
>

' . $safeMobile . '

</a>

</td>

</tr>


<tr>

<td style="
    padding:12px 0;
    border-bottom:1px solid #eeeeee;
    color:#777777;
    font-size:14px;
">

Visit Date

</td>

<td style="
    padding:12px 0;
    border-bottom:1px solid #eeeeee;
    color:#222222;
    font-weight:600;
    font-size:14px;
">

' . $safeVisitDate . '

</td>

</tr>


<tr>

<td style="
    padding:12px 0;
    border-bottom:1px solid #eeeeee;
    color:#777777;
    font-size:14px;
">

No. of Guests

</td>

<td style="
    padding:12px 0;
    border-bottom:1px solid #eeeeee;
    color:#222222;
    font-size:14px;
">

' . $safeGuests . '

</td>

</tr>


<tr>

<td style="
    padding:12px 0;
    border-bottom:1px solid #eeeeee;
    color:#777777;
    font-size:14px;
">

Experience

</td>

<td style="
    padding:12px 0;
    border-bottom:1px solid #eeeeee;
    color:#222222;
    font-weight:600;
    font-size:14px;
">

' . $safeExperience . '

</td>

</tr>


</table>


<!-- NOTES -->

<div style="
    margin-top:25px;
    background:#faf8f1;
    border-left:4px solid #d4af37;
    padding:18px;
    border-radius:6px;
">


<div style="
    color:#8b6f1d;
    font-size:12px;
    font-weight:bold;
    letter-spacing:1px;
    margin-bottom:8px;
">

ADDITIONAL REQUESTS / NOTES

</div>


<div style="
    color:#333333;
    font-size:14px;
    line-height:1.6;
">

' . ($safeMessage ?: "No additional notes provided.") . '

</div>


</div>


<!-- ACTION BUTTONS -->

<div style="
    margin-top:25px;
    text-align:center;
">


<a
    href="tel:' . $safeMobile . '"
    style="
        display:inline-block;
        background:#111111;
        color:#ffffff;
        text-decoration:none;
        padding:12px 22px;
        border-radius:6px;
        font-size:13px;
        margin:4px;
    "
>

Call Customer

</a>


<a
    href="mailto:' . $safeEmail . '"
    style="
        display:inline-block;
        background:#d4af37;
        color:#000000;
        text-decoration:none;
        padding:12px 22px;
        border-radius:6px;
        font-size:13px;
        margin:4px;
    "
>

Email Customer

</a>


</div>


</td>

</tr>


<!-- FOOTER -->

<tr>

<td style="
    background:#111111;
    padding:20px;
    text-align:center;
">


<div style="
    color:#d4af37;
    font-size:12px;
    letter-spacing:2px;
">

SAM\'S WINE

</div>


<div style="
    color:#999999;
    font-size:11px;
    margin-top:7px;
    line-height:1.5;
">

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
';


    // --------------------------------------------------
    // SEND HTML EMAIL
    // --------------------------------------------------

    $mail->isHTML(true);

    $mail->Body = $emailBody;

    $mail->AltBody =
        "New Winery Visit Reservation\n\n" .
        "Name: " . $name . "\n" .
        "Email: " . $email . "\n" .
        "Mobile: " . $mobile . "\n" .
        "Visit Date: " . $visitDate . "\n" .
        "Guests: " . $guests . "\n" .
        "Experience: " . $experience . "\n" .
        "Notes: " . $message;


    // --------------------------------------------------
    // SEND
    // --------------------------------------------------

    $mail->send();


    // --------------------------------------------------
    // SUCCESS
    // --------------------------------------------------

    echo json_encode([

        "success" => true,

        "message" =>
            "Booking submitted successfully."

    ]);


} catch (Exception $e) {


    // --------------------------------------------------
    // ERROR
    // --------------------------------------------------

    http_response_code(500);

    echo json_encode([

        "success" => false,

        "message" =>
            "Email could not be sent.",

        "error" =>
            $mail->ErrorInfo

    ]);

}

?>