<?php
/**
 * Sam's Fruit Wines - Customer Address Book
 * View, Add, Edit, and Update Delivery Addresses.
 */

require_once __DIR__ . '/../includes/functions.php';
require_user_login('../login.php');

$pdo = getDbConnection();
$userId = (int)$_SESSION['user_id'];
$user = get_logged_in_user($pdo);

$success_msg = '';
$error_msg = '';

// Handle Add / Edit Address
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action_type = $_POST['action_type'] ?? 'add';
    $address_id  = (int)($_POST['address_id'] ?? 0);
    $address     = trim($_POST['address'] ?? '');
    $city        = trim($_POST['city'] ?? '');
    $state       = trim($_POST['state'] ?? 'Maharashtra');
    $pincode     = trim($_POST['pincode'] ?? '');
    $is_default  = isset($_POST['is_default']) ? 1 : 0;

    if (empty($address) || empty($city) || empty($state) || empty($pincode)) {
        $error_msg = 'Please fill all address fields.';
    } elseif (!preg_match('/^[0-9]{6}$/', $pincode)) {
        $error_msg = 'Please enter a valid 6-digit Pincode.';
    } else {
        try {
            $pdo->beginTransaction();

            if ($is_default) {
                // Remove default from other addresses
                $unsetStmt = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
                $unsetStmt->execute([$userId]);
            }

            if ($action_type === 'edit' && $address_id > 0) {
                $stmt = $pdo->prepare("
                    UPDATE addresses 
                    SET address = ?, city = ?, state = ?, pincode = ?, is_default = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$address, $city, $state, $pincode, $is_default, $address_id, $userId]);
                $success_msg = 'Address updated successfully!';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO addresses (user_id, address, city, state, pincode, is_default)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $address, $city, $state, $pincode, $is_default]);
                $success_msg = 'New delivery address added successfully!';
            }

            $pdo->commit();
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_msg = 'Could not save address. Please try again.';
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    $delStmt = $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
    $delStmt->execute([$delId, $userId]);
    $success_msg = 'Address removed.';
}

// Handle Set Default
if (isset($_GET['set_default'])) {
    $defId = (int)$_GET['set_default'];
    $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?")->execute([$userId]);
    $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?")->execute([$defId, $userId]);
    $success_msg = 'Default delivery address updated!';
}

// Fetch user addresses
$addrStmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$addrStmt->execute([$userId]);
$addresses = $addrStmt->fetchAll();

