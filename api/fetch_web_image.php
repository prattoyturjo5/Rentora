<?php
/**
 * api/fetch_web_image.php - Secure proxy endpoint for fetching web images dropped into upload zones
 * Validates authentication, restricts against SSRF, enforces 5MB limit and JPEG/PNG/WebP formats.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Authentication check
if (empty($_SESSION['member_id']) && empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized: Please log in first']);
    exit();
}

// 2. Extract and sanitize URL
$url = trim($_GET['url'] ?? $_POST['url'] ?? '');
if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid or missing image URL']);
    exit();
}

$parts = parse_url($url);
$scheme = strtolower($parts['scheme'] ?? '');
if (!in_array($scheme, ['http', 'https'], true)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Only HTTP and HTTPS URLs are permitted']);
    exit();
}

$host = $parts['host'] ?? '';
if (empty($host)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing hostname']);
    exit();
}

// 3. SSRF Protection: Prevent accessing loopback / internal private IP addresses
$ip = gethostbyname($host);
if ($ip === $host && !filter_var($ip, FILTER_VALIDATE_IP)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Could not resolve remote host address']);
    exit();
}

// Reject loopback, private ranges, and link-local
if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access to internal network addresses is prohibited']);
    exit();
}

// 4. Fetch image payload using cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 4,
    CURLOPT_TIMEOUT => 12,
    CURLOPT_CONNECTTIMEOUT => 6,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    CURLOPT_HTTPHEADER => [
        'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        'Accept-Language: en-US,en;q=0.9',
    ],
]);

$data = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($httpCode !== 200 || empty($data)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to retrieve image: ' . ($curlError ?: "HTTP $httpCode")]);
    exit();
}

// 5. Size check (max 5MB)
$maxSize = 5 * 1024 * 1024;
if (strlen($data) > $maxSize) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Image size exceeds maximum limit of 5MB']);
    exit();
}

// 6. Verify image MIME and binary integrity
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->buffer($data);

$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
if (!in_array($mime, $allowedMimes, true)) {
    $imgInfo = @getimagesizefromstring($data);
    if ($imgInfo && in_array($imgInfo[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
        $mime = image_type_to_mime_type($imgInfo[2]);
    } else {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid image format. Only JPG, PNG, and WebP are allowed.']);
        exit();
    }
}

// 7. Output image binary
header('Content-Type: ' . $mime);
header('Content-Length: ' . strlen($data));
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=86400');
echo $data;
exit();
