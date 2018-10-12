<?php

$DEBUG_ALL = 1;
$ECHO_ALL = 0;
$PRINT_ALL = 1;

// test file for qqFileUploader (HTML side)
// load the Basic class here
require 'config.php';
$base = new CoreLibs\Basic();
// set max upload size
$MAX_UPLOAD_SIZE = $base->StringByteFormat(ini_get('upload_max_filesize'));
$base->debug('UPLOADED FRONT', 'With max size: '.$MAX_UPLOAD_SIZE);

// very basic template output with super basic div for two file upload
?>
<html>
<head>
<title>File upload AJAX</title>
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
<style type="text/css">
.normal {
	width: 25%;
}
.flx-s {
	align-content: stretch;
	display: flex;
	flex: 1 100%;
}
.uploadError {
	font-weight: bold;
	color: red;
}
.uploadCancel {
	font-weight: bold;
	color: orange;
}

.qq-file-upload-button {
	border: 1px solid #999999;
	border-radius: 2px 2px 2px 2px;
	box-shadow: 0 10px rgba(255, 255, 255, 0.3) inset, 0 10px rgba(255, 255, 255, 0.2) inset, 0 10px 2px rgba(255, 255, 255, 0.25) inset, 0 -1px 2px rgba(0, 0, 0, 0.3) inset;
	text-align: center;
	padding: 3px 5px 3px;
	background-color: #cacaca;
	margin: 2px;
}

.qq-file-upload-button:hover {
	box-shadow: 0 10px 2px rgba(107, 107, 107, 0.2) inset, 0 12px rgba(107, 107, 107, 0.05) inset, 0 12px 2px rgba(107, 107, 107, 0.1) inset, 0 -1px 2px rgba(255, 255, 255, 0.3) inset;
}

.qq-file-upload-button:active {
	border: 1px solid red;
	background-color: rgba(80, 80, 80, 0.5);
}

.qq-upload-cancel {
	border: 1px solid red;
	border-radius: 2px;
	text-align: center;
	padding: 3px 5px 3px;
	background-color: #eb652d;
	margin: 2px;
}
.qq-upload-cancel:hover {
	background-color: #eb8686;
}
.qq-upload-cancel:active {
	border: 1px solid black;
	background-color: #eb2d2d;
}

.progressBarOutside {
	background-color: #f1f1f1;
	color: black;
	width: 100%;
}

.progressBarInside {
	background-color: #1e9e84;
	border-radius: 4px;
	padding: 0.01em;
	text-align: center;
	font-size: 0.8em;
}

