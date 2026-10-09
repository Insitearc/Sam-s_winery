<?php
/**
 * Sam's Fruit Wines - Executive Admin Dashboard
 */
define('ADMIN_PANEL', true);
require_once __DIR__ . '/auth.php';

$pageTitle = 'Dashboard Overview';
$activeNav = 'dashboard';

// Fetch Statistics from MySQL
$statUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stmtOrders = $pdo->query("SELECT COUNT(*) as total_count, COALESCE(SUM(total_amount), 0) as total_rev FROM orders");
$orderStats = $stmtOrders->fetch(PDO::FETCH_ASSOC);
$statOrders = (int)$orderStats['total_count'];
$statRevenue = (float)$orderStats['total_rev'];

$statApps = (int)$pdo->query("SELECT COUNT(*) FROM free_wine_applications")->fetchColumn();
$statPending = (int)$pdo->query("SELECT COUNT(*) FROM free_wine_applications WHERE verification_status = 'PENDING'")->fetchColumn();
$statVerified = (int)$pdo->query("SELECT COUNT(*) FROM free_wine_applications WHERE verification_status = 'VERIFIED'")->fetchColumn();
$statRejected = (int)$pdo->query("SELECT COUNT(*) FROM free_wine_applications WHERE verification_status = 'REJECTED'")->fetchColumn();
$statRedeemed = (int)$pdo->query("SELECT COUNT(*) FROM gift_redemptions")->fetchColumn();

