<?php
// Add this at the VERY TOP of your PHP files, before any output

// Allow all localhost origins and your production domain
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// Check if it's a localhost origin
if (preg_match('/http:\/\/localhost:\d+/', $origin) || 
    $origin === 'https://fanpoll-flutter-6z77.vercel.app') {
    header("Access-Control-Allow-Origin: $origin");
} else {
    // For production, specify your exact domain or use *
    header("Access-Control-Allow-Origin: https://fanpoll-flutter-6z77.vercel.app");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400"); // 24 hours

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Your existing PHP code continues here...
?>