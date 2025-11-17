<?php
header("Content-Type: application/json");

// Root uploads directory
$uploadsDir = __DIR__ . "/uploads";

// Function: list all files in a folder
function listFiles($path) {
    $result = [];
    if (!is_dir($path)) return $result;

    $files = scandir($path);

    foreach ($files as $file) {
        if ($file === "." || $file === "..") continue;

        $fullPath = $path . "/" . $file;

        $result[] = [
            "name" => $file,
            "path" => $fullPath,
            "size_bytes" => filesize($fullPath),
            "last_modified" => date("Y-m-d H:i:s", filemtime($fullPath))
        ];
    }

    return $result;
}

// Scan both folders
$response = [
    "poll_images" => listFiles($uploadsDir . "/poll_images"),
    "profile_pictures" => listFiles($uploadsDir . "/profile_pictures")
];

// Print JSON
echo json_encode($response, JSON_PRETTY_PRINT);
