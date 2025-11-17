<?php
// CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");

// Get the image filename from URL
$filename = basename($_GET["file"]);  

// Build actual file path
$path = __DIR__ . "/uploads/poll_images/" . $filename;

// Check file exists
if (!file_exists($path)) {
    header("HTTP/1.1 404 Not Found");
    exit("Image not found");
}

// Set correct content type
header("Content-Type: image/jpeg");

// Output image
readfile($path);
exit;
