<?php
/**
 * Sam's Fruit Wines - Admin Orders Management
 * Full order listing, item breakdown, editable status & delivery address inspection
 */
define('ADMIN_PANEL', true);
require_once __DIR__ . '/auth.php';

$pageTitle = 'Orders Management';
$activeNav = 'orders';

$message = '';
$error = '';

// Handle Status Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newOrderStatus = $_POST['order_status'] ?? '';
    $newPaymentStatus = $_POST['payment_status'] ?? '';

    if ($orderId > 0 && !empty($newOrderStatus) && !empty($newPaymentStatus)) {
        try {
            $stmtUpdate = $pdo->prepare("
                UPDATE orders 
                SET order_status = ?, payment_status = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmtUpdate->execute([$newOrderStatus, $newPaymentStatus, $orderId]);
            $message = "Order #{$orderId} status successfully updated to {$newOrderStatus} ({$newPaymentStatus}).";
        } catch (Exception $e) {
            $error = "Error updating order: " . $e->getMessage();
        }
    }
}

// Filters & Search
$statusFilter = $_GET['status'] ?? 'ALL';
$searchQuery = trim($_GET['q'] ?? '');

$sql = "
    SELECT o.*, COALESCE(u.full_name, o.customer_name) as display_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE 1=1
";
$params = [];

if ($statusFilter !== 'ALL') {
    $sql .= " AND o.order_status = ?";
    $params[] = $statusFilter;
}

