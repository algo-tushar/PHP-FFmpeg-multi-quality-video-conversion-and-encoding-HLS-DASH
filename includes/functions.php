<?php 

if ( ! defined( 'ROOTPATH' ) ) exit;

/**
 * Check whether SSL is active.
 *
 * @source https://developer.wordpress.org/reference/functions/is_ssl/
 * @return bool true if SSL, false if not active.
 */
function has_ssl() {
	if ( isset($_SERVER['HTTPS']) ) {
		if ( 'on' == strtolower($_SERVER['HTTPS']) )
			return true;
		if ( '1' == $_SERVER['HTTPS'] )
			return true;
	} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}
	return false;
}


/**
 * Get site home url
 *
 * @return string
 */
function get_home_url() {
    if ( defined('HOMEURL') && '' != HOMEURL ) {
        $siteurl = HOMEURL;
    } else {
        $root_dir = str_replace( '\\', '/', ROOTPATH );
        $script_dir = dirname($_SERVER['SCRIPT_FILENAME']);

        // The request is for a file in ROOTPATH
        if ($script_dir . '/' == $root_dir) {

            $path = preg_replace( '#/[^/]*$#i', '', $_SERVER['PHP_SELF'] );

        } else {

            if (false !== strpos( $_SERVER['SCRIPT_FILENAME'], $root_dir)) {
                $directory = str_replace( ROOTPATH, '', $script_dir );
                $path = preg_replace( '#/' . preg_quote( $directory, '#' ) . '/[^/]*$#i', '' , $_SERVER['REQUEST_URI'] );
            } elseif (false !== strpos( $root_dir, $script_dir)) {
                $subdirectory = substr( $root_dir, strpos( $root_dir, $script_dir ) + strlen( $script_dir ) );
                $path = preg_replace( '#/[^/]*$#i', '' , $_SERVER['REQUEST_URI'] ) . $subdirectory;
            } else {
                $path = $_SERVER['REQUEST_URI'];
            }

        }

        $schema = has_ssl() ? 'https://' : 'http://';
        $siteurl = $schema . $_SERVER['HTTP_HOST'] . $path;
    }

    return rtrim($siteurl, '/');
}

/**
 * Converts a string to UTF-8, so that it can be safely encoded to JSON.
 *
 * @source https://core.trac.wordpress.org/browser/tags/6.4/src/wp-includes/functions.php
 *
 * @param string $input_string The string which is to be converted.
 * @return string The checked string.
 */
function json_convert_string(string $input_string ) {
    $encoding = mb_detect_encoding( $input_string, mb_detect_order(), true );
    if ( $encoding ) {
        return mb_convert_encoding( $input_string, 'UTF-8', $encoding );
    } else {
        return mb_convert_encoding( $input_string, 'UTF-8', 'UTF-8' );
    }
}


/**
 * Performs sanity checks on data that shall be encoded to JSON
 * 
 * @source https://core.trac.wordpress.org/browser/tags/6.4/src/wp-includes/functions.php
 *
 * @throws Exception If depth limit is reached.
 * @param mixed $data  Variable (usually an array or object) to encode as JSON.
 * @param int   $depth Maximum depth to walk through $data. Must be greater than 0.
 * @return mixed The sanitized data that shall be encoded to JSON.
 */
function json_sanity_check( $data, int $depth ) {
	if ( $depth < 0 ) {
		throw new Exception("Reached depth limit");
	}

	if ( is_array( $data ) ) {
		$output = array();
		foreach ( $data as $id => $el ) {
			// Don't forget to sanitize the ID!
			if ( is_string( $id ) ) {
				$clean_id = json_convert_string( $id );
			} else {
				$clean_id = $id;
			}

			// Check the element type, so that we're only recursing if we really have to.
			if ( is_array( $el ) || is_object( $el ) ) {
				$output[ $clean_id ] = json_sanity_check( $el, $depth - 1 );
			} elseif ( is_string( $el ) ) {
				$output[ $clean_id ] = json_convert_string( $el );
			} else {
				$output[ $clean_id ] = $el;
			}
		}
	} elseif ( is_object( $data ) ) {
		$output = new stdClass();
		foreach ( $data as $id => $el ) {
			if ( is_string( $id ) ) {
				$clean_id = json_convert_string( $id );
			} else {
				$clean_id = $id;
			}

			if ( is_array( $el ) || is_object( $el ) ) {
				$output->$clean_id = json_sanity_check( $el, $depth - 1 );
			} elseif ( is_string( $el ) ) {
				$output->$clean_id = json_convert_string( $el );
			} else {
				$output->$clean_id = $el;
			}
		}
	} elseif ( is_string( $data ) ) {
		return json_convert_string( $data );
	} else {
		return $data;
	}

	return $output;
}


