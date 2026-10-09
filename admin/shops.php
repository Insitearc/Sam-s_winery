<?php
/**
 * Sam's Fruit Wines - Admin Redemption Outlets (Shops) Management
 * Add, edit, activate, and deactivate partner cellars & tasting lounges
 */
define('ADMIN_PANEL', true);
require_once __DIR__ . '/auth.php';

$pageTitle = 'Redemption Shops & Outlets';
$activeNav = 'shops';

$message = '';
$error = '';

// Helper function for uploading shop images
function upload_shop_image(&$uploadError) {
    if (!isset($_FILES['shop_image']) || $_FILES['shop_image']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES['shop_image'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadError = "Error uploading image (Code: " . $file['error'] . ").";
        return false;
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        $uploadError = "Image file size exceeds 5MB limit.";
        return false;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
    if (!in_array($ext, $allowedExts)) {
        $uploadError = "Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.";
        return false;
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    $allowedMimes = ['image/jpeg', 'image/pjpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowedMimes)) {
        $uploadError = "Uploaded file is not a valid image.";
        return false;
    }
    $targetDir = __DIR__ . '/../uploads/shops';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $safeName = 'shop_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $targetDir . '/' . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $uploadError = "Failed to store uploaded image on server.";
        return false;
    }
    return 'uploads/shops/' . $safeName;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $shopName = trim($_POST['shop_name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? 'Maharashtra');
        $contact = trim($_POST['contact_number'] ?? '');

        $uploadErr = '';
        $uploadedPath = upload_shop_image($uploadErr);

        if ($uploadedPath === false) {
            $error = $uploadErr;
        } elseif (empty($shopName) || empty($address) || empty($city)) {
            $error = "Shop name, address, and city are mandatory fields.";
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO shops (shop_name, address, city, state, contact_number, image, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
                ");
                $stmt->execute([$shopName, $address, $city, $state, $contact, $uploadedPath]);
                $message = "New outlet '{$shopName}' added successfully!";
            } catch (Exception $e) {
                $error = "Database error adding shop: " . $e->getMessage();
            }
        }
    } elseif ($action === 'edit') {
        $shopId = (int)($_POST['shop_id'] ?? 0);
        $shopName = trim($_POST['shop_name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? 'Maharashtra');
        $contact = trim($_POST['contact_number'] ?? '');

        $uploadErr = '';
        $uploadedPath = upload_shop_image($uploadErr);

        if ($uploadedPath === false) {
            $error = $uploadErr;
        } elseif ($shopId <= 0 || empty($shopName) || empty($address) || empty($city)) {
            $error = "Invalid shop details specified for update.";
        } else {
            try {
                if ($uploadedPath) {
                    $stmt = $pdo->prepare("
                        UPDATE shops 
                        SET shop_name = ?, address = ?, city = ?, state = ?, contact_number = ?, image = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$shopName, $address, $city, $state, $contact, $uploadedPath, $shopId]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE shops 
                        SET shop_name = ?, address = ?, city = ?, state = ?, contact_number = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$shopName, $address, $city, $state, $contact, $shopId]);
                }
                $message = "Outlet details for '{$shopName}' updated successfully!";
            } catch (Exception $e) {
                $error = "Database error updating shop: " . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle_status') {
        $shopId = (int)($_POST['shop_id'] ?? 0);
        $newStatus = ($_POST['status'] === 'active') ? 'active' : 'inactive';

        if ($shopId > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE shops SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$newStatus, $shopId]);
                $message = "Shop status updated to " . strtoupper($newStatus) . ".";
            } catch (Exception $e) {
                $error = "Database error toggling status: " . $e->getMessage();
            }
        }
    }
}

// Fetch all shops with redemptions count
$stmtShops = $pdo->query("
    SELECT s.*, COUNT(r.id) as total_redemptions 
    FROM shops s 
    LEFT JOIN gift_redemptions r ON s.id = r.shop_id 
    GROUP BY s.id 
    ORDER BY s.city, s.shop_name
");
$shops = $stmtShops->fetchAll(PDO::FETCH_ASSOC);

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

<!-- ACTION BAR -->
<div class="admin-card" style="padding: 18px 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h3 style="font-family: var(--font-serif); font-size: 1.4rem; color: #fff;">
                <i class="fa-solid fa-store" style="color: var(--gold-light);"></i> Authorized Tasting Lounges & Outlets
            </h3>
            <span style="font-size: 12.5px; color: var(--text-muted);">Configured physical locations for customer Free Wine bottle collection</span>
        </div>

        <button type="button" class="btn-gold" onclick="openAddModal()">
            <i class="fa-solid fa-plus"></i> Add New Outlet
        </button>
    </div>
</div>

<!-- SHOPS TABLE -->
<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Outlet Name</th>
                    <th>Address</th>
                    <th>City & State</th>
                    <th>Contact Phone</th>
                    <th>Bottles Redeemed</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shops as $s): ?>
                    <tr>
                        <td><strong>#<?php echo $s['id']; ?></strong></td>
                        <td>
                            <?php if (!empty($s['image'])): ?>
                                <img src="../<?php echo htmlspecialchars($s['image']); ?>" alt="Outlet" style="width:38px; height:38px; object-fit:cover; border-radius:6px; border:1px solid var(--border-subtle); display:block;">
                            <?php else: ?>
                                <div style="width:38px; height:38px; border-radius:6px; background:rgba(255,255,255,0.05); border:1px solid var(--border-subtle); display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:14px;">
                                    <i class="fa-solid fa-store"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="color: #fff; font-size: 14px;"><?php echo htmlspecialchars($s['shop_name']); ?></strong>
                        </td>
                        <td>
                            <span style="font-size: 12.5px; color: var(--text-secondary);"><?php echo htmlspecialchars($s['address']); ?></span>
                        </td>
                        <td>
                            <strong style="color: #eee;"><?php echo htmlspecialchars($s['city']); ?></strong>
                            <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($s['state']); ?></div>
                        </td>
                        <td>
                            <?php if (!empty($s['contact_number'])): ?>
                                <span style="font-size: 12px;"><i class="fa-solid fa-phone" style="color:var(--gold); font-size:10px;"></i> <?php echo htmlspecialchars($s['contact_number']); ?></span>
                            <?php else: ?>
                                <span style="color: var(--text-muted); font-size: 12px;">N/A</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-redeemed"><?php echo $s['total_redemptions']; ?> redeemed</span>
                        </td>
                        <td>
                            <?php if ($s['status'] === 'active'): ?>
                                <span class="badge badge-active">ACTIVE</span>
                            <?php else: ?>
                                <span class="badge badge-blocked">INACTIVE</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; gap: 8px;">
                                <button type="button" class="btn-outline" style="padding: 4px 10px; font-size: 11px;" 
                                        onclick="openEditModal(<?php echo htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>

                                <form method="POST" action="shops.php" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="shop_id" value="<?php echo $s['id']; ?>">
                                    <input type="hidden" name="status" value="<?php echo ($s['status'] === 'active') ? 'inactive' : 'active'; ?>">
                                    <?php if ($s['status'] === 'active'): ?>
                                        <button type="submit" class="btn-danger-sm" style="padding: 4px 8px; font-size: 11px;" title="Deactivate Shop">
                                            Deactivate
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="btn-outline" style="padding: 4px 8px; font-size: 11px; color: #4ade80; border-color: #22c55e;" title="Activate Shop">
                                            Activate
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD/EDIT SHOP MODAL -->
<div id="shopModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); z-index:2000; align-items:center; justify-content:center;">
    <div style="background: var(--admin-card); border: 1px solid var(--gold-border); border-radius: 14px; padding: 32px; max-width: 520px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 15px 40px rgba(0,0,0,0.85);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 12px;">
            <h3 style="font-family: var(--font-serif); font-size: 1.5rem; color: #fff;" id="modalTitle">
                Add Outlet
            </h3>
            <button type="button" onclick="closeShopModal()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="POST" action="shops.php" enctype="multipart/form-data">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="shop_id" id="formShopId" value="">

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-size:12.5px; margin-bottom:6px; color:var(--text-secondary); font-weight:500;">Outlet / Cellar Name *</label>
                <input type="text" name="shop_name" id="fShopName" required class="admin-form-input" style="width:100%; padding:9px 12px;" placeholder="e.g. Sam's Cellar Lounge Koregaon Park">
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-size:12.5px; margin-bottom:6px; color:var(--text-secondary); font-weight:500;">Street Address *</label>
                <textarea name="address" id="fAddress" required class="admin-form-input" style="width:100%; padding:9px 12px; height:70px; resize:vertical;" placeholder="Full street address"></textarea>
            </div>

            <div class="admin-grid-2" style="margin-bottom: 16px;">
                <div>
                    <label style="display:block; font-size:12.5px; margin-bottom:6px; color:var(--text-secondary); font-weight:500;">City *</label>
                    <input type="text" name="city" id="fCity" required class="admin-form-input" style="width:100%; padding:9px 12px;" placeholder="e.g. Pune">
                </div>
                <div>
                    <label style="display:block; font-size:12.5px; margin-bottom:6px; color:var(--text-secondary); font-weight:500;">State</label>
                    <input type="text" name="state" id="fState" value="Maharashtra" class="admin-form-input" style="width:100%; padding:9px 12px;">
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display:block; font-size:12.5px; margin-bottom:6px; color:var(--text-secondary); font-weight:500;">Contact Phone</label>
                <input type="text" name="contact_number" id="fContact" class="admin-form-input" style="width:100%; padding:9px 12px;" placeholder="+91 98220 00000">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display:block; font-size:12.5px; margin-bottom:6px; color:var(--text-secondary); font-weight:500;">
                    <i class="fa-solid fa-cloud-arrow-up" style="color:var(--gold-light);"></i> Outlet Image (Upload)
                </label>
                <div style="display:flex; align-items:center; gap:12px; background:rgba(255,255,255,0.03); border:1px solid var(--border-subtle); border-radius:6px; padding:6px 10px;">
                    <img id="fCurrentImagePreview" src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'><rect fill='%23222' width='40' height='40'/><text fill='%23888' x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' font-size='10'>SHOP</text></svg>" alt="Preview" 
                         style="width:40px; height:40px; object-fit:cover; border-radius:4px; background:rgba(0,0,0,0.4); flex-shrink:0;">
                    <input type="file" name="shop_image" id="fShopImage" accept=".jpg,.jpeg,.png,.webp" class="admin-form-input" style="flex:1; border:none; padding:4px; font-size:12px; background:transparent;">
                </div>
                <small style="color:var(--text-muted); font-size:11px; margin-top:4px; display:block;">Select JPG, JPEG, PNG, or WEBP (Max 5MB).</small>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" onclick="closeShopModal()" class="btn-outline" style="padding: 9px 18px;">Cancel</button>
                <button type="submit" class="btn-gold" style="padding: 9px 22px;" id="modalSubmitBtn">Save Outlet</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('fShopImage');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    document.getElementById('fCurrentImagePreview').src = evt.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});

