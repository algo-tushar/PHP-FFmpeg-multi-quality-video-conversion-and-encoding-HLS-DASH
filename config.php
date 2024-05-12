<?php 

error_reporting(E_ALL); // Set error reporting to all
ini_set("display_errors", 1); // Set to 1 to display all errors

/**
 * Check if PHP version is above 7.1.0
 */
if ( strnatcmp(phpversion(), '7.1.0') < 0 ) {
    die("PHP version 7.1.0 or above is required to run this script");
}

/**
 * Prevents direct access to the file if it is being accessed via a GET request
 */
if ( isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] == "GET" && realpath(__FILE__) == realpath($_SERVER["SCRIPT_FILENAME"]) ) {
    header("HTTP/1.0 403 Forbidden", TRUE, 403);
    die("File is not available to public");
}

if ( !isset($_SESSION) ) { session_start(); } // Start session if not started

defined( 'HOMEURL' )    || define( 'HOMEURL', '' ); // i.e: https://example.com. If homeurl not set it will auto detect
defined( 'ROOTPATH' )   || define( 'ROOTPATH', __DIR__ ); // Root directory path
defined( 'UPLOADPATH' ) || define( 'UPLOADPATH', ROOTPATH . '/videos' ); // Upload file directory
defined( 'LOGPATH' )    || define( 'LOGPATH', ROOTPATH . '/logs' ); // Log file directory

/**
 * Check whether upload and log directories are exist
 * If exist check if directories are writeable,
 * If not exist create upload directory
 */
$directories = [UPLOADPATH, LOGPATH];
foreach ($directories as $directory) {
    if (!is_dir($directory)) {
        @mkdir($directory, 0755, true); // Create directory with writable permissions
    }
    if (!is_writable($directory)) {
        die("$directory is not writable");
    }
}

/**
 * Include PHP FFmpeg Video Streaming
 * @package https://github.com/quasarstream/PHP-FFmpeg-video-streaming
 */
require_once 'vendor/autoload.php';


/**
 * Check whether PHP-FFMPEG library has any error
 */
try {
    // If FFMPEG don't autodetect ffmpeg add ffmpeg, Add these binary paths explicitly
    $config = [
        'ffmpeg.binaries'  => '/opt/local/ext/bin/ffmpeg', // The path to the FFMpeg binary
        'ffprobe.binaries' => '/opt/local/ext/bin/ffprobe', // The path to the FFProbe binary
        'timeout'          => 3600, // The timeout for the underlying process
        'ffmpeg.threads'   => 12, // The number of threads that FFmpeg should use
        'temporary_directory' => UPLOADPATH
    ];
    
    $GLOBALS["ffmpeg"] = \Streaming\FFMpeg::create($config);
} catch (\Exception $e) {
    die($e->getMessage());
}

/**
 * Include Functions
 */
require_once 'includes/functions.php';

// Clear upload directory every 24 hours
clearDirectory(UPLOADPATH, (24 * 60 * 60));