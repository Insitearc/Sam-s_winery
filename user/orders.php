<?php
/**
 * Sam's Fruit Wines - Customer Order History
 * Real order records fetched from MySQL with items breakdown.
 */

require_once __DIR__ . '/../includes/functions.php';
require_user_login('../login.php');

$pdo = getDbConnection();
$userId = (int)$_SESSION['user_id'];
$user = get_logged_in_user($pdo);

// Fetch all orders with their item count
$stmt = $pdo->prepare("
    SELECT o.*, 
           COUNT(i.id) AS total_items_count,
           SUM(i.quantity) AS total_bottles_count
    FROM orders o
    LEFT JOIN order_items i ON i.order_id = o.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

// Fetch items for each order
$orderItemsMap = [];
if (!empty($orders)) {
    $orderIds = array_column($orders, 'id');
    $inClause = implode(',', array_fill(0, count($orderIds), '?'));
    $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id IN ($inClause)");
    $itemsStmt->execute($orderIds);
    foreach ($itemsStmt->fetchAll() as $item) {
        $orderItemsMap[$item['order_id']][] = $item;
    }
}
$pageTitle = "My Orders";
$activeNav = "orders";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Sam's Fruit Wines</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="user-style.css">
    <style>
        .order-card {
            background: #141414;
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 20px;
            transition: border-color 0.25s;
        }
        .order-card:hover {
            border-color: var(--gold-border);
        }
        .order-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            padding-bottom: 14px;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .order-meta-group {
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .order-meta-item {
            font-size: 13px;
        }
        .order-meta-label {
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: block;
        }
        .order-meta-val {
            color: #fff;
            font-weight: 600;
        }
        .order-table-wrap {
            overflow-x: auto;
            width: 100%;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 14px;
        }
        .order-items-table {
            width: 100%;
            min-width: 520px;
            border-collapse: collapse;
            font-size: 13.5px;
        }
        .order-items-table th {
            text-align: left;
            padding: 8px 10px;
            color: var(--gold);
            font-size: 11px;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .order-items-table td {
            padding: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            color: var(--text-secondary);
        }
        .order-summary-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 16px;
            background: #0d0d0d;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 13px;
        }
        .delivery-summary {
            max-width: 450px;
        }
        .price-breakdown {
            text-align: right;
        }
    </style>
</head>
<body>

    <!-- ORIGINAL WEBSITE FLOATING NAVBAR -->
    <?php require_once __DIR__ . '/navbar.php'; ?>

    <div class="user-container">
        <!-- SIDEBAR & MOBILE HAMBURGER NAVIGATION -->
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <main class="user-content">
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fa-solid fa-boxes-stacked"></i> My Order History</h2>
                    <span class="text-muted" style="font-size:13px;"><?= count($orders) ?> Total Orders</span>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-wine-bottle"></i>
                        <h3>You Have Not Placed Any Orders</h3>
                        <p>Discover our rare pome, jamun, strawberry, and mahua flower wines made in Nashik.</p>
                        <a href="../shop.html" class="btn-gold-sm"><i class="fa-solid fa-cart-plus"></i> Shop Wines Now</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $ord): ?>
                        <div class="order-card" id="order-<?= $ord['id'] ?>">
                            <div class="order-card-header">
                                <div class="order-meta-group">
                                    <div class="order-meta-item">
                                        <span class="order-meta-label">Order Number</span>
                                        <span class="order-meta-val" style="color:var(--gold-light);"><?= e($ord['order_number']) ?></span>
                                    </div>
                                    <div class="order-meta-item">
                                        <span class="order-meta-label">Date Placed</span>
                                        <span class="order-meta-val"><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></span>
                                    </div>
                                    <div class="order-meta-item">
                                        <span class="order-meta-label">Total Amount</span>
                                        <span class="order-meta-val" style="color:var(--gold); font-size:15px;"><?= format_price($ord['total_amount']) ?></span>
                                    </div>
                                </div>
                                <div style="display:flex; gap:8px;">
                                    <span class="badge badge-<?= strtolower($ord['payment_status']) ?>">Payment: <?= e($ord['payment_status']) ?></span>
                                    <span class="badge badge-<?= strtolower($ord['order_status']) ?>"><?= e($ord['order_status']) ?></span>
                                </div>
                            </div>

                            <!-- ITEMS LIST -->
                            <h4 style="font-size:12px; color:var(--gold); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px;">
                                <i class="fa-solid fa-wine-glass"></i> Bottles in this Order:
                            </h4>
                            <div class="order-table-wrap">
                                <table class="order-items-table">
                                    <thead>
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Bottle Size</th>
                                            <th style="text-align:center;">Quantity</th>
                                            <th style="text-align:right;">Price</th>
                                            <th style="text-align:right;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $items = $orderItemsMap[$ord['id']] ?? [];
                                        foreach ($items as $it): 
                                        ?>
                                            <tr>
                                                <td><strong><?= e($it['product_name']) ?></strong></td>
                                                <td><?= e($it['bottle_size']) ?></td>
                                                <td style="text-align:center;"><?= e($it['quantity']) ?></td>
                                                <td style="text-align:right;"><?= format_price($it['price']) ?></td>
                                                <td style="text-align:right; color:#fff;"><strong><?= format_price($it['subtotal']) ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="order-summary-footer">
                                <div class="delivery-summary">
                                    <span class="order-meta-label"><i class="fa-solid fa-location-dot"></i> Delivery Address for this Order</span>
                                    <p style="color:#eee; margin-top:3px;"><?= e($ord['customer_name']) ?> (<?= e($ord['customer_mobile']) ?>)</p>
                                    <p style="color:var(--text-secondary);"><?= e($ord['delivery_address']) ?></p>
                                    <p style="color:var(--text-muted); font-size:12px;"><?= e($ord['city']) ?>, <?= e($ord['state']) ?> - <?= e($ord['pincode']) ?></p>
                                </div>
                                <div class="price-breakdown">
                                    <p style="color:var(--text-muted);">Subtotal: <?= format_price($ord['subtotal']) ?></p>
                                    <?php if ($ord['discount_amount'] > 0): ?>
                                        <p style="color:#4ade80;">Discount: -<?= format_price($ord['discount_amount']) ?></p>
                                    <?php endif; ?>
                                    <p style="color:var(--text-muted);">Shipping: <?= ($ord['shipping_fee'] == 0) ? 'FREE' : format_price($ord['shipping_fee']) ?></p>
                                    <p style="color:var(--gold-light); font-size:16px; font-weight:700; margin-top:4px;">Grand Total: <?= format_price($ord['total_amount']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>
