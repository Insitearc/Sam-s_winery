<?php
/**
 * Sam's Fruit Wines - Admin Users Management
 * View customers, search, inspect order history, and Free Gift token status
 */
define('ADMIN_PANEL', true);
require_once __DIR__ . '/auth.php';

$pageTitle = 'Registered Customers';
$activeNav = 'users';

$searchQuery = trim($_GET['q'] ?? '');

$sql = "
    SELECT u.*, 
           COALESCE(addr.city, '') as city, 
           COALESCE(addr.state, '') as state,
           COUNT(DISTINCT o.id) as total_orders,
           COALESCE(SUM(o.total_amount), 0) as total_spent,
           fwa.id as app_id,
           fwa.verification_status,
           fwa.redemption_status,
           gt.token,
           gt.status as token_status
    FROM users u
    LEFT JOIN addresses addr ON u.id = addr.user_id AND addr.is_default = 1
    LEFT JOIN orders o ON u.id = o.user_id
    LEFT JOIN free_wine_applications fwa ON u.id = fwa.user_id
    LEFT JOIN gift_tokens gt ON fwa.id = gt.application_id
    WHERE 1=1
";
$params = [];

if (!empty($searchQuery)) {
    $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR u.mobile LIKE ?)";
    $like = "%{$searchQuery}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " GROUP BY u.id ORDER BY u.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/header.php';
?>

<!-- SEARCH AND OVERVIEW -->
<div class="admin-card" style="padding: 18px 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h3 style="font-family: var(--font-serif); font-size: 1.4rem; color: #fff;">
                <i class="fa-solid fa-users" style="color: var(--gold-light);"></i> Customer Accounts
            </h3>
            <span style="font-size: 12.5px; color: var(--text-muted);">Total registered wine enthusiasts: <?php echo count($users); ?></span>
        </div>

        <form method="GET" action="users.php" style="display: flex; gap: 10px; align-items: center;">
            <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search name, phone, email..." 
                   class="admin-form-input" style="padding: 8px 14px; font-size: 13px; width: 240px;">
            <button type="submit" class="btn-gold" style="padding: 8px 14px;"><i class="fa-solid fa-magnifying-glass"></i></button>
            <?php if (!empty($searchQuery)): ?>
                <a href="users.php" class="btn-outline" style="padding: 8px 12px;"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- USERS TABLE -->
