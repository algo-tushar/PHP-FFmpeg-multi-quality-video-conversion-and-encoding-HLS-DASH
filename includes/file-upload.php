<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check whether its ajax action or not
 */
$is_ajax = ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' );
if ( !$is_ajax ) {
    header("HTTP/1.0 403 Forbidden", TRUE, 403);
    die("File is protected to direct access");
}

header('Content-Type: application/json'); // Set file return type as json

/**
 * Include Functions
 */
require_once '../config.php';

// Define allowed file extensions
$allowedExtensions = ['mp4', 'avi', 'mkv', 'mov', 'flv', 'webm', 'mpeg', 'mpg', 'wmv', '3gp'];

$uploadDir = rtrim(UPLOADPATH, '/'); // Target upload directory

if (isset($_FILES['file'])) {
    // Use a unique session ID to prevent interference between tabs
    $sessionId = session_id(); // Retrieve the session ID
    session_write_close(); // Close the session for other requests

    // Check if another conversion is already in progress for this session
    $lockfile = $uploadDir . '/upload_' . $sessionId . '.lock';
    if (file_exists($lockfile)) {
        echo purge_json_encode([
            'error' => 'Another upload is already in progress'
        ]);

        @unlink($lockfile); // Remove lock file
        exit();
    }

    // Create a lock file to prevent concurrent conversions for this session
    file_put_contents($lockfile, 'locked');

    // Check for file upload errors
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = 'File upload error: ' . $_FILES['file']['error'];
        echo purge_json_encode(['success' => false, 'message' => $errorMessage]);

        @unlink($lockfile); // Remove lock file
        exit();
    }

    // Get file extension
    $fileExtension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    // Check if file extension is allowed
    if (!in_array($fileExtension, $allowedExtensions)) {
        $errorMessage = 'File extension is not allowed';
        echo purge_json_encode(['success' => false, 'message' => $errorMessage]);

        @unlink($lockfile); // Remove lock file
        exit();
    }

    $fileId = time() . '_' . rand(100, 999); // Generate random file ID
    // Check whether same file ID exists or not. If exists, generate new ID
    while( file_exists($uploadDir . '/' . $fileId . '.' . $fileExtension) ) {
        $fileId = time() . '_' . rand(100, 999);
    }

    $fileName = $fileId . '.' . $fileExtension; // File name
    $targetFilePath = $uploadDir . '/' . $fileName;  // Target file path

    // Move uploaded file to target directory
    if ( move_uploaded_file($_FILES['file']['tmp_name'], $targetFilePath) ) {
        echo purge_json_encode(['success' => true, 'filename' => $fileName]);
    } else {
        $errorMessage = 'Failed to move file: ' . $fileName;
        echo purge_json_encode(['success' => false, 'message' => $errorMessage]);
    }
} else {
    echo purge_json_encode(['success' => false, 'message' => 'No file uploaded']);
}

@unlink($lockfile); // Remove lock file