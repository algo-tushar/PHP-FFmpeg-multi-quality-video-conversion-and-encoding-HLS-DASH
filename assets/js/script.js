(function ($) {
    $(document).ready(function () {
        /**
         * File Upload
         */

        let uploadContainer = $('#upload-container');
        var ajaxRequest = null;

        uploadContainer.on('drag dragstart dragend dragover dragenter dragleave drop', '.dropzone', function(e) {
            e.preventDefault();
            e.stopPropagation();
        })
        .on('dragover dragenter', function() {
            $(this).addClass('highlight');
        })
        .on('dragleave dragend drop', function() {
            $(this).removeClass('highlight');
        })
        .on('drop', function(e) {
            files = e.originalEvent.dataTransfer.files;
            uploadFiles(files);
        });

        uploadContainer.on('change', '#fileInput', function(e) {
            files = e.target.files;

            if (files.length > 3) {
                window.createNotification({
                    theme: 'warning',
                    displayCloseButton: true,
                    showDuration: 5000
                })({
                    message: "You can only select a maximum of 3 files."
                });
                
                $(this).val('');
                return;
            }
            
            // Proceed with uploading the selected files
            uploadFiles(files);
            $(this).val('');
        });

        function uploadFiles(files) {
            if (ajaxRequest !== null) {
                window.createNotification({
                    theme: 'warning',
                    displayCloseButton: true,
                    showDuration: 5000
                })({
                    message: "A request is already ongoing. Please wait for the current request to complete."
                });

                return; // Skip if a request is already ongoing
            }

            // Check if any files were selected
            if (files.length === 0) {
                window.createNotification({
                    theme: 'warning',
                    displayCloseButton: true,
                    showDuration: 5000
                })({
                    message: "No files selected."
                });
                return; // Skip this file
            }

            var allowedExtensions = ['mp4', 'avi', 'mkv', 'mov', 'flv', 'webm', 'mpeg', 'mpg', 'wmv', '3gp'];
            var filteredFiles = [];

            // Iterate through each file
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var fileType = file.type;
                var fileName = file.name;
                var fileSizeMB = file.size / (1024 * 1024); // Convert size to MB
                var extension = fileName.split('.').pop().toLowerCase();

                if ( file.size >= vid_enc.maxUploadSize ) {
                    // Display error message if file size exceeds limit
                    window.createNotification({
                        theme: 'warning',
                        displayCloseButton: true,
                        showDuration: 5000
                    })({
                        message: "File size of " + fileName + " exceeds the limit of " + vid_enc.maxUploadSize / (1024 * 1024) + "MB."
                    });
                    continue; // Skip this file
                }

                // Check file size
                if (fileSizeMB > 2048) {
                    // Display error message if file size exceeds limit
                    window.createNotification({
                        theme: 'warning',
                        displayCloseButton: true,
                        showDuration: 5000
                    })({
                        message: "File size of " + fileName + " exceeds the limit of 2GB."
                    });
                    continue; // Skip this file
                }

                // Check file extension
                if (!allowedExtensions.includes(extension)) {
                    // Display error message if file extension is not allowed
                    window.createNotification({
                        theme: 'warning',
                        displayCloseButton: true,
                        showDuration: 5000
                    })({
                        message: "File extension of " + fileName + " is not allowed."
                    });
                    continue; // Skip this file
                }

                // Add the file to the filteredFiles array
                filteredFiles.push(file);
            }

            // Check if any files remain after filtering
            if (filteredFiles.length === 0) {
                window.createNotification({
                    theme: 'warning',
                    displayCloseButton: true,
                    showDuration: 5000
                })({
                    message: "No valid files selected."
                });
                return;
            }

            
            var uploadFiles = uploadContainer.find('.upload-files');
            uploadFiles.empty();

            // Iterate through each filtered file and upload individually
            $.each(filteredFiles, function(index, file) {
                var formData = new FormData();
                formData.append('file', file);

                var fileHtml = '<li class="mt-3 mb-0 file_' + index + '">' 
                + '<div class="upload-file-container">' 
                    + '<i class="fa-solid fa-film file-type-icon"></i>'
                    + '<div class="file-name">' + file.name + '</div>'
                    + '<div class="file-action">'
                        + '<button class="cancel-upload"><i class="fa-solid fa-xmark"></i></button>'
                    + '</div>'
                + '</div>'
                + '<div class="upload-status"><div class="progress"><div class="progress-bar progress-bar-animated progress-bar-striped bg-info"></div></div></div>'
                + '</li>';
                uploadFiles.append(fileHtml);

                ajaxRequest = $.ajax({
                    url: vid_enc.home_url + "/includes/file-upload.php",
                    type: 'POST',
                    data: formData,
                    dataType: 'json', 
                    processData: false,
                    contentType: false,
                    caches: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener('progress', function(e) {
                            if (e.lengthComputable) {
                                var percent = (e.loaded / e.total) * 100;
                                uploadFiles.find('.file_' + index + ' .progress-bar').width(percent + '%').text(percent.toFixed(2) + '%');
                            }
                        });
                        return xhr;
                    },
                    success: function(response) {
                        if ( response && typeof response === 'object' && 'success' in response ) {
                            if ( response.success && response.filename && response.filename !== "") {
                                // Display success message
                                window.createNotification({
                                    theme: 'success',
                                    displayCloseButton: true,
                                    showDuration: 3000
                                })({
                                    message: "File " + file.name + " uploaded successfully."
                                });
                                
                                uploadFiles.find('.file_' + index + ' .progress-bar').css("width", "100%").removeClass("progress-bar-striped progress-bar-animated");

                                uploadFiles.find('.file_' + index + ' .file-action').html('<select class="form-select form-select-sm" name="file_' + index + '" data-filename="' + response.filename + '"><option value="dash">Dash</option><option value="hls">Hls</option></select><button class="btn btn-secondary btn-sm" name="convert_' + index + '">Convert</button>');
                            
                            } else {
                                // Display error message
                                window.createNotification({
                                    theme: 'warning',
                                    displayCloseButton: true,
                                    showDuration: 5000
                                })({
                                    message: "Failed to upload file " + file.name + ": " + response.message
                                });

                                uploadFiles.find('.file_' + index + ' .upload-status').html('<div class="alert alert-danger">' + response.message + '</div>');
                            }
                        }
                    },
                    complete: function() {
                        ajaxRequest = null; // Reset request on completion

                        //progressBar.hide();
                        //progress.width('0%');
                    },
                    error: function(xhr, status, error) {
                        ajaxRequest = null; // Reset request on completion
                        window.createNotification({
                            theme: 'warning',
                            displayCloseButton: true,
                            showDuration: 5000
                        })({
                            message: "Failed to upload file " + file.name + ": " + error
                        });

                        uploadFiles.find('.file_' + index + ' .upload-status').html('<div class="alert alert-danger">Failed to upload file ' + file.name + ': ' + error + '</div>');
                    }
                });

                
            });
        }

        uploadContainer.on('click', 'button[name^="convert_"]', function(e) {
            e.preventDefault();

            if (ajaxRequest !== null) {
                window.createNotification({
                    theme: 'warning',
                    displayCloseButton: true,
                    showDuration: 5000
                })({
                    message: "A request is already ongoing. Please wait for the current request to complete."
                });

                return; // Skip if a request is already ongoing
            }

            var uploadFiles = uploadContainer.find('.upload-files');
            var index = $(this).prop('name').split('_')[1];

            var select = $(this).closest('.file-action').find('select[name="file_'+index+'"]');
            var fileName = $(select).data('filename');
            
            if ( fileName && fileName.trim() !== '' && index && parseInt(index) >= 0 ) {
                var streaming = $(select).find('option:selected').val();
                
                $(this).prop('disabled', true);
                $(select).prop('disabled', true);
                
                ajaxRequest = $.ajax ({
                    url : vid_enc.home_url + "/includes/file-convert.php",
                    type: 'POST',
                    data : {
                        "filename": fileName,
                        "streaming": streaming // dash or hls
                    },
                    async: true,
                    beforeSend: function (xhr){
                        uploadFiles.find('.file_' + index + ' .upload-status').html('<div class="progress"><div class="progress-bar progress-bar-striped bg-warning"></div></div>');
                    },
                    xhrFields: {
                        onprogress: function(e) {
                            var response = e.target.responseText
                            if(previousResponse !== response){
                                const regex = new RegExp(`"\\bprogress\\b":\\s*("[^"]*"|\\d+),?\\s*}(?=[^{}]*$)`, 's');
                                const match = response.match(regex);
                                if ( match !== null && 1 in match ) {
                                    var percentage = String(match[1]) + "%";

                                    uploadFiles.find('.file_' + index + ' .progress-bar').css("width", percentage).html(percentage).addClass("progress-bar-animated");
                                }
                            }
                            var previousResponse = response;
                        }
                    },
                    success: function(response) {
                        //console.log(response);
                    },
                    complete: function (xhr, status){
                        ajaxRequest = null; // Reset request on completion
                        
                        // Clear progress bar
                        uploadFiles.find('.file_' + index + ' .upload-status').empty();

                        // Convert as zip
                        folderName = fileName.substring(0, fileName.lastIndexOf('.'));
                        convertZip(index, folderName);
                    },
                    error: function(xhr, status, error) {
                        ajaxRequest = null; // Reset request on completion

                        uploadFiles.find('.file_' + index + ' .upload-status').html('<div class="alert alert-danger">An error occurred during convert file</div>');
                    }
                });
            }
        });


        function convertZip(index, folderName) {
            if (ajaxRequest !== null) {
                window.createNotification({
                    theme: 'warning',
                    displayCloseButton: true,
                    showDuration: 5000
                })({
                    message: "A request is already ongoing. Please wait for the current request to complete."
                });

                return; // Skip if a request is already ongoing
            }

            var uploadFiles = uploadContainer.find('.upload-files');

            if ( folderName && folderName.trim() !== '' ) {
                ajaxRequest = $.ajax({
                    url : vid_enc.home_url + "/includes/zip-convert.php",
                    type: 'POST',
                    data: {
                        "foldername": folderName.trim()
                    },
                    beforeSend: function (xhr){
                        uploadFiles.find('.file_' + index + ' .file-action').html('<span>Converting as zip... <i class="fa-solid fa-spinner"></i></span>');
                    },
                    success: function(response) {
                        if ( response && typeof response === 'object' && 'success' in response ) {
                            if ( response.success && response.zipfilename && response.zipfilename !== "") {
                                var downloadlink = vid_enc.home_url + "/videos/" + response.zipfilename;
                                
                                uploadFiles.find('.file_' + index + ' .file-action').html('<a href="' + downloadlink + '" class="btn btn-sm btn-warning" download><i class="fa-solid fa-download"></i> Download</a>');
                            } else {
                                uploadFiles.find('.file_' + index + ' .upload-status').html('<div class="alert alert-danger">' + response.message + '</div>');
                            }
                        }
                    },
                    complete: function (xhr, status){
                        ajaxRequest = null; // Reset request on completion
                    },
                    error: function(xhr, status, error) {
                        ajaxRequest = null; // Reset request on completion
                        
                        uploadFiles.find('.file_' + index + ' .upload-status').html('<div class="alert alert-danger">An error occurred during convert file</div>');
                    }
                });
            }
        }

        
        $(window).on('beforeunload', function() {
            // If the Ajax request is still ongoing, abort it
            if (ajaxRequest) {
                ajaxRequest.abort();
            }
        });
    });
})(jQuery);