<?php
/**
 * Sam's Fruit Wines - My Free Gift / Free Wine Portal
 * Displays customer's Free Gift application, Aadhaar verification state, and redemption token.
 */

require_once __DIR__ . '/../includes/functions.php';
require_user_login('../login.php');

$pdo = getDbConnection();
$userId = (int)$_SESSION['user_id'];
$user = get_logged_in_user($pdo);

// Fetch application, token, and redemption details
$stmt = $pdo->prepare("
    SELECT f.*, 
           t.token, t.status AS token_status, t.expires_at,
           r.redeemed_at, r.gift_item_name
    FROM free_wine_applications f
    LEFT JOIN gift_tokens t ON t.application_id = f.id
    LEFT JOIN gift_redemptions r ON r.token_id = t.id
    WHERE f.user_id = ?
    LIMIT 1
");
$stmt->execute([$userId]);
$pageTitle = "My Free Gift";
$activeNav = "free-gift";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Free Gift | Sam's Fruit Wines</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="user-style.css">
    <style>
        .gift-status-banner {
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid var(--border-subtle);
        }
        .gift-verified {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.12) 0%, rgba(198, 161, 91, 0.08) 100%);
            border-color: #22c55e;
        }
        .gift-pending {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.12) 0%, rgba(198, 161, 91, 0.06) 100%);
            border-color: #f59e0b;
        }
        .gift-rejected {
            background: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
        }
        .token-display-box {
            background: #0d0d0d;
            border: 2px dashed var(--gold);
            border-radius: 12px;
            padding: 24px;
            text-align: center;
            margin: 20px 0;
            overflow: hidden;
        }
        .token-code {
            font-size: 2.3rem;
            letter-spacing: 5px;
            font-family: monospace;
            font-weight: 700;
            color: var(--gold-light);
            text-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
            word-break: break-all;
            max-width: 100%;
        }
        @media (max-width: 480px) {
            .token-code {
                font-size: 1.55rem;
                letter-spacing: 2px;
            }
            .gift-status-banner {
                padding: 18px 14px;
            }
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
                    <h2><i class="fa-solid fa-wine-bottle"></i> My Complimentary Wine Bottle</h2>
                </div>

                <?php if (!$app): ?>
                    <!-- NOT APPLIED YET -->
                    <div class="empty-state">
                        <i class="fa-solid fa-gift" style="color:var(--gold-light);"></i>
                        <h3>You Haven't Claimed Your Free Wine Bottle Yet</h3>
                        <p>Members of Sam's Wine Club receive an exclusive welcome voucher for a complimentary 750ml bottle of Scarlet Silk or Vitality Jamun Wine. Upload your Aadhaar card to claim.</p>
                        <a href="../signup.html" class="btn-gold"><i class="fa-solid fa-wand-magic-sparkles"></i> Claim Free Wine Bottle Now →</a>
                    </div>
                <?php else: ?>
                    <!-- APPLICATION STATUS -->
                    <?php 
                        $statusClass = 'gift-pending';
                        if ($app['verification_status'] === 'VERIFIED') $statusClass = 'gift-verified';
                        elseif ($app['verification_status'] === 'REJECTED') $statusClass = 'gift-rejected';
                    ?>
                    <div class="gift-status-banner <?= $statusClass ?>">
                        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                            <div>
                                <span class="badge badge-<?= strtolower($app['verification_status']) ?>">Verification: <?= e($app['verification_status']) ?></span>
                                <h3 style="font-size:1.4rem; margin-top:8px;">Application #<?= e($app['id']) ?></h3>
                                <p style="font-size:13px; color:var(--text-secondary); margin-top:4px;">
                                    Submitted on <?= date('d M Y, h:i A', strtotime($app['created_at'])) ?>
                                </p>
                            </div>
                            <div>
                                <span class="badge badge-<?= strtolower($app['redemption_status']) ?>">Redemption: <?= str_replace('_', ' ', e($app['redemption_status'])) ?></span>
                            </div>
                        </div>

                        <?php if ($app['verification_status'] === 'PENDING'): ?>
                            <div style="margin-top:16px; padding:12px 14px; background:rgba(0,0,0,0.4); border-radius:8px; font-size:13.5px;">
                                <i class="fa-solid fa-hourglass-half" style="color:#fbbf24; margin-right:6px;"></i>
                                <strong>Under Manual Review:</strong> Our winery administrator is reviewing your uploaded Aadhaar document against your submitted Date of Birth. Once verified, your unique redemption token will be activated below.
                            </div>
                        <?php elseif ($app['verification_status'] === 'REJECTED'): ?>
                            <div style="margin-top:16px; padding:12px 14px; background:rgba(0,0,0,0.4); border-radius:8px; font-size:13.5px; color:#fca5a5;">
                                <i class="fa-solid fa-circle-xmark" style="color:#ef4444; margin-right:6px;"></i>
                                <strong>Application Rejected:</strong> The details on your uploaded Aadhaar document did not match your submitted date of birth or legal requirements.
                                <?php if (!empty($app['admin_notes'])): ?>
                                    <p style="margin-top:6px; color:#fff;">Admin Remarks: <?= e($app['admin_notes']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($app['verification_status'] === 'VERIFIED'): ?>
                            <div style="margin-top:16px; padding:12px 14px; background:rgba(0,0,0,0.4); border-radius:8px; font-size:13.5px;">
                                <i class="fa-solid fa-circle-check" style="color:#4ade80; margin-right:6px;"></i>
                                <strong>Aadhaar Verified Successfully:</strong> Your identity and Date of Birth have been authenticated by Sam's Cellar Admin.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- ACTIVE TOKEN CARD -->
                    <?php if ($app['verification_status'] === 'VERIFIED' && !empty($app['token'])): ?>
                        <div class="token-display-box">
                            <span style="font-size:11px; color:var(--text-muted); letter-spacing:2px; text-transform:uppercase; display:block; margin-bottom:8px;">
                                YOUR UNIQUE COMPLIMENTARY BOTTLE TOKEN
                            </span>
                            <div class="token-code"><?= e($app['token']) ?></div>
                            <div style="margin-top:12px;">
                                <span class="badge badge-<?= strtolower($app['token_status']) ?>">Token Status: <?= e($app['token_status']) ?></span>
                            </div>
                            <p style="font-size:13px; color:var(--text-secondary); max-width:540px; margin:14px auto 0; line-height:1.5;">
                                Present this token at any authorized Sam's Estate Tasting Room or retail boutique to claim your complimentary 750ml reserve bottle.
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- REDEMPTION DETAILS (IF REDEEMED) -->
                    <?php if ($app['redemption_status'] === 'REDEEMED' && !empty($app['redeemed_at'])): ?>
                        <div style="background:#141414; border:1px solid var(--gold-border); border-radius:12px; padding:20px; margin-bottom:24px;">
                            <h3 style="color:var(--gold-light); font-size:1.15rem; margin-bottom:8px;">
                                <i class="fa-solid fa-champagne-glasses"></i> Bottle Redeemed
                            </h3>
                            <p style="font-size:14px; color:#eee;">Item: <strong><?= e($app['gift_item_name'] ?: "Sam's Reserve Fruit Wine (750 ML)") ?></strong></p>
                            <p style="font-size:13px; color:var(--text-secondary); margin-top:4px;">
                                Date & Time: <?= date('d M Y, h:i A', strtotime($app['redeemed_at'])) ?>
                            </p>
                            <small style="color:var(--text-muted); display:block; margin-top:10px;">Rule: One person = One free gift voucher. This token has been redeemed.</small>
                        </div>
                    <?php endif; ?>

                    <!-- HOW TO CLAIM -->
                    <div style="margin-top:24px; padding:22px; background:#141414; border:1px solid var(--border-subtle); border-radius:12px;">
                        <h3 style="font-size:1.2rem; font-family:var(--font-serif); color:#fff; margin-bottom:8px;">
                            <i class="fa-solid fa-store" style="color:var(--gold);"></i> How to Claim Your Free Bottle
                        </h3>
                        <p style="font-size:13.5px; color:var(--text-secondary); line-height:1.6;">
                            You can visit any participating Sam's boutique or authorized winery tasting lounge to claim your complimentary bottle. Simply show your active redemption token above to our team at the counter upon visiting. Valid identity verification applies at collection.
                        </p>
                    </div>

                <?php endif; ?>
            </div>
        </main>
    </div>

</body>
</html>
