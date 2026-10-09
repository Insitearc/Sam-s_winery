<?php
/**
 * Sam's Fruit Wines - User Dashboard
 * Overview of customer account, recent orders, and Free Gift status.
 */

require_once __DIR__ . '/../includes/functions.php';
require_user_login('../login.php');

$pdo = getDbConnection();
$userId = (int)$_SESSION['user_id'];
$user = get_logged_in_user($pdo);

// Fetch stats
$orderCountStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$orderCountStmt->execute([$userId]);
$totalOrders = (int)$orderCountStmt->fetchColumn();

// Fetch Free Gift status
$giftStmt = $pdo->prepare("
    SELECT f.*, t.token, t.status AS token_status, r.redeemed_at, s.shop_name
    FROM free_wine_applications f
    LEFT JOIN gift_tokens t ON t.application_id = f.id
    LEFT JOIN gift_redemptions r ON r.token_id = t.id
    LEFT JOIN shops s ON s.id = r.shop_id
    WHERE f.user_id = ?
    LIMIT 1
");
$giftStmt->execute([$userId]);
$freeGift = $giftStmt->fetch();

// Fetch Recent Orders (last 5)
$ordersStmt = $pdo->prepare("
    SELECT * FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 5
");
$ordersStmt->execute([$userId]);
$recentOrders = $ordersStmt->fetchAll();

// Fetch Default Address
$addrStmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
$addrStmt->execute([$userId]);
$defaultAddress = $addrStmt->fetch();

$pageTitle = "Dashboard Overview";
$activeNav = "dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard | Sam's Fruit Wines</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="user-style.css">
</head>
<body>

    <!-- ORIGINAL WEBSITE FLOATING NAVBAR -->
    <?php require_once __DIR__ . '/navbar.php'; ?>

    <!-- MAIN CONTAINER WITH SIDEBAR & CONTENT -->
    <div class="user-container">
        
        <!-- SIDEBAR & MOBILE HAMBURGER NAVIGATION -->
        <?php require_once __DIR__ . '/sidebar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="user-content">
            
            <!-- WELCOME BANNER -->
            <div class="welcome-banner">
                <div class="welcome-text">
                    <h1>Namaste, <?= e(explode(' ', $user['full_name'])[0]) ?></h1>
                    <p>Welcome to your personal Sam's Fruit Wines Cellar Portal. Track estate orders, view voucher tokens, and manage delivery addresses.</p>
                </div>
                <div class="welcome-actions">
                    <a href="../shop.html" class="btn-gold"><i class="fa-solid fa-plus"></i> Order Wines</a>
                    <a href="free-gift.php" class="btn-trans"><i class="fa-solid fa-gift"></i> Free Gift Status</a>
                </div>
            </div>

            <!-- STATS CARDS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-boxes-packing"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Total Orders</span>
                        <strong class="stat-number"><?= $totalOrders ?></strong>
                        <span class="stat-sub">Delivered from Nashik</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-wine-bottle"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Free Wine Gift</span>
                        <strong class="stat-number" style="font-size: 1.3rem;">
                            <?= $freeGift ? e($freeGift['verification_status']) : 'Not Applied' ?>
                        </strong>
                        <span class="stat-sub">
                            <?= ($freeGift && !empty($freeGift['token'])) ? 'Token: ' . e($freeGift['token']) : 'Birthday / Welcome Offer' ?>
                        </span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-location-pin"></i></div>
                    <div class="stat-info">
                        <span class="stat-label">Default City</span>
                        <strong class="stat-number" style="font-size: 1.3rem;">
                            <?= $defaultAddress ? e($defaultAddress['city']) : 'Not Set' ?>
                        </strong>
                        <span class="stat-sub"><?= $defaultAddress ? e($defaultAddress['state']) : 'Add in Addresses' ?></span>
                    </div>
                </div>
            </div>

            <!-- FREE GIFT VOUCHER HIGHLIGHT CARD (IF ACTIVE) -->
            <?php if ($freeGift && !empty($freeGift['token']) && $freeGift['token_status'] === 'ACTIVE'): ?>
                <div class="token-alert-card">
                    <div class="token-badge"><i class="fa-solid fa-sparkles"></i> ACTIVE GIFT VOUCHER READY</div>
                    <h2>Complimentary Bottle Token: <span class="gold-token"><?= e($freeGift['token']) ?></span></h2>
                    <p>Congratulations! Your Aadhaar verification is complete. Present this token at any authorized Sam's Tasting Room or retail boutique to redeem your free 750ml bottle.</p>
                    <a href="free-gift.php" class="btn-gold-sm">View Gift Details & Authorized Outlets →</a>
                </div>
            <?php endif; ?>

            <!-- RECENT ORDERS SECTION -->
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fa-solid fa-clock-rotate-left"></i> Recent Orders</h2>
                    <a href="orders.php" class="card-link">View All Orders →</a>
                </div>

                <?php if (empty($recentOrders)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-wine-glass-empty"></i>
                        <h3>No Orders Yet</h3>
                        <p>Explore our handcrafted fruit and floral wines from the hills of Dindori, Nashik.</p>
                        <a href="../shop.html" class="btn-gold-sm">Browse Cellar Wines</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Total Amount</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $ord): ?>
                                    <tr>
                                        <td><strong><?= e($ord['order_number']) ?></strong></td>
                                        <td><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></td>
                                        <td><strong><?= format_price($ord['total_amount']) ?></strong></td>
                                        <td><span class="badge badge-<?= strtolower($ord['payment_status']) ?>"><?= e($ord['payment_status']) ?></span></td>
                                        <td><span class="badge badge-<?= strtolower($ord['order_status']) ?>"><?= e($ord['order_status']) ?></span></td>
                                        <td><a href="orders.php#order-<?= $ord['id'] ?>" class="btn-action">View Details</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- SAVED ADDRESS PREVIEW -->
            <div class="content-card">
                <div class="card-header">
                    <h2><i class="fa-solid fa-map-location-dot"></i> Default Delivery Address</h2>
                    <a href="address.php" class="card-link">Manage Addresses →</a>
                </div>
                <?php if ($defaultAddress): ?>
                    <div class="address-preview-box">
                        <p><strong><?= e($user['full_name']) ?></strong> (Mobile: <?= e($user['mobile']) ?>)</p>
                        <p class="addr-line"><?= e($defaultAddress['address']) ?></p>
                        <p class="addr-city"><?= e($defaultAddress['city']) ?>, <?= e($defaultAddress['state']) ?> - <?= e($defaultAddress['pincode']) ?></p>
                    </div>
                <?php else: ?>
                    <p class="text-muted">No default delivery address saved. <a href="address.php" style="color:var(--gold);">Add an address</a></p>
                <?php endif; ?>
            </div>

        </main>
    </div>

</body>
</html>
