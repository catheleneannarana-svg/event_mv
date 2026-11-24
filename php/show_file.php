<?php
// Get the filename from the URL
$file = $_GET['file'] ?? '';

// Security: prevent directory traversal
$filename = basename($file); // remove any folder paths
$uploadsDir = realpath('../uploads'); // adjust path to your actual uploads folder
$path = $uploadsDir . DIRECTORY_SEPARATOR . $filename;

// Check if file exists
if(file_exists($path)){
    $info = getimagesize($path);
    header("Content-Type: " . $info['mime']);
    readfile($path);
    exit;
} else {
    http_response_code(404);
    echo "File not found.";
}
