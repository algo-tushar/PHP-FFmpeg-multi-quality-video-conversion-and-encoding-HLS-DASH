<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
/**
 * Include configuraton file
 */
require_once 'config.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encode the video in multiple quality levels</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/fontawesome.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/notifications.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/fonts.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="page-wrapper">
        <!-- Header -->
        <header class="header text-center">
            <div class="container">
                <div class="branding">
                    <h1 class="logo">
                        <i class="fa-regular fa-file-video icon"></i>
                        <span class="text-highlight">Empowering Effortless Multi-Quality Video Conversion & Encoding to HLS/DASH with </span>
                        <span class="text-bold">FFmpeg</span>
                    </h1>
                </div>

                <!--//branding-->
                <div class="tagline">
                    <p>Optimize Your Streaming Experience with Seamless Format Conversion</p>
                </div>

                <div class="social-container">
                    <a class="github-button" href="https://github.com/algo-tushar" data-size="large"
                        aria-label="Follow @algo-tushar on GitHub">Follow @algo-tushar</a>
                </div>
                <!--//social-container-->
            </div>
            <!--//container-->
        </header>
        <!-- Header -->

        <!-- Main Section -->
        <section class="cards-section">
            <div class="container">
                <h2 class="title mb-3 text-center">Getting started is easy!</h2>
                <div class="intro">
                    <p class="text-center">
                    Unlock the Power of Seamless Video Conversion and Streaming with Our GitHub Project!
                    
                    Our innovative solution utilizes the robust capabilities of FFmpeg to encode video files into <strong>Multiple Quality</strong> formats, seamlessly converting them into <strong><a href="https://www.cloudflare.com/learning/video/what-is-mpeg-dash/" rel="nofollow" target="_blank">Dash/HLS formats</a></strong> for optimal streaming experiences.
                    </p>

                    <div class="d-flex flex-wrap justify-content-center align-items-center mt-5 mb-5">
                        <div id="upload-container">
                            <label for="fileInput" class="dropzone">
                                <input type="file" id="fileInput" name="fileInput" multiple class="visually-hidden" accept=".mp4,.avi,.mkv,.mov,.flv,.webm,.mpeg,.mpg,.wmv,.3gp">
                                <div class="dropzone-text-area">
                                    <i class="fas fa-cloud-upload-alt fs-3 me-3"></i>
                                    <div class="dropzone-text">
                                        <span class="mb-1 fs-5">Drag and drop files here</span>
                                        <small>Limit 2GB per file • MP4, AVI, MKV, MOV, FLV, WEBM, MPEG, MPG, WMV, 3GP</small>
                                    </div>
                                </div>
                                <span class="btn btn-primary dropzone-browse">Browse files</span>
                            </label>
                            <ul class="upload-files"></ul>
                        </div>
                    </div>

                    <ul class="list-group">
                        <li class="list-group-item"><strong>Intuitive Interface:</strong> Our platform boasts a user-friendly interface meticulously crafted for seamless navigation and operational efficiency.</li>
                        <li class="list-group-item"><strong>Effortless Upload Process:</strong> Users experience unparalleled ease when uploading their video files. Our intuitive system ensures a smooth and hassle-free process from start to finish.</li>
                        <li class="list-group-item"><strong>Automated Conversion Magic:</strong> Upon upload, our advanced backend springs into action, orchestrating a meticulously automated conversion process. Users can sit back and relax as our system handles the heavy lifting with precision and speed.</li>
                        <li class="list-group-item"><strong>Streamlined Packaging:</strong> Once the conversion is complete, users are presented with their transformed video files neatly packaged into a ZIP format. No more scattered files or confusion – everything is conveniently organized for immediate access.</li>
                        <li class="list-group-item"><strong>Instant Accessibility:</strong> With the converted files readily available in ZIP format, users can dive straight into utilizing their content without delay. Whether for streaming, sharing, or further editing, your videos are primed and ready for action.</li>
                    </ul>

                    <p style="margin-top:60px" class="text-center">
                        <strong>Github Repository:</strong>
                        <a href="https://github.com/algo-tushar/PHP-FFmpeg-multi-quality-video-conversion-and-encoding-HLS-DASH">https://github.com/algo-tushar/PHP-FFmpeg-multi-quality-video-conversion-and-encoding-HLS-DASH</a>
                    </p>
                </div>
                <!--//intro-->

            </div>
            <!--//container-->
        </section>
        <!-- /Main Section -->
    </div>

    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container">
            <small class="footer-text">Built with ❤️ <a href="https://freelancer.com/u/zabubakar">© Abubakar Wazih Tushar</a></small>
        </div>
    </footer>
    <!-- /Footer -->

    <script type="text/javascript">
    <?= localize_array('vid_enc', ['home_url' => get_home_url()]) ?>
    </script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script async defer src="https://buttons.github.io/buttons.js" type="text/javascript"></script>
    <script src="assets/js/jquery-3.7.1.min.js" type="text/javascript"></script>
    <script src="assets/js/notifications.js" type="text/javascript"></script>
    <script src="assets/js/script.js" type="text/javascript"></script>
</body>

</html>