if (!empty($searchQuery)) {
    $sql .= " AND (o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ? OR o.customer_mobile LIKE ?)";
    $like = "%{$searchQuery}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY o.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all order items indexed by order_id
$orderIds = array_column($orders, 'id');
$itemsByOrder = [];
if (!empty($orderIds)) {
    $inClause = implode(',', array_fill(0, count($orderIds), '?'));
    $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id IN ($inClause)");
    $stmtItems->execute($orderIds);
    $allItems = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
    foreach ($allItems as $item) {
        $itemsByOrder[$item['order_id']][] = $item;
    }
}

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

<!-- FILTER & SEARCH BAR -->
<div class="admin-card" style="padding: 18px 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="orders.php" class="btn-outline <?php echo ($statusFilter === 'ALL' && empty($searchQuery)) ? 'active' : ''; ?>">All Orders</a>
            <a href="orders.php?status=CONFIRMED" class="btn-outline <?php echo ($statusFilter === 'CONFIRMED') ? 'active' : ''; ?>">Confirmed</a>
            <a href="orders.php?status=PROCESSING" class="btn-outline <?php echo ($statusFilter === 'PROCESSING') ? 'active' : ''; ?>">Processing</a>
            <a href="orders.php?status=SHIPPED" class="btn-outline <?php echo ($statusFilter === 'SHIPPED') ? 'active' : ''; ?>">Shipped</a>
            <a href="orders.php?status=DELIVERED" class="btn-outline <?php echo ($statusFilter === 'DELIVERED') ? 'active' : ''; ?>">Delivered</a>
            <a href="orders.php?status=CANCELLED" class="btn-outline <?php echo ($statusFilter === 'CANCELLED') ? 'active' : ''; ?>">Cancelled</a>
        </div>

        <form method="GET" action="orders.php" style="display: flex; gap: 10px; align-items: center;">
            <?php if ($statusFilter !== 'ALL'): ?>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
            <?php endif; ?>
            <input type="text" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Order #, name, phone..." 
                   class="admin-form-input" style="padding: 8px 14px; font-size: 13px; width: 220px;">
            <button type="submit" class="btn-gold" style="padding: 8px 14px;"><i class="fa-solid fa-magnifying-glass"></i></button>
            <?php if (!empty($searchQuery)): ?>
                <a href="orders.php" class="btn-outline" style="padding: 8px 12px;"><i class="fa-solid fa-xmark"></i></a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- ORDERS TABLE -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3><i class="fa-solid fa-box-archive"></i> Customer Orders (<?php echo count($orders); ?>)</h3>
    </div>

    <?php if (empty($orders)): ?>
        <p style="color: var(--text-muted); text-align: center; padding: 40px 0;">No customer orders found matching criteria.</p>
    <?php else: ?>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Items & Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $ord): 
                        $items = $itemsByOrder[$ord['id']] ?? [];
                        $itemCount = array_sum(array_column($items, 'quantity'));
                        $oStat = strtoupper($ord['order_status']);
                        $pStat = strtoupper($ord['payment_status']);
                    ?>
                        <tr>
                            <td>
                                <strong style="color: var(--gold-light); font-size: 14px;"><?php echo htmlspecialchars($ord['order_number']); ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);">ID: #<?php echo $ord['id']; ?></div>
                            </td>
                            <td>
                                <strong style="color: #fff;"><?php echo htmlspecialchars($ord['display_name']); ?></strong>
                                <div style="font-size: 11px; color: var(--text-muted);">User ID: #<?php echo $ord['user_id']; ?></div>
                            </td>
                            <td>
                                <div><i class="fa-solid fa-phone" style="font-size:10px; color:var(--gold);"></i> <?php echo htmlspecialchars($ord['customer_mobile']); ?></div>
                                <div style="font-size:12px; color:var(--text-muted);"><?php echo htmlspecialchars($ord['customer_email']); ?></div>
                            </td>
                            <td>
                                <div><strong style="color: #fff; font-size: 14px;">₹<?php echo number_format((float)$ord['total_amount'], 2); ?></strong></div>
                                <div style="font-size: 11.5px; color: var(--gold-light);"><?php echo $itemCount; ?> bottle(s) ordered</div>
                            </td>
                            <td>
                                <?php 
                                    $pBadge = ($pStat === 'PAID') ? 'badge-paid' : (($pStat === 'FAILED') ? 'badge-failed' : 'badge-pending');
                                ?>
                                <span class="badge <?php echo $pBadge; ?>"><?php echo $pStat; ?></span>
                                <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 2px;">
                                    <?php echo htmlspecialchars($ord['payment_method'] ?? 'COD'); ?>
                                </div>
                            </td>
                            <td>
                                <?php 
                                    $oBadge = 'badge-pending';
                                    if ($oStat === 'CONFIRMED') $oBadge = 'badge-confirmed';
                                    elseif ($oStat === 'PROCESSING') $oBadge = 'badge-processing';
                                    elseif ($oStat === 'SHIPPED') $oBadge = 'badge-shipped';
                                    elseif ($oStat === 'DELIVERED') $oBadge = 'badge-delivered';
                                    elseif ($oStat === 'CANCELLED') $oBadge = 'badge-cancelled';
                                ?>
                                <span class="badge <?php echo $oBadge; ?>"><?php echo $oStat; ?></span>
                            </td>
                            <td>
                                <span style="font-size: 12px;"><?php echo date('d M Y, H:i', strtotime($ord['created_at'])); ?></span>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <button type="button" class="btn-outline" style="padding: 5px 12px; font-size: 11.5px;" 
                                        onclick="openOrderModal(<?php echo htmlspecialchars(json_encode($ord), ENT_QUOTES, 'UTF-8'); ?>, <?php echo htmlspecialchars(json_encode($items), ENT_QUOTES, 'UTF-8'); ?>)">
                                    <i class="fa-solid fa-eye"></i> Details
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ORDER DETAILS & STATUS UPDATE MODAL -->
<div id="orderModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.85); backdrop-filter:blur(6px); z-index:2000; align-items:center; justify-content:center;">
    <div style="background: var(--admin-card); border: 1px solid var(--gold-border); border-radius: 14px; padding: 32px; max-width: 650px; width: 92%; max-height: 90vh; overflow-y: auto; box-shadow: 0 15px 40px rgba(0,0,0,0.85);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid var(--border-subtle); padding-bottom: 14px;">
            <h3 style="font-family: var(--font-serif); font-size: 1.55rem; color: #fff;">
                Order <span id="mOrderNum" style="color:var(--gold-light);"></span>
            </h3>
            <button type="button" onclick="closeOrderModal()" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <!-- Customer & Delivery Address Card -->
        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--border-subtle); border-radius: 8px; padding: 18px; margin-bottom: 20px;">
            <h4 style="font-size: 12px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--gold); margin-bottom: 8px;">Delivery Details</h4>
            <div style="font-size: 14px; font-weight: 600; color: #fff;" id="mCustName"></div>
            <div style="font-size: 13px; color: var(--text-secondary); margin-top: 4px;" id="mContact"></div>
            <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed rgba(255,255,255,0.1); font-size: 13px; color: var(--text-secondary);">
                <i class="fa-solid fa-location-dot" style="color: var(--gold-light); margin-right: 6px;"></i>
                <span id="mDeliveryAddr"></span>
            </div>
        </div>

        <!-- Order Items List -->
        <div style="margin-bottom: 20px;">
            <h4 style="font-size: 12px; letter-spacing: 1.5px; text-transform: uppercase; color: var(--gold); margin-bottom: 10px;">Ordered Wines</h4>
            <div id="mItemsContainer" style="display:flex; flex-direction:column; gap:8px;"></div>
            <div style="display:flex; justify-content:space-between; margin-top:14px; padding-top:12px; border-top:1px solid var(--border-subtle); font-size:15px; font-weight:700; color:#fff;">
                <span>Total Amount:</span>
                <span id="mTotalAmount" style="color:var(--gold-light);"></span>
            </div>
        </div>

        <!-- Update Status Form -->
        <form method="POST" action="orders.php" style="border-top: 1px solid var(--border-subtle); padding-top: 20px;">
            <input type="hidden" name="order_id" id="mOrderId" value="">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display:block; font-size:12px; color:var(--text-secondary); margin-bottom:6px; font-weight:500;">Order Status</label>
                    <select name="order_status" id="mSelectOrderStatus" class="admin-form-input" style="width:100%; padding:9px 12px; background:#000; border:1px solid var(--gold-border); color:#fff; border-radius:6px;">
                        <option value="CONFIRMED">CONFIRMED</option>
                        <option value="PROCESSING">PROCESSING</option>
                        <option value="SHIPPED">SHIPPED</option>
                        <option value="DELIVERED">DELIVERED</option>
                        <option value="CANCELLED">CANCELLED</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-size:12px; color:var(--text-secondary); margin-bottom:6px; font-weight:500;">Payment Status</label>
                    <select name="payment_status" id="mSelectPaymentStatus" class="admin-form-input" style="width:100%; padding:9px 12px; background:#000; border:1px solid var(--gold-border); color:#fff; border-radius:6px;">
                        <option value="PENDING">PENDING</option>
                        <option value="PAID">PAID</option>
                        <option value="FAILED">FAILED</option>
                    </select>
                </div>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button type="button" onclick="closeOrderModal()" class="btn-outline" style="padding: 9px 18px;">Close</button>
                <button type="submit" class="btn-gold" style="padding: 9px 22px;">Update Order</button>
            </div>
        </form>
    </div>