/**
 * Sanitize Json Encode
 *
 * @source https://developer.wordpress.org/reference/functions/wp_json_encode/
 * @return mixed
 */
function purge_json_encode( $data, int $options = 0, int $depth = 512 ) {
	$json = json_encode( $data, $options, $depth );

	// If json_encode() was successful, no need to do more sanity checking.
	if ( false !== $json ) {
		return $json;
	}

    try {
		$data = json_sanity_check( $data, $depth );
	} catch ( Exception $e ) {
		return false;
	}

	return json_encode( $data, $options, $depth );
}

/**
 * Convert string to bytes
 *
 * @param string $sizeString
 * @return int
 */
function convertToBytes($sizeString) {
    $sizeString = trim($sizeString);
    $last = strtolower($sizeString[strlen($sizeString)-1]);
    $size = (int)$sizeString;
    switch($last) {
        case 'g':
            $size *= 1024; // fall-through
        case 'm':
            $size *= 1024; // fall-through
        case 'k':
            $size *= 1024;
    }
    return $size;
}

/**
 * Localizes a script
 *
 * @param string $object_name Name of the variable that will contain the data.
 * @param array  $l10n        Array of data to localize.
 * @return string
 */
function localize_array(string $object_name, array $l10n, bool $include_max_upload_size = true): string {
    foreach ( $l10n as $key => $value ) {
        if ( ! is_scalar( $value ) ) {
            continue;
        }

        $l10n[$key] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
    }

	if ( $include_max_upload_size ) {
		$maxUploadSize = ini_get('upload_max_filesize');
		$maxUploadSizeBytes = convertToBytes($maxUploadSize);

		$l10n['maxUploadSize'] =  $maxUploadSizeBytes;
	}

    return "var $object_name = " . purge_json_encode($l10n);
}

/**
 * Store Log data
 * 
 * @param string
 * @return void
 */
function logger($log_msg = ""): void {
    $log_file_data = rtrim(LOGPATH, '/') . '/log_' . date('d-M-Y') . '.log';
    file_put_contents($log_file_data, $log_msg . "\n", FILE_APPEND);
}

/**
 * Delete a folder and its contents
 *
 * @param string $folderPath The path to the folder to delete
 * @return bool True if the folder was deleted, false otherwise
 */
function deleteFolder(string $folderPath) {
    // Check if the folder exists
    if (!file_exists($folderPath) || !is_dir($folderPath)) {
        return false; // Folder does not exist or is not a directory
    }

    // Open the directory
    $dirHandle = opendir($folderPath);

    // Iterate over the contents of the directory
    while (($file = readdir($dirHandle)) !== false) {
        if ($file != "." && $file != "..") {
            $filePath = $folderPath . DIRECTORY_SEPARATOR . $file;

            // If the current item is a directory, recursively delete it
            if (is_dir($filePath)) {
                deleteFolder($filePath);
            } else {
                // If it's a file, delete it
                unlink($filePath);
            }
        }
    }

    // Close the directory handle
    closedir($dirHandle);

    // Delete the empty directory
    return rmdir($folderPath);
}

/**
 * Clear a directory of files and folders older than a specified time
 *
 * @param string $directory The path to the directory to clear
 * @param int $timings The time in seconds after which files and folders should be deleted
 * @return void
 */
function clearDirectory(string $directory, int $timings = 3600): void {
    if (is_dir($directory)) {
        // Get the current time
        $currentTime = time();
    
        // Open the directory
        if ($dh = opendir($directory)) {
            // Iterate through each file and folder
            while (($file = readdir($dh)) !== false) {
                // Check if the file/folder name matches the specified pattern
                if (preg_match('/^(\d{10})_\d{3}(?:\.[a-zA-Z0-9]+)?$/', $file, $matches)) {
                    // Extract timestamp from the filename
                    $fileTimestamp = $matches[1];
    
                    // Check if the file/folder is older than 5 minutes
                    if ($currentTime - $fileTimestamp > $timings ) {
                        // Construct the file/folder path
                        $filePath = $directory . '/' . $file;
    
                        // Check if it's a file or directory and delete accordingly
                        if (is_file($filePath)) {
                            @unlink($filePath); // Delete file
                        } elseif (is_dir($filePath)) {
                            // Recursively delete the directory and its contents
                            deleteFolder($filePath);
                        }
                    }
                }
            }
            // Close the directory handle
            closedir($dh);
        }
    } else {
        return;
    }
}