<?php
/**
 * Sam's Fruit Wines - Free Wine / Free Gift Application API
 * Requires user authentication, validates Aadhaar document, saves to MySQL,
 * and sends notification email with attached Aadhaar to Admin.
 */

header('Content-Type: application/json; charset=UTF-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// Check user login
if (!is_user_logged_in()) {
    http_response_code(401);
    echo json_encode([
        'status'   => 'unauthorized',
        'message'  => 'Please login or register first to claim your Free Wine bottle.',
        'loggedIn' => false
    ]);
    exit;
}

$pdo = getDbConnection();
$userId = (int)$_SESSION['user_id'];

// Check if user already applied
$checkStmt = $pdo->prepare("SELECT id, verification_status, redemption_status FROM free_wine_applications WHERE user_id = ?");
$checkStmt->execute([$userId]);
$existing = $checkStmt->fetch();

if ($existing) {
    http_response_code(409);
    echo json_encode([
        'status'  => 'error',
        'message' => 'You have already submitted a Free Wine application (Application #' . $existing['id'] . '). Status: ' . $existing['verification_status'] . '.'
    ]);
    exit;
}

// Read inputs
$name   = trim($_POST['name'] ?? $_SESSION['user_name'] ?? '');
$email  = trim(filter_var($_POST['email'] ?? $_SESSION['user_email'] ?? '', FILTER_SANITIZE_EMAIL));
$mobile = trim($_POST['mobile'] ?? '');
$dob    = trim($_POST['dob'] ?? '');

if (empty($name) || empty($email) || empty($mobile) || empty($dob)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields (Name, Email, Mobile, DOB).']);
    exit;
}

// Validate file upload
if (!isset($_FILES['aadhaar_file']) || $_FILES['aadhaar_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Please upload a clear copy of your Aadhaar Card.']);
    exit;
}

$file = $_FILES['aadhaar_file'];
$maxSize = 5 * 1024 * 1024; // 5MB

if ($file['size'] > $maxSize) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Aadhaar document size must not exceed 5MB.']);
    exit;
}

$allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
$fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($fileExt, $allowedExtensions)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Allowed formats: JPG, JPEG, PNG, PDF.']);
    exit;
}

// Verify actual MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

$allowedMimes = ['image/jpeg', 'image/png', 'application/pdf', 'image/pjpeg'];
if (!in_array($mimeType, $allowedMimes)) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Uploaded file is not a valid JPG, PNG, or PDF document.']);
    exit;
}

// Ensure private upload directory exists
$uploadDir = __DIR__ . '/../uploads/private';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
    file_put_contents($uploadDir . '/.htaccess', "Deny from all\n");
}

// Generate secure random filename
$safeFileName = 'aadhaar_u' . $userId . '_' . bin2hex(random_bytes(10)) . '.' . $fileExt;
$destination = $uploadDir . '/' . $safeFileName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to securely store Aadhaar document. Please try again.']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO free_wine_applications (
            user_id, name, mobile, email, dob, aadhaar_file, verification_status, redemption_status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, 'PENDING', 'NOT_REDEEMED'
        )
    ");
    $stmt->execute([$userId, $name, $mobile, $email, $dob, $safeFileName]);
    $applicationId = (int)$pdo->lastInsertId();

    $pdo->commit();

    // Prepare email to Admin with Aadhaar attachment
    $applicationDate = date('Y-m-d H:i:s');
    $emailSubject = "New Free Wine Gift Application #{$applicationId} - {$name}";
    $emailBody = "
    <div style='background:#0d0d0d;color:#fff;padding:24px;font-family:Arial,sans-serif;max-width:640px;margin:auto;border:1px solid #d4af37;border-radius:10px;'>
        <div style='text-align:center;padding-bottom:16px;border-bottom:1px solid #333;'>
            <h2 style='color:#d4af37;margin:0;'>SAM'S FRUIT WINES</h2>
            <p style='color:#bbb;margin:4px 0 0;'>Free Wine Bottle Application Received</p>
        </div>
        <div style='padding:20px 0;'>
            <p style='color:#eee;'>A customer has submitted a claim for the complimentary Free Wine Bottle voucher. Please verify their Date of Birth against the attached Aadhaar card.</p>
            <table style='width:100%;border-collapse:collapse;color:#ddd;font-size:14px;margin-top:15px;'>
                <tr>
                    <td style='padding:8px;border-bottom:1px solid #222;color:#d4af37;'><strong>Application ID:</strong></td>
                    <td style='padding:8px;border-bottom:1px solid #222;'>#{$applicationId}</td>
                </tr>
                <tr>
                    <td style='padding:8px;border-bottom:1px solid #222;color:#d4af37;'><strong>Applicant Name:</strong></td>
                    <td style='padding:8px;border-bottom:1px solid #222;'>{$name}</td>
                </tr>
                <tr>
                    <td style='padding:8px;border-bottom:1px solid #222;color:#d4af37;'><strong>Mobile Number:</strong></td>
                    <td style='padding:8px;border-bottom:1px solid #222;'>{$mobile}</td>
                </tr>
                <tr>
                    <td style='padding:8px;border-bottom:1px solid #222;color:#d4af37;'><strong>Email Address:</strong></td>
                    <td style='padding:8px;border-bottom:1px solid #222;'>{$email}</td>
                </tr>
                <tr>
                    <td style='padding:8px;border-bottom:1px solid #222;color:#d4af37;'><strong>Declared DOB:</strong></td>
                    <td style='padding:8px;border-bottom:1px solid #222;'>{$dob}</td>
                </tr>
                <tr>
                    <td style='padding:8px;border-bottom:1px solid #222;color:#d4af37;'><strong>Submission Date:</strong></td>
                    <td style='padding:8px;border-bottom:1px solid #222;'>{$applicationDate}</td>
                </tr>
            </table>
            <div style='margin-top:20px;padding:12px;background:#181818;border-radius:6px;border-left:3px solid #d4af37;'>
                <p style='margin:0;font-size:13px;color:#ccc;'>The applicant's Aadhaar document is attached to this email. You can also view and verify it in the Admin Panel.</p>
            </div>
        </div>
        <p style='text-align:center;color:#777;font-size:12px;margin-top:20px;'>Sam's Fruit Wines Admin Notification System</p>
    </div>";

    $attachments = [
        [
            'path' => $destination,
            'name' => "Aadhaar_App_{$applicationId}." . $fileExt
        ]
    ];

    @send_smtp_email(MAIL_ADMIN_EMAILS, $emailSubject, $emailBody, $attachments, $email);

    echo json_encode([
        'status'        => 'success',
        'message'       => 'Your Free Wine claim and Aadhaar document have been submitted! Our estate cellar team will verify your Date of Birth and issue your active redemption voucher token.',
        'applicationId' => $applicationId
    ]);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Clean up file if db fails
    if (file_exists($destination)) {
        @unlink($destination);
    }
    error_log("Free Wine Application Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Failed to submit application: ' . $e->getMessage()
    ]);
    exit;
}
