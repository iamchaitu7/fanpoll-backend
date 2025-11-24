<?php
// Add this at the VERY TOP of your PHP files, before any output

// Allow all localhost origins and your production domains
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Define allowed production domains
$allowed_production_domains = [
    'https://fanpoll-flutter-6z77.vercel.app',
    'https://fanpoll-flutter.vercel.app', 
    'https://fanpoll-flutter-6z77-git-main-chaithus-projects-ac727576.vercel.app',
    'https://www.fanpollworld.com',
    'https://fanpollworld.com'
];

// Check if it's a localhost origin or allowed production domain
$is_allowed_origin = false;

// Allow any localhost port (development)
if (preg_match('/http:\/\/localhost:\d+/', $origin)) {
    $is_allowed_origin = true;
}
// Allow 127.0.0.1 with any port (development)
elseif (preg_match('/http:\/\/127\.0\.0\.1:\d+/', $origin)) {
    $is_allowed_origin = true;
}
// Allow specific production domains
elseif (in_array($origin, $allowed_production_domains)) {
    $is_allowed_origin = true;
}

// Set CORS headers if origin is allowed
if ($is_allowed_origin) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    // Optional: Set a default origin or leave it empty
    // header("Access-Control-Allow-Origin: https://fanpoll-flutter-6z77.vercel.app");
}

// Always set these headers
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-API-Key, Accept");
header("Access-Control-Expose-Headers: Authorization, X-API-Key");
header("Access-Control-Max-Age: 86400"); // 24 hours
header("Access-Control-Allow-Origin: *");

$path = __DIR__ . "/uploads/poll_images/" . basename($_GET["file"]);
if (file_exists($path)) {
    header("Content-Type: image/jpeg");
    readfile($path);
}


// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('HTTP/1.1 200 OK');
    exit();
}

// Your existing PHP code continues here...
?>