<div class="admin-card">
    <?php if (empty($users)): ?>
        <p style="color: var(--text-muted); text-align: center; padding: 40px 0;">No registered users found.</p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Location</th>
                        <th>Orders</th>
                        <th>Total Spend</th>
                        <th>Free Gift Token</th>
                        <th>Joined</th>
                        <th style="text-align: right;">Profile</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><strong>#<?php echo $u['id']; ?></strong></td>
                            <td>
                                <strong style="color: #fff; font-size: 14px;"><?php echo htmlspecialchars($u['full_name']); ?></strong>
                                <?php if (!empty($u['dob'])): ?>
                                    <div style="font-size: 11px; color: var(--text-muted);">
                                        DOB: <?php echo date('d M Y', strtotime($u['dob'])); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div><i class="fa-solid fa-phone" style="font-size:10px; color:var(--gold);"></i> <?php echo htmlspecialchars($u['mobile']); ?></div>
                                <div style="font-size:12px; color:var(--text-muted);"><?php echo htmlspecialchars($u['email']); ?></div>
                            </td>
                            <td>
                                <?php if (!empty($u['city'])): ?>
                                    <div><?php echo htmlspecialchars($u['city']); ?></div>
                                    <small style="color: var(--text-muted);"><?php echo htmlspecialchars($u['state']); ?></small>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 12px;">Not provided</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-shipped"><?php echo $u['total_orders']; ?> orders</span>
                            </td>
                            <td>
                                <strong style="color: var(--gold-light);">₹<?php echo number_format((float)$u['total_spent'], 2); ?></strong>
                            </td>
                            <td>
                                <?php if (!empty($u['token'])): ?>
                                    <code style="background: rgba(212,175,55,0.15); color: var(--gold-light); padding: 3px 6px; border-radius: 4px; font-weight: 600; font-size: 11.5px;">
                                        <?php echo htmlspecialchars($u['token']); ?>
                                    </code>
                                    <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">
                                        <?php echo ($u['redemption_status'] === 'REDEEMED') ? '<span style="color:#4ade80;">REDEEMED</span>' : strtoupper($u['token_status']); ?>
                                    </div>
                                <?php elseif (!empty($u['verification_status'])): ?>
                                    <span class="badge badge-pending"><?php echo strtoupper($u['verification_status']); ?></span>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 11.5px;">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-size: 12px;"><?php echo date('d M Y', strtotime($u['created_at'])); ?></span>
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn-outline" style="padding: 4px 10px; font-size: 11px;" 
                                        onclick="viewUser(<?php echo htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <i class="fa-solid fa-address-card"></i> View
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- USER DETAILS MODAL -->
<div id="userModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); z-index:2000; align-items:center; justify-content:center;">
    <div style="background: var(--admin-card); border: 1px solid var(--gold-border); border-radius: 14px; padding: 32px; max-width: 550px; width: 90%; box-shadow: 0 15px 40px rgba(0,0,0,0.85);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 12px;">
            <h3 style="font-family: var(--font-serif); font-size: 1.55rem; color: #fff;">
                Customer <span id="uModalName" style="color:var(--gold-light);"></span>
            </h3>
            <button type="button" onclick="closeUserModal()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div style="display:flex; flex-direction:column; gap:14px;">
            <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 16px;">
                <h4 style="font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--gold); margin-bottom: 8px;">Contact & Profile</h4>
                <div style="font-size: 13.5px; color:#fff;" id="uModalContact"></div>
                <div style="font-size: 13px; color:var(--text-secondary); margin-top:4px;" id="uModalDob"></div>
                <div style="font-size: 13px; color:var(--text-secondary); margin-top:4px;" id="uModalLocation"></div>
            </div>

            <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 16px;">
                <h4 style="font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--gold); margin-bottom: 8px;">Order & Free Gift Metrics</h4>
                <div style="display:flex; justify-content:space-between; font-size:13px; color:var(--text-secondary);">
                    <span>Total Orders:</span>
                    <strong id="uModalOrders" style="color:#fff;"></strong>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:13px; color:var(--text-secondary); margin-top:4px;">
                    <span>Total Purchases:</span>
                    <strong id="uModalSpent" style="color:var(--gold-light);"></strong>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:13px; color:var(--text-secondary); margin-top:4px;">
                    <span>Free Wine Token:</span>
                    <strong id="uModalToken" style="color:var(--gold-light);"></strong>
                </div>
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
            <a href="orders.php" id="uModalOrderLink" class="btn-outline" style="padding: 8px 16px;">Filter Orders</a>
            <button type="button" onclick="closeUserModal()" class="btn-gold" style="padding: 8px 20px;">Close</button>
        </div>
    </div>
</div>

<script>
function viewUser(u) {
    document.getElementById('uModalName').innerText = u.full_name;
    document.getElementById('uModalContact').innerHTML = `<i class="fa-solid fa-envelope"></i> ${u.email} &nbsp;|&nbsp; <i class="fa-solid fa-phone"></i> ${u.mobile}`;
    document.getElementById('uModalDob').innerHTML = `<strong>Date of Birth:</strong> ${u.dob || 'Not provided'}`;
    document.getElementById('uModalLocation').innerHTML = `<strong>Location:</strong> ${(u.city || '')} ${(u.state ? ', ' + u.state : '')}`;

    document.getElementById('uModalOrders').innerText = u.total_orders + ' order(s)';
    document.getElementById('uModalSpent').innerText = '₹' + parseFloat(u.total_spent).toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('uModalToken').innerText = u.token ? (u.token + ' (' + (u.redemption_status === 'REDEEMED' ? 'REDEEMED' : u.token_status) + ')') : (u.verification_status ? u.verification_status : 'Not Applied');

    document.getElementById('uModalOrderLink').href = 'orders.php?q=' + encodeURIComponent(u.mobile);
    document.getElementById('userModal').style.display = 'flex';
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