// Fetch Recent Orders (5)
$stmtRecentOrders = $pdo->query("
    SELECT o.*, COALESCE(u.full_name, o.customer_name) as display_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.id DESC 
    LIMIT 6
");
$recentOrders = $stmtRecentOrders->fetchAll(PDO::FETCH_ASSOC);

// Fetch Recent Applications (6)
$stmtRecentApps = $pdo->query("
    SELECT a.*, t.token, t.status as token_status 
    FROM free_wine_applications a 
    LEFT JOIN gift_tokens t ON a.id = t.application_id 
    ORDER BY a.id DESC 
    LIMIT 6
");
$recentApps = $stmtRecentApps->fetchAll(PDO::FETCH_ASSOC);

// Fetch Recent Redemptions (5)
$stmtRecentRedemptions = $pdo->query("
    SELECT r.*, t.token, u.full_name as customer_name, s.shop_name, s.city as shop_city 
    FROM gift_redemptions r 
    JOIN gift_tokens t ON r.token_id = t.id 
    JOIN users u ON r.user_id = u.id 
    JOIN shops s ON r.shop_id = s.id 
    ORDER BY r.id DESC 
    LIMIT 5
");
$recentRedemptions = $stmtRecentRedemptions->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/header.php';
?>

<!-- STATS OVERVIEW CARDS -->
<div class="admin-stats-grid">
    <div class="stat-box">
        <div class="stat-box-icon icon-gold">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="stat-box-details">
            <span class="stat-box-title">Registered Users</span>
            <div class="stat-box-num"><?php echo number_format($statUsers); ?></div>
            <span class="stat-box-sub">Active wine community</span>
        </div>
    </div>

    <div class="stat-box">
        <div class="stat-box-icon icon-green">
            <i class="fa-solid fa-cart-shopping"></i>
        </div>
        <div class="stat-box-details">
            <span class="stat-box-title">Total Orders</span>
            <div class="stat-box-num"><?php echo number_format($statOrders); ?></div>
            <span class="stat-box-sub">₹<?php echo number_format($statRevenue, 2); ?> total sales</span>
        </div>
    </div>

    <div class="stat-box">
        <div class="stat-box-icon icon-yellow">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="stat-box-details">
            <span class="stat-box-title">Pending Claims</span>
            <div class="stat-box-num"><?php echo number_format($statPending); ?></div>
            <span class="stat-box-sub">Awaiting DOB check</span>
        </div>
    </div>

    <div class="stat-box">
        <div class="stat-box-icon icon-blue">
            <i class="fa-solid fa-certificate"></i>
        </div>
        <div class="stat-box-details">
            <span class="stat-box-title">Verified Claims</span>
            <div class="stat-box-num"><?php echo number_format($statVerified); ?></div>
            <span class="stat-box-sub">Tokens activated</span>
        </div>
    </div>

    <div class="stat-box">
        <div class="stat-box-icon icon-red">
            <i class="fa-solid fa-ban"></i>
        </div>
        <div class="stat-box-details">
            <span class="stat-box-title">Rejected Claims</span>
            <div class="stat-box-num"><?php echo number_format($statRejected); ?></div>
            <span class="stat-box-sub">Invalid or underage</span>
        </div>
    </div>

    <div class="stat-box">
        <div class="stat-box-icon icon-gold">
            <i class="fa-solid fa-wine-glass"></i>
        </div>
        <div class="stat-box-details">
            <span class="stat-box-title">Redeemed Bottles</span>
            <div class="stat-box-num"><?php echo number_format($statRedeemed); ?></div>
            <span class="stat-box-sub">Free bottles collected</span>
        </div>
    </div>
</div>

<!-- SECTION 1: RECENT FREE WINE CLAIMS -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fa-solid fa-gift"></i> Recent Free Wine Applications</h3>
        <a href="free-wine.php" class="btn-outline">View All (<?php echo $statApps; ?>) <i class="fa-solid fa-arrow-right"></i></a>
    </div>

    <?php if (empty($recentApps)): ?>
        <p style="color: var(--text-muted); text-align: center; padding: 25px 0;">No Free Wine applications submitted yet.</p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Applicant</th>
                        <th>Contact</th>
                        <th>DOB</th>
                        <th>Aadhaar</th>
                        <th>Verification</th>
                        <th>Token</th>
                        <th>Applied On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentApps as $app): ?>
                        <tr>
                            <td><strong>#<?php echo $app['id']; ?></strong></td>
                            <td>
                                <strong style="color: #fff;"><?php echo htmlspecialchars($app['name']); ?></strong>
                            </td>
                            <td>
                                <div><i class="fa-solid fa-phone" style="font-size:11px; color:var(--gold);"></i> <?php echo htmlspecialchars($app['mobile']); ?></div>
                                <div style="font-size:12px; color:var(--text-muted);"><?php echo htmlspecialchars($app['email']); ?></div>
                            </td>
                            <td><?php echo date('d M Y', strtotime($app['dob'])); ?></td>
                            <td>
                                <?php if (!empty($app['aadhaar_file'])): ?>
                                    <a href="view-aadhaar.php?id=<?php echo $app['id']; ?>" target="_blank" class="btn-outline" style="padding: 4px 10px; font-size: 11px;">
                                        <i class="fa-solid fa-file-pdf"></i> View
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size:12px;">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $vStatus = strtoupper($app['verification_status'] ?? 'PENDING');
                                    $badgeClass = 'badge-pending';
                                    if ($vStatus === 'VERIFIED') $badgeClass = 'badge-verified';
                                    elseif ($vStatus === 'REJECTED') $badgeClass = 'badge-rejected';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo $vStatus; ?></span>
                            </td>
                            <td>
                                <?php if (!empty($app['token'])): ?>
                                    <code style="background: rgba(212,175,55,0.15); color: var(--gold-light); padding: 3px 6px; border-radius: 4px; font-weight: 600;">
                                        <?php echo htmlspecialchars($app['token']); ?>
                                    </code>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 12px;">Not Generated</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M, H:i', strtotime($app['created_at'])); ?></td>
                            <td>
                                <a href="free-wine.php?focus=<?php echo $app['id']; ?>" class="btn-outline" style="padding: 4px 8px; font-size: 11px;">
                                    Manage <i class="fa-solid fa-sliders"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 24px;">
    <!-- SECTION 2: RECENT ORDERS -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fa-solid fa-box"></i> Recent Orders</h3>
            <a href="orders.php" class="btn-outline">All Orders <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        <?php if (empty($recentOrders)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 25px 0;">No orders placed yet.</p>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $ord): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--gold-light);"><?php echo htmlspecialchars($ord['order_number']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($ord['display_name']); ?></td>
                                <td><strong>₹<?php echo number_format((float)$ord['total_amount'], 2); ?></strong></td>
                                <td>
                                    <?php 
                                        $oStat = strtolower($ord['order_status']);
                                        $oBadge = 'badge-pending';
                                        if (in_array($oStat, ['confirmed', 'paid'])) $oBadge = 'badge-confirmed';
                                        elseif ($oStat === 'delivered') $oBadge = 'badge-delivered';
                                        elseif ($oStat === 'shipped') $oBadge = 'badge-shipped';
                                        elseif ($oStat === 'cancelled') $oBadge = 'badge-cancelled';
                                    ?>
                                    <span class="badge <?php echo $oBadge; ?>"><?php echo strtoupper($oStat); ?></span>
                                </td>
                                <td><?php echo date('d M Y', strtotime($ord['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- SECTION 3: RECENT REDEMPTIONS -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fa-solid fa-store"></i> Gift Redemptions Log</h3>
            <span class="badge badge-redeemed"><?php echo $statRedeemed; ?> Total Claimed</span>
        </div>

        <?php if (empty($recentRedemptions)): ?>
            <p style="color: var(--text-muted); text-align: center; padding: 25px 0;">No free bottles redeemed at outlets yet.</p>
        <?php else: ?>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Token</th>
                            <th>Customer</th>
                            <th>Shop Outlet</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRedemptions as $red): ?>
                            <tr>
                                <td>
                                    <code style="color: var(--gold-light); font-weight: 600;">
                                        <?php echo htmlspecialchars($red['token']); ?>
                                    </code>
                                </td>
                                <td><?php echo htmlspecialchars($red['customer_name']); ?></td>
                                <td>
                                    <div><strong><?php echo htmlspecialchars($red['shop_name']); ?></strong></div>
                                    <small style="color: var(--text-muted);"><?php echo htmlspecialchars($red['shop_city']); ?></small>
                                </td>
                                <td><?php echo date('d M Y, H:i', strtotime($red['redeemed_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
