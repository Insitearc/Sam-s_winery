<?php
/**
 * Sam's Fruit Wines - Admin Free Wine / Free Gift Management
 * Full verification, token generation, Aadhaar document check & atomic redemption
 */
define('ADMIN_PANEL', true);
require_once __DIR__ . '/auth.php';

$pageTitle = 'Free Wine / Free Gift Applications';
$activeNav = 'free-wine';

$message = '';
$error = '';

// Handle Actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $appId = (int)($_POST['app_id'] ?? 0);

    if ($action === 'verify' && $appId > 0) {
        // Admin verifies Aadhaar and activates unique token
        try {
            $pdo->beginTransaction();

            // Fetch application
            $stmt = $pdo->prepare("SELECT * FROM free_wine_applications WHERE id = ? FOR UPDATE");
            $stmt->execute([$appId]);
            $app = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$app) {
                throw new Exception("Application not found.");
            }

            // Check if token already exists for this application / user
            $stmtToken = $pdo->prepare("SELECT * FROM gift_tokens WHERE application_id = ? OR user_id = ? FOR UPDATE");
            $stmtToken->execute([$app['id'], $app['user_id']]);
            $existingToken = $stmtToken->fetch(PDO::FETCH_ASSOC);

            $tokenCode = '';
            if ($existingToken) {
                $tokenCode = $existingToken['token'];
                // Update to ACTIVE if not already REDEEMED
                if ($existingToken['status'] === 'REDEEMED') {
                    throw new Exception("This user's gift has already been redeemed!");
                }
                $updateTokenStmt = $pdo->prepare("UPDATE gift_tokens SET status = 'ACTIVE', expires_at = DATE_ADD(NOW(), INTERVAL 90 DAY) WHERE id = ?");
                $updateTokenStmt->execute([$existingToken['id']]);
            } else {
                // Generate a unique token
                $tokenCode = generate_unique_token($pdo);
                $insertTokenStmt = $pdo->prepare("
                    INSERT INTO gift_tokens (application_id, user_id, token, status, expires_at)
                    VALUES (?, ?, ?, 'ACTIVE', DATE_ADD(NOW(), INTERVAL 90 DAY))
                ");
                $insertTokenStmt->execute([$app['id'], $app['user_id'], $tokenCode]);
            }

            // Update application verification status
            $updateAppStmt = $pdo->prepare("
                UPDATE free_wine_applications 
                SET verification_status = 'VERIFIED', verified_by = ?, verified_at = NOW() 
                WHERE id = ?
            ");
            $updateAppStmt->execute([$_SESSION['admin_id'], $app['id']]);

            $pdo->commit();
            $message = "Application #{$appId} successfully VERIFIED! Active Token: <strong>{$tokenCode}</strong> has been allocated.";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e->getMessage();
        }
    } elseif ($action === 'reject' && $appId > 0) {
        // Admin rejects application
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT * FROM free_wine_applications WHERE id = ? FOR UPDATE");
            $stmt->execute([$appId]);
            $app = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$app) {
                throw new Exception("Application not found.");
            }

            // If token exists, block it
            $stmtBlock = $pdo->prepare("UPDATE gift_tokens SET status = 'BLOCKED' WHERE user_id = ? OR application_id = ?");
            $stmtBlock->execute([$app['user_id'], $app['id']]);

            // Update application
            $updateStmt = $pdo->prepare("
                UPDATE free_wine_applications 
                SET verification_status = 'REJECTED', verified_by = ?, verified_at = NOW() 
                WHERE id = ?
            ");
            $updateStmt->execute([$_SESSION['admin_id'], $app['id']]);

            $pdo->commit();
            $message = "Application #{$appId} marked as REJECTED.";
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $e->getMessage();
        }
    } elseif ($action === 'redeem') {
        // Atomic redemption with strict ONE PERSON = ONE FREE GIFT guarantee
        $tokenId = (int)($_POST['token_id'] ?? 0);
        $shopId = (int)($_POST['shop_id'] ?? 0);

        if ($tokenId <= 0 || $shopId <= 0) {
            $error = "Please select both an active token and a valid redemption outlet shop.";
        } else {
            try {
                $pdo->beginTransaction();

                // Lock the token row
                $stmtToken = $pdo->prepare("SELECT * FROM gift_tokens WHERE id = ? FOR UPDATE");
                $stmtToken->execute([$tokenId]);
                $token = $stmtToken->fetch(PDO::FETCH_ASSOC);

                if (!$token) {
                    throw new Exception("Token record does not exist.");
                }

                if ($token['status'] === 'REDEEMED') {
                    // Find where it was previously redeemed
                    $stmtPrior = $pdo->prepare("
                        SELECT r.*, s.shop_name 
                        FROM gift_redemptions r 
                        JOIN shops s ON r.shop_id = s.id 
                        WHERE r.token_id = ?
                    ");
                    $stmtPrior->execute([$tokenId]);
                    $prior = $stmtPrior->fetch(PDO::FETCH_ASSOC);
                    $where = $prior ? " at " . $prior['shop_name'] . " on " . date('d M Y, H:i', strtotime($prior['redeemed_at'])) : "";
                    throw new Exception("DUPLICATE REDEMPTION BLOCKED: This token was ALREADY REDEEMED{$where}. Each customer is permitted exactly ONE free gift.");
                }

                if ($token['status'] !== 'ACTIVE') {
                    throw new Exception("This token is not in ACTIVE status (current: " . $token['status'] . "). It cannot be redeemed.");
                }

                // Double check user constraint in gift_redemptions with lock
                $stmtCheckUser = $pdo->prepare("SELECT id FROM gift_redemptions WHERE user_id = ? FOR UPDATE");
                $stmtCheckUser->execute([$token['user_id']]);
                if ($stmtCheckUser->fetch()) {
                    throw new Exception("DUPLICATE REDEMPTION REJECTED: User #{$token['user_id']} has already redeemed a free gift previously. One person = One free gift rule strictly enforced.");
                }

                // Verify shop exists and is active
                $stmtShop = $pdo->prepare("SELECT shop_name FROM shops WHERE id = ? AND status = 'active'");
                $stmtShop->execute([$shopId]);
                $shop = $stmtShop->fetch(PDO::FETCH_ASSOC);
                if (!$shop) {
                    throw new Exception("Selected outlet is invalid or inactive.");
                }

                // Record redemption
                $stmtInsertRed = $pdo->prepare("
                    INSERT INTO gift_redemptions (user_id, token_id, shop_id, gift_id, gift_item_name, redeemed_at, status)
                    VALUES (?, ?, ?, 'SAM-FREE-750ML', 'Sam\'s Reserve Fruit Wine (750 ML)', NOW(), 'REDEEMED')
                ");
                $stmtInsertRed->execute([$token['user_id'], $token['id'], $shopId]);

                // Update token status
                $stmtUpdateTok = $pdo->prepare("UPDATE gift_tokens SET status = 'REDEEMED', updated_at = NOW() WHERE id = ?");
                $stmtUpdateTok->execute([$token['id']]);

                // Update application redemption status
                $stmtUpdateApp = $pdo->prepare("UPDATE free_wine_applications SET redemption_status = 'REDEEMED' WHERE id = ?");
                $stmtUpdateApp->execute([$token['application_id']]);

                $pdo->commit();
                $message = "SUCCESS! Token <strong>{$token['token']}</strong> successfully marked as REDEEMED at <strong>{$shop['shop_name']}</strong>.";
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = $e->getMessage();
            }
        }
    }
}

// Filter and Search parameters
$filterStatus = $_GET['status'] ?? 'ALL';
$searchQuery = trim($_GET['q'] ?? '');

$sql = "
    SELECT a.*, 
           t.id as token_id, t.token, t.status as token_status, t.expires_at,
           adm.name as verified_by_name,
           r.redeemed_at, r.shop_id, s.shop_name as redeemed_shop_name
    FROM free_wine_applications a
    LEFT JOIN gift_tokens t ON a.id = t.application_id
    LEFT JOIN admins adm ON a.verified_by = adm.id
    LEFT JOIN gift_redemptions r ON t.id = r.token_id
    LEFT JOIN shops s ON r.shop_id = s.id
    WHERE 1=1
";
$params = [];

if ($filterStatus !== 'ALL') {
    if ($filterStatus === 'REDEEMED') {
        $sql .= " AND a.redemption_status = 'REDEEMED'";
    } else {
        $sql .= " AND a.verification_status = ?";
        $params[] = $filterStatus;
    }
}

if (!empty($searchQuery)) {
    $sql .= " AND (a.name LIKE ? OR a.email LIKE ? OR a.mobile LIKE ? OR t.token LIKE ?)";
    $like = "%{$searchQuery}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY a.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all active shops for redemption dropdown
$shops = $pdo->query("SELECT id, shop_name, city FROM shops WHERE status = 'active' ORDER BY city, shop_name")->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/header.php';
?>

<?php if ($message): ?>
    <div class="admin-alert admin-alert-success">
        <i class="fa-solid fa-circle-check"></i>
        <span><?php echo $message; ?></span>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="admin-alert admin-alert-error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span><?php echo $error; ?></span>
    </div>
<?php endif; ?>

<!-- TOP ACTION & FILTER BAR -->
<div class="admin-card" style="padding: 18px 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="free-wine.php" class="btn-outline <?php echo ($filterStatus === 'ALL' && empty($searchQuery)) ? 'active' : ''; ?>">All Applications</a>
            <a href="free-wine.php?status=PENDING" class="btn-outline <?php echo ($filterStatus === 'PENDING') ? 'active' : ''; ?>">Pending Review</a>
            <a href="free-wine.php?status=VERIFIED" class="btn-outline <?php echo ($filterStatus === 'VERIFIED') ? 'active' : ''; ?>">Verified & Active</a>
            <a href="free-wine.php?status=REDEEMED" class="btn-outline <?php echo ($filterStatus === 'REDEEMED') ? 'active' : ''; ?>">Redeemed</a>
            <a href="free-wine.php?status=REJECTED" class="btn-outline <?php echo ($filterStatus === 'REJECTED') ? 'active' : ''; ?>">Rejected</a>
        </div>

        <form method="GET" action="free-wine.php" style="display: flex; gap: 10px; align-items: center;">
            <?php if ($filterStatus !== 'ALL'): ?>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($filterStatus); ?>">
            <?php endif; ?>
            <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search name, phone, token..." 
                   class="admin-form-input" style="padding: 8px 14px; font-size: 13px; width: 230px;">
            <button type="submit" class="btn-gold" style="padding: 8px 14px;"><i class="fa-solid fa-magnifying-glass"></i></button>
            <?php if (!empty($searchQuery)): ?>
                <a href="free-wine.php" class="btn-outline" style="padding: 8px 12px;" title="Clear search"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- APPLICATIONS TABLE -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fa-solid fa-award"></i> Free Gift Applications List (<?php echo count($applications); ?>)</h3>
        <span style="font-size: 12.5px; color: var(--text-muted);">
            <i class="fa-solid fa-shield-halved" style="color: var(--gold-light);"></i> Strict 1-Bottle Rule Enforced
        </span>
    </div>

    <?php if (empty($applications)): ?>
        <p style="color: var(--text-muted); text-align: center; padding: 40px 0;">No Free Wine applications match your filter criteria.</p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table admin-table-wide">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Applicant</th>
                        <th>Contact</th>
                        <th>DOB</th>
                        <th>Aadhaar</th>
                        <th>Verification</th>
                        <th>Token</th>
                        <th>Applied On</th>
                        <th style="text-align: right; width: 180px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): 
                        $vStat = strtoupper($app['verification_status'] ?? 'PENDING');
                        $tStat = strtoupper($app['token_status'] ?? 'PENDING');
                        $rStat = strtoupper($app['redemption_status'] ?? 'NOT_REDEEMED');
                    ?>
                        <tr id="app-row-<?php echo $app['id']; ?>">
                            <td><strong style="color: var(--gold-light);">#<?php echo $app['id']; ?></strong></td>
                            <td>
                                <strong style="color: #fff; font-size: 14px;"><?php echo htmlspecialchars($app['name']); ?></strong>
                                <span style="font-size: 11px; color: var(--text-muted); margin-left: 4px;">(#<?php echo $app['user_id']; ?>)</span>
                            </td>
                            <td>
                                <span><i class="fa-solid fa-phone" style="font-size:10px; color:var(--gold); margin-right:3px;"></i> <?php echo htmlspecialchars($app['mobile']); ?></span>
                                <span style="color: rgba(255,255,255,0.2); margin: 0 4px;">|</span>
                                <span style="font-size:12px; color:var(--text-muted);"><?php echo htmlspecialchars($app['email']); ?></span>
                            </td>
                            <td>
                                <strong style="color: #eee;"><?php echo date('d M Y', strtotime($app['dob'])); ?></strong>
                                <span style="font-size: 11px; color: var(--gold); margin-left: 3px;">
                                    <?php 
                                        $age = (new DateTime($app['dob']))->diff(new DateTime())->y;
                                        echo "({$age}y)";
                                    ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($app['aadhaar_file'])): ?>
                                    <a href="view-aadhaar.php?id=<?php echo $app['id']; ?>" target="_blank" class="btn-outline" style="padding: 5px 11px; font-size: 11px;" title="Secure view Aadhaar">
                                        <i class="fa-solid fa-id-card"></i> View Aadhaar
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size:12px;">Not Uploaded</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $vBadge = 'badge-pending';
                                    if ($vStat === 'VERIFIED') $vBadge = 'badge-verified';
                                    elseif ($vStat === 'REJECTED') $vBadge = 'badge-rejected';
                                ?>
                                <span class="badge <?php echo $vBadge; ?>"><?php echo $vStat; ?></span>
                                <?php if (!empty($app['verified_by_name'])): ?>
                                    <span style="font-size: 10px; color: var(--text-muted); margin-left: 3px;">
                                        (by <?php echo htmlspecialchars($app['verified_by_name']); ?>)
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($app['token'])): ?>
                                    <code style="background: rgba(212,175,55,0.15); color: var(--gold-light); padding: 4px 8px; border-radius: 4px; font-weight: 700; font-size: 12px; letter-spacing: 0.5px;">
                                        <?php echo htmlspecialchars($app['token']); ?>
                                    </code>
                                    <?php if ($rStat === 'REDEEMED'): ?>
                                        <span class="badge badge-redeemed" style="margin-left: 6px;" title="<?php echo htmlspecialchars($app['redeemed_shop_name'] ?? 'Outlet'); ?> on <?php echo date('d M Y', strtotime($app['redeemed_at'])); ?>">
                                            REDEEMED
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-active" style="margin-left: 6px;"><?php echo $tStat; ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 12px;">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span style="font-size: 12.5px; color: var(--text-secondary);"><?php echo date('d M Y, H:i', strtotime($app['created_at'])); ?></span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <?php if ($vStat === 'PENDING' || $vStat === 'REJECTED'): ?>
                                        <!-- Verify Button -->
                                        <form method="POST" action="free-wine.php" onsubmit="return confirm('Confirm DOB matches Aadhaar and activate token for <?php echo htmlspecialchars(addslashes($app['name'])); ?>?');" style="display:inline;">
                                            <input type="hidden" name="action" value="verify">
                                            <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                            <button type="submit" class="btn-gold" style="padding: 5px 10px; font-size: 11px;" title="Verify & Allocate Token">
                                                <i class="fa-solid fa-check"></i> Verify
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($vStat === 'PENDING' || $vStat === 'VERIFIED'): ?>
                                        <?php if ($rStat !== 'REDEEMED'): ?>
                                            <!-- Reject Button -->
                                            <form method="POST" action="free-wine.php" onsubmit="return confirm('Reject application #<?php echo $app['id']; ?>? This will block their token.');" style="display:inline;">
                                                <input type="hidden" name="action" value="reject">
                                                <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                                                <button type="submit" class="btn-danger-sm" style="padding: 5px 8px; font-size: 11px;" title="Reject Application">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($vStat === 'VERIFIED' && $tStat === 'ACTIVE' && $rStat !== 'REDEEMED'): ?>
                                        <!-- Redeem Trigger Modal Button -->
                                        <button type="button" class="btn-outline" style="padding: 5px 10px; font-size: 11px; border-color: #22c55e; color: #4ade80;" 
                                                onclick="openRedeemModal(<?php echo $app['token_id']; ?>, '<?php echo htmlspecialchars(addslashes($app['token'])); ?>', '<?php echo htmlspecialchars(addslashes($app['name'])); ?>')">
                                            <i class="fa-solid fa-wine-glass"></i> Redeem
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- REDEEM MODAL (Admin Outlet Walk-In Record) -->
<div id="redeemModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); z-index:2000; align-items:center; justify-content:center;">
    <div style="background: var(--admin-card); border: 1px solid var(--gold-border); border-radius: 14px; padding: 30px; max-width: 480px; width: 90%; box-shadow: 0 15px 40px rgba(0,0,0,0.8);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-family: var(--font-serif); font-size: 1.5rem; color: #fff;">
                <i class="fa-solid fa-wine-bottle" style="color: var(--gold-light);"></i> Redeem Free Bottle
            </h3>
            <button type="button" onclick="closeRedeemModal()" style="background:none; border:none; color:var(--text-muted); font-size:18px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="POST" action="free-wine.php" onsubmit="return confirm('Confirm bottle handover? This action is permanent and prevents any second redemption.');">
            <input type="hidden" name="action" value="redeem">
            <input type="hidden" name="token_id" id="modalTokenId" value="">

            <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 14px; margin-bottom: 20px;">
                <div style="font-size: 12px; color: var(--text-muted);">Customer: <strong id="modalCustomerName" style="color:#fff;"></strong></div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Token: <code id="modalTokenCode" style="color:var(--gold-light); font-weight:700;"></code></div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Free Gift: <strong style="color:#fff;">Sam's Reserve Fruit Wine (750 ML)</strong></div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display:block; font-size:12.5px; margin-bottom:8px; color:var(--text-secondary); font-weight:500;">
                    Select Redemption Outlet / Shop: <span style="color:#ef4444;">*</span>
                </label>
                <select name="shop_id" required class="admin-form-input" style="width: 100%; padding: 10px 14px; font-size: 13.5px; background: #000; border: 1px solid var(--gold-border); color: #fff; border-radius: 6px;">
                    <option value="">-- Choose Authorized Cellar / Outlet --</option>
                    <?php foreach ($shops as $shop): ?>
                        <option value="<?php echo $shop['id']; ?>">
                            <?php echo htmlspecialchars($shop['city'] . ' - ' . $shop['shop_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" onclick="closeRedeemModal()" class="btn-outline" style="padding: 10px 18px;">Cancel</button>
                <button type="submit" class="btn-gold" style="padding: 10px 22px;">Confirm & Redeem</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRedeemModal(tokenId, tokenCode, customerName) {
    document.getElementById('modalTokenId').value = tokenId;
    document.getElementById('modalTokenCode').innerText = tokenCode;
    document.getElementById('modalCustomerName').innerText = customerName;
    document.getElementById('redeemModal').style.display = 'flex';
}

function closeRedeemModal() {
    document.getElementById('redeemModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
