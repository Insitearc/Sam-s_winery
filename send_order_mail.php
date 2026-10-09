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

/* =====================================================
   SMTP CONFIGURATION
   ===================================================== */

$smtp_host = 'smtp.gmail.com';
$smtp_port = 465;

/*
 * Your SMTP/Gmail account
 * Keep these credentials ONLY in PHP.
 */
$smtp_user = 'safl.wineryshop@samagri.com';
$smtp_pass = 'fmcf bjhj hshb ochg';

/*
 * Where you want to RECEIVE the order
 */
$to_emails = [
    'safl.wineryshop@samagri.com',
    'safl.winery@samagri.com',
    'sharvil11115@gmail.com',
];

$sender_name = "Sam's Winery";


/* =====================================================
   READ ORDER DATA
   ===================================================== */

$raw_body = file_get_contents('php://input');
$order = json_decode($raw_body, true);

if (!is_array($order)) {
    http_response_code(400);

    echo json_encode([
        "status" => "error",
        "message" => "Invalid order data."
    ]);

    exit;
}


/* =====================================================
   CUSTOMER DATA
   ===================================================== */

$order_id   = $order['orderId'] ?? '';
$order_date = $order['orderDate'] ?? '';

$customer = $order['customer'] ?? [];

$name    = trim(strip_tags($customer['name'] ?? ''));
$mobile  = trim(strip_tags($customer['mobile'] ?? ''));
$email   = trim(filter_var($customer['email'] ?? '', FILTER_SANITIZE_EMAIL));
$address = trim(strip_tags($customer['address'] ?? ''));


/* =====================================================
   ORDER DATA
   ===================================================== */

$items       = $order['items'] ?? [];
$discount    = (float)($order['discount'] ?? 0);
$shipping    = (float)($order['shipping'] ?? 0);
$grand_total = (float)($order['grandTotal'] ?? 0);


/* =====================================================
   VALIDATION
   ===================================================== */

if (empty($name) || empty($mobile) || empty($email) || empty($address)) {

    http_response_code(422);

    echo json_encode([
        "status" => "error",
        "message" => "Customer information is incomplete."
    ]);

    exit;
}


/* =====================================================
   BUILD PRODUCT TABLE
   ===================================================== */

$product_rows = '';

foreach ($items as $item) {

    $item_name = htmlspecialchars($item['name'] ?? '');
    $category  = htmlspecialchars($item['category'] ?? '');
    $size      = htmlspecialchars($item['size'] ?? '');

    $price = (float)($item['price'] ?? 0);
    $qty   = (int)($item['qty'] ?? 0);

    $item_total = (float)($item['itemTotal'] ?? ($price * $qty));

    $product_rows .= '
        <tr>
            <td style="padding:12px;border-bottom:1px solid #333;color:#fff;">
                ' . $item_name . '
            </td>

            <td style="padding:12px;border-bottom:1px solid #333;color:#ccc;">
                ' . $category . '
            </td>

            <td style="padding:12px;border-bottom:1px solid #333;color:#ccc;">
                ' . $size . '
            </td>

            <td style="padding:12px;border-bottom:1px solid #333;color:#ccc;text-align:center;">
                ' . $qty . '
            </td>

            <td style="padding:12px;border-bottom:1px solid #333;color:#d4af37;text-align:right;">
                ₹' . number_format($price, 2) . '
            </td>

            <td style="padding:12px;border-bottom:1px solid #333;color:#d4af37;text-align:right;">
                ₹' . number_format($item_total, 2) . '
            </td>
        </tr>
    ';
}


/* =====================================================
   EMAIL SUBJECT
   ===================================================== */

$subject = "New Winery Order - " . $order_id;


/* =====================================================
   HTML EMAIL
   ===================================================== */

$html_body = '
<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>New Sam\'s Winery Order</title>

</head>

<body style="
    margin:0;
    padding:30px;
    background:#080808;
    font-family:Arial,Helvetica,sans-serif;
    color:#fff;
">

