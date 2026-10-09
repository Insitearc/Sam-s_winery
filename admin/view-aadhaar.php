<?php
/**
 * Sam's Fruit Wines - Secure Aadhaar Document Viewer for Admin
 * Streams uploaded Aadhaar card directly from private storage to authenticated admins only.
 */

require_once __DIR__ . '/auth.php'; // Enforces admin authentication

$appId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($appId <= 0) {
    http_response_code(400);
    die("Invalid application ID specified.");
}

$stmt = $pdo->prepare("SELECT id, name, aadhaar_file FROM free_wine_applications WHERE id = ? LIMIT 1");
$stmt->execute([$appId]);
$application = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$application || empty($application['aadhaar_file'])) {
    http_response_code(404);
    die("Application or Aadhaar document record not found.");
}

$fileName = basename($application['aadhaar_file']);
$privateDir = realpath(__DIR__ . '/../uploads/private');
$filePath = $privateDir . DIRECTORY_SEPARATOR . $fileName;

if (!$privateDir || !file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    die("The Aadhaar file does not exist on the server.");
}

// Security check: ensure path is strictly within private directory
if (strpos(realpath($filePath), $privateDir) !== 0) {
    http_response_code(403);
    die("Access denied: Invalid file path.");
}

// Determine MIME type
$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
$mimeMap = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png'
];

$contentType = $mimeMap[$ext] ?? 'application/octet-stream';

// Clear output buffers
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: inline; filename="' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName) . '"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

readfile($filePath);
exit;
