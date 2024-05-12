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

$uploadDir = rtrim(UPLOADPATH, '/'); // Target upload directory

if (isset($_POST['foldername'])) {
    // Use a unique session ID to prevent interference between tabs
    $sessionId = session_id(); // Retrieve the session ID
    session_write_close(); // Close the session for other requests

    $foldername = $_POST['foldername'];
    $folderPath = $uploadDir . '/' . $foldername; 

    if (file_exists($folderPath) && is_dir($folderPath)) {
        // Check if the folder is not empty
        $files = scandir($folderPath);
        $fileCount = count($files) - 2; // Subtracting 2 for '.' and '..'
    
        if ( empty($fileCount) ) {
            echo purge_json_encode([
                'success' => false,
                'message' => 'The folder is empty.'
            ]);
            exit();
        }
    } else {
        echo purge_json_encode([
            'success' => false,
            'message' => 'The folder does not exist'
        ]);
        exit();
    }

    // Check if another conversion is already in progress for this session
    $lockfile = $uploadDir . '/zip_' . $sessionId . '.lock';
    if (file_exists($lockfile)) {
        echo purge_json_encode([
            'error' => 'Another zip conversation is already in progress'
        ]);
        exit();
    }

    // Create a lock file to prevent concurrent conversions for this session
    file_put_contents($lockfile, 'locked');

    // Name for the zip file
    $zipFilename = $uploadDir . '/' . $foldername . '.zip';

    // Initialize zip object
    $zip = new ZipArchive();
    if ($zip->open($zipFilename, ZipArchive::CREATE) !== TRUE) {
        echo purge_json_encode([
            'success' => false,
            'message' => 'Failed to create zip archive'
        ]);

        @unlink($lockfile); // Remove lock file
        exit();
    }

    // Add files from the folder to the zip archive
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folderPath),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ($files as $name => $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen($folderPath) + 1);

            // Add file to the zip archive
            $zip->addFile($filePath, $relativePath);
        }
    }

    // Close the zip archive
    $zip->close();
    @unlink($lockfile); // Remove lock file
    deleteFolder($folderPath); // Delete the folder after zipping

    echo purge_json_encode([
        'success' => true,
        'zipfilename' => $foldername . '.zip'
    ]);
} else {
    echo purge_json_encode([
        'success' => false,
        'message' => 'Folder name not provided'
    ]);
}