<div style="
    max-width:800px;
    margin:auto;
    background:#121212;
    border:1px solid #c6a15b;
    border-radius:12px;
    overflow:hidden;
">

    <!-- HEADER -->

    <div style="
        padding:25px;
        text-align:center;
        background:#0d0d0d;
        border-bottom:2px solid #d4af37;
    ">

        <h1 style="
            margin:0;
            color:#d4af37;
            font-size:24px;
            letter-spacing:3px;
        ">
            SAM\'S WINERY
        </h1>

        <p style="
            margin:8px 0 0;
            color:#aaa;
            font-size:13px;
        ">
            NEW ORDER RECEIVED
        </p>

    </div>


    <!-- ORDER INFORMATION -->

    <div style="padding:25px;">

        <h2 style="
            color:#d4af37;
            font-size:16px;
            margin-top:0;
        ">
            ORDER INFORMATION
        </h2>

        <table width="100%" cellpadding="8" cellspacing="0">

            <tr>
                <td style="color:#999;">Order ID</td>
                <td style="color:#fff;font-weight:bold;">
                    ' . htmlspecialchars($order_id) . '
                </td>
            </tr>

            <tr>
                <td style="color:#999;">Order Date</td>
                <td style="color:#fff;">
                    ' . htmlspecialchars($order_date) . '
                </td>
            </tr>

        </table>


        <!-- CUSTOMER INFORMATION -->

        <h2 style="
            color:#d4af37;
            font-size:16px;
            margin-top:30px;
        ">
            CUSTOMER INFORMATION
        </h2>

        <table width="100%" cellpadding="8" cellspacing="0">

            <tr>
                <td width="30%" style="color:#999;">Full Name</td>
                <td style="color:#fff;">
                    ' . htmlspecialchars($name) . '
                </td>
            </tr>

            <tr>
                <td style="color:#999;">Mobile</td>
                <td style="color:#fff;">
                    ' . htmlspecialchars($mobile) . '
                </td>
            </tr>

            <tr>
                <td style="color:#999;">Email</td>
                <td style="color:#fff;">
                    ' . htmlspecialchars($email) . '
                </td>
            </tr>

            <tr>
                <td style="color:#999;vertical-align:top;">Address</td>
                <td style="color:#fff;">
                    ' . nl2br(htmlspecialchars($address)) . '
                </td>
            </tr>

        </table>


        <!-- PRODUCTS -->

        <h2 style="
            color:#d4af37;
            font-size:16px;
            margin-top:30px;
        ">
            ORDER ITEMS
        </h2>

        <table
            width="100%"
            cellpadding="0"
            cellspacing="0"
            style="
                border-collapse:collapse;
                font-size:13px;
            "
        >

            <thead>

                <tr style="background:#1c1c1c;">

                    <th style="padding:12px;text-align:left;color:#d4af37;">
                        Product
                    </th>

                    <th style="padding:12px;text-align:left;color:#d4af37;">
                        Category
                    </th>

                    <th style="padding:12px;text-align:left;color:#d4af37;">
                        Size
                    </th>

                    <th style="padding:12px;text-align:center;color:#d4af37;">
                        Qty
                    </th>

                    <th style="padding:12px;text-align:right;color:#d4af37;">
                        Price
                    </th>

                    <th style="padding:12px;text-align:right;color:#d4af37;">
                        Total
                    </th>

                </tr>

            </thead>

            <tbody>

                ' . $product_rows . '

            </tbody>

        </table>


        <!-- TOTAL -->

        <div style="
            margin-top:25px;
            border-top:1px solid #333;
            padding-top:20px;
        ">

            <table width="100%" cellpadding="8">

                <tr>

                    <td style="color:#aaa;">
                        Discount
                    </td>

                    <td style="
                        color:#4ade80;
                        text-align:right;
                    ">
                        - ₹' . number_format($discount, 2) . '
                    </td>

                </tr>


                <tr>

                    <td style="color:#aaa;">
                        Shipping
                    </td>

                    <td style="
                        color:#fff;
                        text-align:right;
                    ">
                        ₹' . number_format($shipping, 2) . '
                    </td>

                </tr>


                <tr>

                    <td style="
                        color:#fff;
                        font-size:18px;
                        font-weight:bold;
                        padding-top:15px;
                    ">
                        GRAND TOTAL
                    </td>

                    <td style="
                        color:#d4af37;
                        font-size:22px;
                        font-weight:bold;
                        text-align:right;
                        padding-top:15px;
                    ">
                        ₹' . number_format($grand_total, 2) . '
                    </td>

                </tr>

            </table>

        </div>

    </div>


    <!-- FOOTER -->

    <div style="
        padding:18px;
        background:#0b0b0b;
        text-align:center;
        color:#777;
        font-size:11px;
    ">

        Order received from
        <strong style="color:#d4af37;">
            samsfruitwines.com
        </strong>

    </div>

