<?php
use \Streaming\Representation;

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


/**
 * Include Functions
 */
require_once '../config.php';
global $ffmpeg;

$uploadDir = rtrim(UPLOADPATH, '/'); // Target upload directory

if ( isset($_POST['filename'], $_POST['streaming']) && $_POST['filename'] != "" && in_array(strtolower($_POST['streaming']), ['hls', 'dash']) ){
    // Use a unique session ID to prevent interference between tabs
    $sessionId = session_id(); // Retrieve the session ID
    session_write_close(); // Close the session for other requests
    
    $filepath = $uploadDir . '/' . $_POST['filename']; //"/1715437079222.mp4";
    
    // Check if the file exists
    if ( ! file_exists($filepath) ) {
        echo purge_json_encode([
            'error' => 'File not found'
        ]);
        exit();
    }

    //pathinfo($filePath, PATHINFO_FILENAME);

    $filename = pathinfo($filepath, PATHINFO_FILENAME); // Extract file name
    $outputdir = rtrim(UPLOADPATH, '/') . '/' . $filename . '/';

    /**
     * Check whether output directory is exist
     * If exist check if directory is writeable,
     * If not exist create output directory
     */
    $error = true;
    if ( file_exists($outputdir) ) {
        if ( !is_writable($outputdir) ) {
            echo purge_json_encode([
                'error' => $outputdir . " is not writeable"
            ]);
            exit();
        }
        $error = false;
    } else {
        try {
            mkdir($outputdir, 0755, true);
            $error = false;
        } catch (\Exception $e) {
            echo purge_json_encode([
                'error' => $e->getMessage()
            ]);
            exit();
        }
    }

    // Move forward if no errors
    if ( !$error ) {
        $video = $ffmpeg->open($filepath);

        // Videos in multiple resulutions
        $r_144p = (new Representation)->setKiloBitrate(95)->setResize(256, 144);
        $r_240p = (new Representation)->setKiloBitrate(150)->setResize(426, 240);
        $r_360p = (new Representation)->setKiloBitrate(276)->setResize(640, 360);
        $r_480p = (new Representation)->setKiloBitrate(750)->setResize(854, 480);
        $r_720p = (new Representation)->setKiloBitrate(2048)->setResize(1280, 720);
        $r_1080p = (new Representation)->setKiloBitrate(4096)->setResize(1920, 1080);
        $r_2k = (new Representation)->setKiloBitrate(6144)->setResize(2560, 1440);
        $r_4k = (new Representation)->setKiloBitrate(17408)->setResize(3840, 2160);

        ob_implicit_flush(true); // Turn on implicit flushing 
        while (ob_get_level()) {ob_end_flush();}
        
        // Return cuurrent progress
        $format = new \Streaming\Format\X264();
        $progress = 0;
        $format->on('progress', function ($video, $format, $percentage) use (&$progress) {
            if ( $percentage > $progress ) {
                $progress = $percentage;
            }
            echo purge_json_encode([
                'progress' => $progress
            ]);
        });


        /**
         * Convert video in different streaming
         */
        if ( strtolower($_POST['streaming']) == 'dash' ) {
            /**
             * Dynamic Adaptive Streaming over HTTP (DASH)
             */
            //$dash = $video->dash()->setFormat($format)->autoGenerateRepresentations([$r_480p, $r_720p, $r_1080p]);
            //$dash = $video->dash()->setFormat($format)->autoGenerateRepresentations([2160, 1440, 1080, 720, 480]);
            $dash = $video->dash()->setFormat($format)->autoGenerateRepresentations();
            $dash->save($outputdir . '/' . $filename . '.mpd');

            @unlink($filepath); // Delete video file from server after conversion
        } else {
            /**
             * HTTP Live Streaming (also known as HLS)
             */
            $hls = $video->hls()->setFormat($format)->autoGenerateRepresentations();
            $hls->save($outputdir . '/' . $filename . '.m3u8');

            @unlink($filepath); // Delete video file from server after conversion
        }

        exit();
    }
}

echo purge_json_encode([
    'error' => 'Invalid Request'
]);
exit();