// If editing
$editingAddress = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $eStmt = $pdo->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ? LIMIT 1");
    $eStmt->execute([$editId, $userId]);
    $editingAddress = $eStmt->fetch();
}
$pageTitle = "My Addresses";
$activeNav = "address";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Addresses | Sam's Fruit Wines</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="user-style.css">
    <style>
        .address-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        @media (max-width: 480px) {
            .address-grid {
                grid-template-columns: 1fr;
            }
        }
        .address-box {
            background: #141414;
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 22px;
            position: relative;
            transition: all 0.25s ease;
        }
        .address-box.is-default {
            border-color: var(--gold-light);
            background: radial-gradient(circle at top right, rgba(212, 175, 55, 0.08) 0%, #141414 70%);
        }
        .default-badge {
            display: inline-block;
            background: var(--gold-light);
            color: #000;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            padding: 3px 8px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .address-actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
            padding-top: 14px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 13px;
        }
        .address-actions a {
            color: var(--gold-light);
            text-decoration: none;
            transition: color 0.2s;
        }
        .address-actions a:hover {
            color: #fff;
            text-decoration: underline;
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
                    <h2><i class="fa-solid fa-map-marked-alt"></i> Saved Delivery Addresses</h2>
                    <a href="#address-form" class="btn-gold-sm"><i class="fa-solid fa-plus"></i> Add New Address</a>
                </div>

                <?php if (!empty($success_msg)): ?>
                    <div class="alert-box alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <span><?= htmlspecialchars($success_msg) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_msg)): ?>
                    <div class="alert-box alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span><?= htmlspecialchars($error_msg) ?></span>
                    </div>
                <?php endif; ?>

                <!-- SAVED ADDRESSES LIST -->
                <div class="address-grid">
                    <?php if (empty($addresses)): ?>
                        <div class="empty-state" style="grid-column: 1 / -1;">
                            <i class="fa-solid fa-location-dot"></i>
                            <h3>No Addresses Saved</h3>
                            <p>Add a delivery address below for quick wine checkout.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($addresses as $a): ?>
                            <div class="address-box <?= $a['is_default'] ? 'is-default' : '' ?>">
                                <?php if ($a['is_default']): ?>
                                    <span class="default-badge"><i class="fa-solid fa-star"></i> Default Address</span>
                                <?php endif; ?>
                                <p style="font-size:15px; font-weight:600; color:#fff;"><?= e($user['full_name']) ?></p>
                                <p style="font-size:14px; color:var(--text-secondary); margin-top:6px; line-height:1.5;">
                                    <?= e($a['address']) ?>
                                </p>
                                <p style="font-size:13.5px; color:var(--gold-light); margin-top:4px;">
                                    <?= e($a['city']) ?>, <?= e($a['state']) ?> - <?= e($a['pincode']) ?>
                                </p>
                                <p style="font-size:12.5px; color:var(--text-muted); margin-top:4px;">
                                    Mobile: <?= e($user['mobile']) ?>
                                </p>

                                <div class="address-actions">
                                    <a href="address.php?edit=<?= $a['id'] ?>#address-form"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                    <?php if (!$a['is_default']): ?>
                                        <a href="address.php?set_default=<?= $a['id'] ?>"><i class="fa-solid fa-check"></i> Make Default</a>
                                        <a href="address.php?delete=<?= $a['id'] ?>" onclick="return confirm('Remove this address?');" style="color:#f87171;"><i class="fa-solid fa-trash"></i> Delete</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- ADD / EDIT ADDRESS FORM -->
                <div style="border-top:1px solid var(--border-subtle); padding-top:28px;" id="address-form">
                    <h3 style="font-family:var(--font-serif); font-size:1.5rem; margin-bottom:18px;">
                        <?= $editingAddress ? '<i class="fa-solid fa-pen"></i> Edit Address' : '<i class="fa-solid fa-plus"></i> Add New Delivery Address' ?>
                    </h3>

                    <form method="POST" action="address.php">
                        <input type="hidden" name="action_type" value="<?= $editingAddress ? 'edit' : 'add' ?>">
                        <input type="hidden" name="address_id" value="<?= $editingAddress ? (int)$editingAddress['id'] : 0 ?>">

                        <div class="form-group">
                            <label for="addr-text">Street Address / House / Apartment</label>
                            <textarea id="addr-text" name="address" class="form-control" placeholder="House/Flat No., Street, Landmark" required><?= $editingAddress ? e($editingAddress['address']) : '' ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="addr-city">City</label>
                                <input type="text" id="addr-city" name="city" class="form-control" placeholder="e.g. Nashik, Pune, Mumbai" value="<?= $editingAddress ? e($editingAddress['city']) : '' ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="addr-state">State</label>
                                <input type="text" id="addr-state" name="state" class="form-control" value="<?= $editingAddress ? e($editingAddress['state']) : 'Maharashtra' ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="addr-pin">Pincode</label>
                                <input type="text" id="addr-pin" name="pincode" class="form-control" placeholder="6-digit PIN" maxlength="6" pattern="[0-9]{6}" value="<?= $editingAddress ? e($editingAddress['pincode']) : '' ?>" required>
                            </div>
                            <div class="form-group" style="display:flex; align-items:center; gap:10px; padding-top:24px;">
                                <label style="display:flex; align-items:center; gap:8px; cursor:pointer; text-transform:none; font-size:13px; color:#eee;">
                                    <input type="checkbox" name="is_default" value="1" <?= ($editingAddress && $editingAddress['is_default']) || empty($addresses) ? 'checked' : '' ?> style="accent-color:var(--gold-light); width:18px; height:18px;">
                                    Set as default delivery address
                                </label>
                            </div>
                        </div>

                        <div style="display:flex; gap:12px; margin-top:10px;">
                            <button type="submit" class="btn-gold"><i class="fa-solid fa-floppy-disk"></i> <?= $editingAddress ? 'Update Address' : 'Save Address' ?></button>
                            <?php if ($editingAddress): ?>
                                <a href="address.php" class="btn-trans">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>

</body>
</html>