</div>

</body>

</html>
';


/* =====================================================
   SMTP FUNCTION
   ===================================================== */

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

    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true
        ]
    ]);

    $socket = @stream_socket_client(
        "ssl://" . $host . ":" . $port,
        $errno,
        $errstr,
        15,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, 15);


    $read = function() use ($socket) {

        $response = '';

        while (!feof($socket)) {

            $line = fgets($socket, 512);

            if ($line === false) {
                break;
            }

            $response .= $line;

            if (
                strlen($line) >= 4 &&
                substr($line, 3, 1) === ' '
            ) {
                break;
            }
        }

        return $response;
    };


    $send = function($command) use ($socket) {

        fwrite(
            $socket,
            $command . "\r\n"
        );
    };


    // Greeting

    $read();


    // EHLO

    $send("EHLO samsfruitwines.com");
    $read();


    // AUTH LOGIN

    $send("AUTH LOGIN");
    $read();


    // Username

    $send(base64_encode($username));
    $read();


    // Password

    $send(base64_encode($password));

    $auth_response = $read();

    if (
        strpos($auth_response, '235') === false
    ) {

        fclose($socket);

        return false;
    }


    // MAIL FROM

    $send(
        "MAIL FROM:<" .
        $username .
        ">"
    );

    $read();


    // RCPT TO

    $send(
        "RCPT TO:<" .
        $to .
        ">"
    );

    $read();


    // DATA

    $send("DATA");
    $read();


    $encoded_subject =
        '=?UTF-8?B?' .
        base64_encode($subject) .
        '?=';


    $headers  =
        "Date: " . date('r') . "\r\n";

    $headers .=
        "From: " .
        $sender_name .
        " <" .
        $username .
        ">\r\n";

    $headers .=
        "To: <" .
        $to .
        ">\r\n";

    $headers .=
        "Reply-To: <" .
        $reply_email .
        ">\r\n";

    $headers .=
        "Subject: " .
        $encoded_subject .
        "\r\n";

    $headers .=
        "MIME-Version: 1.0\r\n";

    $headers .=
        "Content-Type: text/html; charset=UTF-8\r\n";

    $headers .=
        "Content-Transfer-Encoding: 8bit\r\n";


    $message =
        $headers .
        "\r\n" .
        $body .
        "\r\n.";


    $send($message);

    $response = $read();


    $send("QUIT");

    fclose($socket);


    return (
        strpos($response, '250') !== false
    );
}


/* =====================================================
   SEND EMAIL
   ===================================================== */

$sent = true;

foreach ($to_emails as $recipient) {

    $mail_sent = send_smtp_email(
        $smtp_host,
        $smtp_port,
        $smtp_user,
        $smtp_pass,
        $recipient,
        $email,
        $subject,
        $html_body,
        $sender_name
    );

    if (!$mail_sent) {
        $sent = false;
    }
}


/* =====================================================
   RESPONSE
   ===================================================== */

if ($sent) {

    echo json_encode([
        "status" => "success",
        "message" => "Order email sent successfully."
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Order email could not be sent."
    ]);
}

exit;
?>