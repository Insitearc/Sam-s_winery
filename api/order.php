<?php
/**
 * Sam's Fruit Wines - Order Placement API
 * Saves authenticated orders and order items in MySQL, preserving user's default saved address.
 * Dispatches confirmation emails.
 */

header('Content-Type: application/json; charset=UTF-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// Check User Authentication
if (!is_user_logged_in()) {
    http_response_code(401);
    echo json_encode([
        'status'   => 'unauthorized',
        'message'  => 'Please login or register to complete your order.',
        'loggedIn' => false
    ]);
    exit;
}

$pdo = getDbConnection();
$userId = (int)$_SESSION['user_id'];

// Read input
$rawBody = file_get_contents('php://input');
$order = json_decode($rawBody, true) ?: [];

$customerData = $order['customer'] ?? [];
$items        = $order['items'] ?? [];

if (empty($items)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Cart is empty. Please add bottles before checking out.']);
    exit;
}

// Customer details for this order
$name    = trim($customerData['name'] ?? $_SESSION['user_name'] ?? '');
$email   = trim(filter_var($customerData['email'] ?? $_SESSION['user_email'] ?? '', FILTER_SANITIZE_EMAIL));
$mobile  = trim($customerData['mobile'] ?? '');
$address = trim($customerData['address'] ?? '');
$city    = trim($customerData['city'] ?? 'Nashik');
$state   = trim($customerData['state'] ?? 'Maharashtra');
$pincode = trim($customerData['pincode'] ?? '');

if (empty($name) || empty($mobile) || empty($address)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Please provide complete delivery details (Name, Mobile, Address).']);
    exit;
}

// Calculate totals server-side
$subtotal = 0;
$totalQuantity = 0;
$processedItems = [];

// Fetch products from database to ensure genuine pricing
$productStmt = $pdo->prepare("SELECT id, name, price, bottle_size, category FROM products WHERE status = 'active'");
$productStmt->execute();
$dbProducts = [];
foreach ($productStmt->fetchAll() as $p) {
    $dbProducts[$p['id']] = $p;
    $dbProducts[strtolower($p['name'])] = $p;
}

foreach ($items as $item) {
    $itemName = trim($item['name'] ?? '');
    $qty      = max(1, (int)($item['qty'] ?? 1));
    $size     = trim($item['size'] ?? '750 ML');
    $clientPrice = (float)($item['price'] ?? 0);

    // Match with DB product if available
    $matched = null;
    if (isset($item['id']) && isset($dbProducts[$item['id']])) {
        $matched = $dbProducts[$item['id']];
    } elseif (isset($dbProducts[strtolower($itemName)])) {
        $matched = $dbProducts[strtolower($itemName)];
    }

    $price = $matched ? (float)$matched['price'] : ($clientPrice > 0 ? $clientPrice : 2399.00);
    $productId = $matched ? (int)$matched['id'] : 1; // Fallback to 1
    $itemSubtotal = $price * $qty;

    $subtotal += $itemSubtotal;
    $totalQuantity += $qty;

    $processedItems[] = [
        'product_id'   => $productId,
        'product_name' => $itemName ?: ($matched ? $matched['name'] : "Sam's Fruit Wine"),
        'bottle_size'  => $size,
        'quantity'     => $qty,
        'price'        => $price,
        'subtotal'     => $itemSubtotal
    ];
}

$discountPercent = (float)($order['discountPercent'] ?? 0);
$discountAmount = ($subtotal * $discountPercent) / 100;
$shippingFee = ($totalQuantity >= 6) ? 0.00 : 250.00;
$grandTotal = $subtotal - $discountAmount + $shippingFee;

$orderNumber = generate_order_number($pdo);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO orders (
            user_id, order_number, customer_name, customer_email, customer_mobile,
            delivery_address, city, state, pincode, subtotal, shipping_fee,
            discount_amount, total_amount, payment_method, payment_status, order_status
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, 'UPI / Online Payment', 'PENDING', 'CONFIRMED'
        )
    ");

    $stmt->execute([
        $userId,
        $orderNumber,
        $name,
        $email,
        $mobile,
        $address,
        $city,
        $state,
        $pincode,
        $subtotal,
        $shippingFee,
        $discountAmount,
        $grandTotal
    ]);

    $orderId = (int)$pdo->lastInsertId();

    $itemStmt = $pdo->prepare("
        INSERT INTO order_items (
            order_id, product_id, product_name, bottle_size, quantity, price, subtotal
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($processedItems as $it) {
        $itemStmt->execute([
            $orderId,
            $it['product_id'],
            $it['product_name'],
            $it['bottle_size'],
            $it['quantity'],
            $it['price'],
            $it['subtotal']
        ]);
    }

    $pdo->commit();

    // Prepare email confirmation
    $itemsHtml = '';
    foreach ($processedItems as $it) {
        $itemsHtml .= "<tr>
            <td style='padding:8px;border-bottom:1px solid #333;'>{$it['product_name']} ({$it['bottle_size']})</td>
            <td style='padding:8px;border-bottom:1px solid #333;text-align:center;'>{$it['quantity']}</td>
            <td style='padding:8px;border-bottom:1px solid #333;text-align:right;'>₹" . number_format($it['price'], 2) . "</td>
            <td style='padding:8px;border-bottom:1px solid #333;text-align:right;'>₹" . number_format($it['subtotal'], 2) . "</td>
        </tr>";
    }

    $emailBody = "
    <div style='background:#0a0a0a;color:#fff;padding:24px;font-family:sans-serif;max-width:640px;margin:auto;border:1px solid #c6a15b;border-radius:12px;'>
        <div style='text-align:center;padding-bottom:16px;border-bottom:1px solid #333;'>
            <h2 style='color:#c6a15b;margin:0;'>SAM'S FRUIT WINES</h2>
            <p style='color:#bbb;margin:4px 0 0;'>Order Confirmation - {$orderNumber}</p>
        </div>
        <div style='padding:16px 0;'>
            <p>Dear <strong>{$name}</strong>,</p>
            <p>Thank you for your order! Your bottles will be packed with shock-proof, temperature-controlled packaging and dispatched from our Dindori Winery in Nashik.</p>
            <h4 style='color:#c6a15b;margin:16px 0 8px;'>Order Summary</h4>
            <table style='width:100%;border-collapse:collapse;color:#ddd;font-size:14px;'>
                <thead>
                    <tr style='background:#1a1a1a;color:#c6a15b;'>
                        <th style='padding:8px;text-align:left;'>Product</th>
                        <th style='padding:8px;text-align:center;'>Qty</th>
                        <th style='padding:8px;text-align:right;'>Price</th>
                        <th style='padding:8px;text-align:right;'>Total</th>
                    </tr>
                </thead>
                <tbody>{$itemsHtml}</tbody>
            </table>
            <div style='text-align:right;margin-top:16px;padding-top:12px;border-top:1px solid #333;font-size:14px;color:#ddd;'>
                <p style='margin:4px 0;'>Subtotal: ₹" . number_format($subtotal, 2) . "</p>
                <p style='margin:4px 0;'>Discount: -₹" . number_format($discountAmount, 2) . "</p>
                <p style='margin:4px 0;'>Express Shipping: " . ($shippingFee == 0 ? '<strong style="color:#4ade80;">FREE</strong>' : '₹' . number_format($shippingFee, 2)) . "</p>
                <h3 style='margin:8px 0;color:#c6a15b;'>Grand Total: ₹" . number_format($grandTotal, 2) . "</h3>
            </div>
            <div style='background:#161616;padding:12px;border-radius:8px;margin-top:16px;border-left:3px solid #c6a15b;'>
                <strong style='color:#c6a15b;'>Delivery Address:</strong><br>
                {$name}<br>
                {$address}, {$city}, {$state} - {$pincode}<br>
                Mobile: {$mobile}
            </div>
        </div>
        <p style='color:#888;font-size:12px;text-align:center;margin-top:20px;'>Sam's Winery Estate, Dindori Valley, Nashik, Maharashtra</p>
    </div>";

    // Send order confirmation to customer and admin
    $recipients = array_merge([$email], MAIL_ADMIN_EMAILS);
    @send_smtp_email($recipients, "Order Confirmation #{$orderNumber} - Sam's Fruit Wines", $emailBody);

    echo json_encode([
        'status'      => 'success',
        'message'     => 'Order placed successfully!',
        'orderId'     => $orderId,
        'orderNumber' => $orderNumber,
        'grandTotal'  => $grandTotal,
        'customer'    => [
            'name'    => $name,
            'mobile'  => $mobile,
            'email'   => $email,
            'address' => $address
        ]
    ]);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Order placement error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Failed to place order: ' . $e->getMessage()
    ]);
    exit;
}