</style>
<script src="layout/default/javascript/prototype.js" type="text/javascript"></script>
<script src="layout/default/javascript/file-uploader/fileuploader.js" type="text/javascript"></script>
<script type="text/javascript">
function formatBytes(bytes)
{
	var i = -1;
	do {
		bytes = bytes / 1024;
		i++;
	} while (bytes > 99);

	// return Math.max(bytes, 0.1).toFixed(1) + ['kB', 'MB', 'GB', 'TB', 'PB', 'EB'][i];
	return parseFloat(Math.round(bytes * Math.pow(10, 2)) / Math.pow(10, 2)) + ['kB', 'MB', 'GB', 'TB', 'PB', 'EB'][i];
}
var MAX_UPLOAD_SIZE = <?=$MAX_UPLOAD_SIZE;?>;
// function to add an AJAX uploadeder to the set
function createUploaderSin(divName, divNumber) {
	divID = divName + '_' + divNumber;
	console.log('Div: %s, Number: %s => ID: %s', divName, divNumber, divID);
	$(divID + '_Cancel').hide();
	var uploader = new qq.FileUploaderBasic({
		// element: document.getElementById(divID),
		element: $(divID),
		cancel: $(divID + '_Cancel'),
		action: 'qq_file_upload_ajax.php',
		multiple: false,
		button: $(divID),
		allowedExtensions: ['csv', 'zip', 'jpg', 'pdf', 'bz2'],
		sizeLimit: MAX_UPLOAD_SIZE, // size set from php ini
		name: divID,
		params: {
			'file_pos': divNumber, // we need to add here ID or something
			'action': 'upload',
			'task_uid': divNumber // -> test for some internal uid
		},
		onSubmit: function(id, filename, target) {
			console.log('File upload: "%s", ID: "%s" => "%s"', filename, id, target);
			// remove any assigned error classes and flags
			if ($(target + '_ProgressText').hasClassName('uploadError') || $(target + '_ProgressText').hasClassName('uploadCancel')) {
				$(target + '_ProgressText').className = '';
				$(target + '_Error').value = 0;
			}
			$(target + '_ProgressText').innerHTML = 'Start uploading file: ' + filename;
			$(target + '_Cancel').show();
			// disabled stuff here
		},
		onProgress: function(id, filename, loaded, total, target) {
			console.log('Progress for file: "%s", ID: "%s", loaded: "%s", total: "%s" => "%s"', id, filename, loaded, total, target);
			var percent = Math.round((loaded / total) * 100);
			$(target + '_ProgressBar').innerHTML = percent + '%';
			$(target + '_ProgressBar').style.width = percent + '%';
			$(target + '_ProgressText').innerHTML = 'Uploading: ' + filename + ', ' + formatBytes(loaded) + '/' + formatBytes(total);
		},
		onComplete: function(id, filename, responseJSON, target) {
			console.log('File upload for file "%s", id "%s" done with status "%s" => "%s", And success: %s', filename, id, responseJSON, target, responseJSON.result.success);
			if (responseJSON.result.success) {
				$(target + '_ProgressBar').innerHTML = '100%';
				$(target + '_ProgressBar').style.width = '100%';
				$(target + '_ProgressText').innerHTML = 'Uploaded: ' + filename + ' (' + responseJSON.filesize_formated + ')';
				// also write hidden vars for this (file name, etc)
				// for that we replace the divName part from the target and get just the pos number ?
				// $(target + 'Name').value = filename;
				// $(target + 'NameUpload').value = responseJSON.filename;
				// $(target + 'Type').value = responseJSON.type;
				// $(target + 'Size').value = responseJSON.filesize;
			} else {
				// set the error class
				$(target + '_ProgressText').className = 'uploadError';
				// flag error
				$(target + '_Error').value = 1;
				// and write the error
				$(target + '_ProgressText').innerHTML = 'UPLOAD FAILED FOR FILE: ' + filename;
			}
			// renable stuff here
			$(target + '_Cancel').hide();
		},
		onCancel: function (id, filename, target) {
			// cancel upload
			console.log('File upload cancled for file "%s", id "%s"', filename, id);
			// upload cancel
			$(target + '_ProgressText').className = 'uploadCancel';
			$(target + '_Error').value = 1;
			$(target + '_ProgressText').innerHTML = 'UPLOAD CANCELED FOR FILE: ' + filename;
			$(target + '_Cancel').hide();
		},
		/*showMessage: function(message) {
			console.log('MESSAGE: %s', message);
		}, */
		debug: true
	});
	// console.log('INIT Nr %s => cnt: %s', divNumber, uploader);
	return uploader;
}
</script>
</head>

<body>
<div id="masterGroup">
	<div>File upload via AJAX (MAX SIZE: <?=$base->byteStringFormat($MAX_UPLOAD_SIZE);?>)</div>
	<div class="flx-s">
		<div id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_MF5tISORiay7" class="normal qq-file-upload-button" style="width: 20%">Upload File</div>
		<div style="width: 5%">
			<div id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_MF5tISORiay7_Cancel" class="qq-upload-cancel">X</div>
		</div>
		<div style="width: 75%; padding: 5px;">
			<div id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_MF5tISORiay7_ProgressText"></div>
			<div class="progressBarOutside">
				<div class="progressBarInside" id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_MF5tISORiay7_ProgressBar" style="width: 0%">0%</div>
			</div>
		</div>
		<input type="hidden" name="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_MF5tISORiay7_Error" id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_MF5tISORiay7_Error" value="">
	</div>
	<div class="flx-s">
		<div id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_Ww9iWKrl3Xou" class="normal qq-file-upload-button" style="width: 20%">Upload File</div>
		<div style="width: 5%">
			<div id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_Ww9iWKrl3Xou_Cancel" class="qq-upload-cancel">X</div>
		</div>
		<div style="width: 75%; padding: 5px;">
			<div id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_Ww9iWKrl3Xou_ProgressText"></div>
			<div class="progressBarOutside">
				<div class="progressBarInside" id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_Ww9iWKrl3Xou_ProgressBar" style="width: 0%">0%</div>
			</div>
		</div>
		<input type="hidden" name="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_Ww9iWKrl3Xou_Error" id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_Ww9iWKrl3Xou_Error" value="">
	</div>
</div>
</body>
</html>
<script type="text/javascript">
// attach uploader to div areas
createUploaderSin('Uploader', '3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_MF5tISORiay7');
createUploaderSin('Uploader', '3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_Ww9iWKrl3Xou');
</script>
<?php
$base->printErrorMsg();

// __END__