function openAddModal() {
    document.getElementById('modalTitle').innerText = 'Add New Outlet';
    document.getElementById('formAction').value = 'add';
    document.getElementById('formShopId').value = '';
    document.getElementById('fShopName').value = '';
    document.getElementById('fAddress').value = '';
    document.getElementById('fCity').value = '';
    document.getElementById('fState').value = 'Maharashtra';
    document.getElementById('fContact').value = '';
    document.getElementById('fShopImage').value = '';
    document.getElementById('fCurrentImagePreview').src = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'><rect fill='%23222' width='40' height='40'/><text fill='%23888' x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' font-size='10'>SHOP</text></svg>";
    document.getElementById('modalSubmitBtn').innerText = 'Add Outlet';
    document.getElementById('shopModal').style.display = 'flex';
}

function openEditModal(s) {
    document.getElementById('modalTitle').innerText = 'Edit Outlet Details';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('formShopId').value = s.id;
    document.getElementById('fShopName').value = s.shop_name;
    document.getElementById('fAddress').value = s.address;
    document.getElementById('fCity').value = s.city;
    document.getElementById('fState').value = s.state || 'Maharashtra';
    document.getElementById('fContact').value = s.contact_number || '';
    document.getElementById('fShopImage').value = '';
    if (s.image) {
        document.getElementById('fCurrentImagePreview').src = '../' + s.image;
    } else {
        document.getElementById('fCurrentImagePreview').src = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'><rect fill='%23222' width='40' height='40'/><text fill='%23888' x='50%25' y='55%25' dominant-baseline='middle' text-anchor='middle' font-size='10'>SHOP</text></svg>";
    }
    document.getElementById('modalSubmitBtn').innerText = 'Update Outlet';
    document.getElementById('shopModal').style.display = 'flex';
}

function closeShopModal() {
    document.getElementById('shopModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
