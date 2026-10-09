<?php
/**
 * Sam's Fruit Wines - Admin Wine Products Catalog Management
 * Manage wines, inventory, pricing, bottle sizes, and availability
 */
define('ADMIN_PANEL', true);
require_once __DIR__ . '/auth.php';

$pageTitle = 'Wine Products Catalog';
$activeNav = 'products';

$message = '';
$error = '';

// Helper function for uploading product images
function upload_product_image(&$uploadError) {
    if (!isset($_FILES['product_image']) || $_FILES['product_image']['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $file = $_FILES['product_image'];
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
    $targetDir = __DIR__ . '/../uploads/products';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $safeName = 'wine_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $targetDir . '/' . $safeName;
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        $uploadError = "Failed to store uploaded image on server.";
        return false;
    }
    return 'uploads/products/' . $safeName;
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'Fruit Wine');
        $tagline = trim($_POST['tagline'] ?? '');
        $bottleSize = trim($_POST['bottle_size'] ?? '750 ML');
        $alcoholContent = trim($_POST['alcohol_content'] ?? '11.5% ABV');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 50);

        $uploadErr = '';
        $uploadedPath = upload_product_image($uploadErr);

        if ($uploadedPath === false) {
            $error = $uploadErr;
        } elseif (empty($name) || $price <= 0) {
            $error = "Product name and a valid price are required.";
        } else {
            $imagePath = $uploadedPath ?: 'images/RUBY RICH.png';
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO products (name, category, tagline, bottle_size, alcohol_content, price, stock, image, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
                ");
                $stmt->execute([$name, $category, $tagline, $bottleSize, $alcoholContent, $price, $stock, $imagePath]);
                $message = "Wine product '{$name}' successfully added to catalog!";
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    } elseif ($action === 'edit') {
        $id = (int)($_POST['product_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? 'Fruit Wine');
        $tagline = trim($_POST['tagline'] ?? '');
        $bottleSize = trim($_POST['bottle_size'] ?? '750 ML');
        $alcoholContent = trim($_POST['alcohol_content'] ?? '11.5% ABV');
        $price = (float)($_POST['price'] ?? 0);
        $stock = (int)($_POST['stock'] ?? 50);

        $uploadErr = '';
        $uploadedPath = upload_product_image($uploadErr);

        if ($uploadedPath === false) {
            $error = $uploadErr;
        } elseif ($id <= 0 || empty($name) || $price <= 0) {
            $error = "Invalid product details for update.";
        } else {
            try {
                if ($uploadedPath) {
                    $stmt = $pdo->prepare("
                        UPDATE products 
                        SET name = ?, category = ?, tagline = ?, bottle_size = ?, alcohol_content = ?, price = ?, stock = ?, image = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $category, $tagline, $bottleSize, $alcoholContent, $price, $stock, $uploadedPath, $id]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE products 
                        SET name = ?, category = ?, tagline = ?, bottle_size = ?, alcohol_content = ?, price = ?, stock = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $category, $tagline, $bottleSize, $alcoholContent, $price, $stock, $id]);
                }
                $message = "Product details for '{$name}' updated successfully!";
            } catch (Exception $e) {
                $error = "Database error updating product: " . $e->getMessage();
            }
        }
    } elseif ($action === 'toggle_status') {
        $id = (int)($_POST['product_id'] ?? 0);
        $newStatus = ($_POST['status'] === 'active') ? 'active' : 'inactive';

        if ($id > 0) {
            try {
                $stmt = $pdo->prepare("UPDATE products SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$newStatus, $id]);
                $message = "Product visibility updated to " . strtoupper($newStatus) . ".";
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch all products
$products = $pdo->query("SELECT * FROM products ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

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
                <i class="fa-solid fa-wine-bottle" style="color: var(--gold-light);"></i> Fruit Wines & Reserves Catalog
            </h3>
            <span style="font-size: 12.5px; color: var(--text-muted);">Manage vintage products, bottle sizes, live pricing, and cellar inventory</span>
        </div>

        <button type="button" class="btn-gold" onclick="openAddModal()">
            <i class="fa-solid fa-plus"></i> Add New Wine
        </button>
    </div>
</div>

<!-- PRODUCTS TABLE -->
<div class="admin-card">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Wine</th>
                    <th>Category</th>
                    <th>Size & ABV</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <img src="../<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" 
                                     style="width: 36px; height: 50px; object-fit: contain; background: rgba(255,255,255,0.03); border-radius: 4px; padding: 2px;"
                                     onerror="this.src='../assets/images/logo.png'">
                                <div>
                                    <strong style="color: #fff; font-size: 14px;"><?php echo htmlspecialchars($p['name']); ?></strong>
                                    <div style="font-size: 11.5px; color: var(--text-muted);"><?php echo htmlspecialchars($p['tagline'] ?? ''); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge" style="background: rgba(212,175,55,0.1); color: var(--gold-light); border: 1px solid var(--gold-border);">
                                <?php echo htmlspecialchars($p['category']); ?>
                            </span>
                        </td>
                        <td>
                            <div><strong><?php echo htmlspecialchars($p['bottle_size']); ?></strong></div>
                            <small style="color: var(--text-muted);"><?php echo htmlspecialchars($p['alcohol_content']); ?></small>
                        </td>
                        <td>
                            <strong style="color: var(--gold-light); font-size: 14.5px;">₹<?php echo number_format((float)$p['price'], 2); ?></strong>
                        </td>
                        <td>
                            <?php if ($p['stock'] > 10): ?>
                                <span style="color: #4ade80; font-weight: 600;"><?php echo $p['stock']; ?> in stock</span>
                            <?php else: ?>
                                <span style="color: #f87171; font-weight: 600;"><?php echo $p['stock']; ?> low stock</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['status'] === 'active'): ?>
                                <span class="badge badge-active">ACTIVE</span>
                            <?php else: ?>
                                <span class="badge badge-blocked">INACTIVE</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; gap: 8px;">
                                <button type="button" class="btn-outline" style="padding: 4px 10px; font-size: 11px;" 
                                        onclick="openEditModal(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>

                                <form method="POST" action="products.php" style="display:inline;">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="status" value="<?php echo ($p['status'] === 'active') ? 'inactive' : 'active'; ?>">
                                    <?php if ($p['status'] === 'active'): ?>
                                        <button type="submit" class="btn-danger-sm" style="padding: 4px 8px; font-size: 11px;">Hide</button>
                                    <?php else: ?>
                                        <button type="submit" class="btn-outline" style="padding: 4px 8px; font-size: 11px; color: #4ade80; border-color: #22c55e;">Show</button>
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

<!-- ADD/EDIT PRODUCT MODAL -->
<div id="productModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); z-index:2000; align-items:center; justify-content:center;">
    <div style="background: var(--admin-card); border: 1px solid var(--gold-border); border-radius: 14px; padding: 32px; max-width: 550px; width: 92%; max-height: 90vh; overflow-y: auto; box-shadow: 0 15px 40px rgba(0,0,0,0.85);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 12px;">
            <h3 style="font-family: var(--font-serif); font-size: 1.5rem; color: #fff;" id="prodModalTitle">
                Add Wine
            </h3>
            <button type="button" onclick="closeProductModal()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="POST" action="products.php" enctype="multipart/form-data">
            <input type="hidden" name="action" id="pAction" value="add">
            <input type="hidden" name="product_id" id="pId" value="">

            <div style="margin-bottom: 14px;">
                <label style="display:block; font-size:12px; margin-bottom:5px; color:var(--text-secondary); font-weight:500;">Wine Product Name *</label>
                <input type="text" name="name" id="pName" required class="admin-form-input" style="width:100%; padding:9px 12px;" placeholder="e.g. Sam's Pomegranate Wine">
            </div>

            <div class="admin-grid-2" style="margin-bottom: 14px;">
                <div>
                    <label style="display:block; font-size:12px; margin-bottom:5px; color:var(--text-secondary); font-weight:500;">Category</label>
                    <input type="text" name="category" id="pCategory" class="admin-form-input" style="width:100%; padding:9px 12px;" placeholder="e.g. Ruby Rich Reserve">
                </div>
                <div>
                    <label style="display:block; font-size:12px; margin-bottom:5px; color:var(--text-secondary); font-weight:500;">Bottle Size</label>
                    <input type="text" name="bottle_size" id="pSize" value="750 ML" class="admin-form-input" style="width:100%; padding:9px 12px;">
                </div>
            </div>

            <div class="admin-grid-2" style="margin-bottom: 14px;">
                <div>
                    <label style="display:block; font-size:12px; margin-bottom:5px; color:var(--text-secondary); font-weight:500;">Price (₹) *</label>
                    <input type="number" step="0.01" name="price" id="pPrice" required class="admin-form-input" style="width:100%; padding:9px 12px;" placeholder="2399.00">
                </div>
                <div>
                    <label style="display:block; font-size:12px; margin-bottom:5px; color:var(--text-secondary); font-weight:500;">Stock Inventory</label>
                    <input type="number" name="stock" id="pStock" value="50" class="admin-form-input" style="width:100%; padding:9px 12px;">
                </div>
            </div>

            <div class="admin-grid-2" style="margin-bottom: 14px;">
                <div>
                    <label style="display:block; font-size:12px; margin-bottom:5px; color:var(--text-secondary); font-weight:500;">Alcohol % (ABV)</label>
                    <input type="text" name="alcohol_content" id="pAbv" value="11.5% ABV" class="admin-form-input" style="width:100%; padding:9px 12px;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; margin-bottom:5px; color:var(--text-secondary); font-weight:500;">
                        <i class="fa-solid fa-cloud-arrow-up" style="color:var(--gold-light);"></i> Wine Bottle Image (Upload)
                    </label>
                    <div style="display:flex; align-items:center; gap:12px; background:rgba(255,255,255,0.03); border:1px solid var(--border-subtle); border-radius:6px; padding:6px 10px;">
                        <img id="pCurrentImagePreview" src="../images/RUBY RICH.png" alt="Preview" 
                             style="width:32px; height:46px; object-fit:contain; border-radius:4px; background:rgba(0,0,0,0.4); padding:2px; flex-shrink:0;">
                        <input type="file" name="product_image" id="pProductImage" accept=".jpg,.jpeg,.png,.webp" class="admin-form-input" style="flex:1; border:none; padding:4px; font-size:12px; background:transparent;">
                    </div>
                    <small style="color:var(--text-muted); font-size:11px; margin-top:4px; display:block;">Select JPG, JPEG, PNG, or WEBP (Max 5MB).</small>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display:block; font-size:12px; margin-bottom:5px; color:var(--text-secondary); font-weight:500;">Tagline / Summary</label>
                <input type="text" name="tagline" id="pTagline" class="admin-form-input" style="width:100%; padding:9px 12px;" placeholder="Handcrafted from fresh residue-free fruits">
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" onclick="closeProductModal()" class="btn-outline" style="padding: 9px 18px;">Cancel</button>
                <button type="submit" class="btn-gold" style="padding: 9px 22px;" id="pSubmitBtn">Save Wine</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('pProductImage');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    document.getElementById('pCurrentImagePreview').src = evt.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});

function openAddModal() {
    document.getElementById('prodModalTitle').innerText = 'Add New Wine to Catalog';
    document.getElementById('pAction').value = 'add';
    document.getElementById('pId').value = '';
    document.getElementById('pName').value = '';
    document.getElementById('pCategory').value = 'Fruit Wine';
    document.getElementById('pSize').value = '750 ML';
    document.getElementById('pPrice').value = '2399.00';
    document.getElementById('pStock').value = '50';
    document.getElementById('pAbv').value = '11.5% ABV';
    document.getElementById('pProductImage').value = '';
    document.getElementById('pCurrentImagePreview').src = '../images/RUBY RICH.png';
    document.getElementById('pTagline').value = '';
    document.getElementById('pSubmitBtn').innerText = 'Add Wine';
    document.getElementById('productModal').style.display = 'flex';
}

function openEditModal(p) {
    document.getElementById('prodModalTitle').innerText = 'Edit Wine Details';
    document.getElementById('pAction').value = 'edit';
    document.getElementById('pId').value = p.id;
    document.getElementById('pName').value = p.name;
    document.getElementById('pCategory').value = p.category;
    document.getElementById('pSize').value = p.bottle_size;
    document.getElementById('pPrice').value = p.price;
    document.getElementById('pStock').value = p.stock;
    document.getElementById('pAbv').value = p.alcohol_content;
    document.getElementById('pProductImage').value = '';
    document.getElementById('pCurrentImagePreview').src = '../' + (p.image || 'images/RUBY RICH.png');
    document.getElementById('pTagline').value = p.tagline || '';
    document.getElementById('pSubmitBtn').innerText = 'Update Wine';
    document.getElementById('productModal').style.display = 'flex';
}

function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