</div>

<script>
function openOrderModal(order, items) {
    document.getElementById('mOrderId').value = order.id;
    document.getElementById('mOrderNum').innerText = '#' + order.order_number;
    document.getElementById('mCustName').innerText = order.display_name || order.customer_name;
    document.getElementById('mContact').innerText = (order.customer_mobile || '') + ' | ' + (order.customer_email || '');
    document.getElementById('mDeliveryAddr').innerText = order.delivery_address + ', ' + order.city + ', ' + order.state + ' - ' + order.pincode;
    document.getElementById('mTotalAmount').innerText = '₹' + parseFloat(order.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2});

    document.getElementById('mSelectOrderStatus').value = order.order_status;
    document.getElementById('mSelectPaymentStatus').value = order.payment_status;

    const container = document.getElementById('mItemsContainer');
    container.innerHTML = '';

    if (items && items.length > 0) {
        items.forEach(it => {
            const row = document.createElement('div');
            row.style.display = 'flex';
            row.style.justifyContent = 'space-between';
            row.style.alignItems = 'center';
            row.style.padding = '8px 12px';
            row.style.background = 'rgba(255,255,255,0.02)';
            row.style.borderRadius = '6px';
            row.style.border = '1px solid var(--border-subtle)';

            row.innerHTML = `
                <div>
                    <strong style="color:#fff; font-size:13.5px;">${it.product_name}</strong>
                    <div style="font-size:11.5px; color:var(--text-muted);">${it.bottle_size || '750 ML'} &times; ${it.quantity} bottle(s)</div>
                </div>
                <div style="font-weight:600; color:var(--gold-light); font-size:13.5px;">
                    ₹${parseFloat(it.subtotal).toLocaleString('en-IN', {minimumFractionDigits: 2})}
                </div>
            `;
            container.appendChild(row);
        });
    } else {
        container.innerHTML = '<p style="color:var(--text-muted); font-size:12px;">No individual item details logged.</p>';
    }

    document.getElementById('orderModal').style.display = 'flex';
}

function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
