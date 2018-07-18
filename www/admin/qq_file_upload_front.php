<?php

$DEBUG_ALL = 1;
$ECHO_ALL = 0;
$PRINT_ALL = 1;

// test file for qqFileUploader (HTML side)
// load the Basic class here
require 'config.inc';
$base = new CoreLibs\Basic();
// set max upload size
$MAX_UPLOAD_SIZE = $base->StringByteFormat(ini_get('upload_max_filesize'));
$base->debug('UPLOADED FRONT', 'With max size: '.$MAX_UPLOAD_SIZE);

// very basic template output with super basic div for two file upload
?>
<html>
<head>
<title>File upload AJAX</title>
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

.qq-file-upload-button {
	border: 1px solid #999999;
	border-radius: 2px 2px 2px 2px;
	box-shadow: 0 10px rgba(255, 255, 255, 0.3) inset, 0 10px rgba(255, 255, 255, 0.2) inset, 0 10px 2px rgba(255, 255, 255, 0.25) inset, 0 -1px 2px rgba(0, 0, 0, 0.3) inset;
	height: 17px;
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

		var uploader = new qq.FileUploaderBasic({
			// element: document.getElementById(divID),
			element: $(divID),
			action: 'qq_file_upload_ajax.php',
			multiple: false,
			button: $(divID),
			allowedExtensions: ['csv', 'zip', 'jpg', 'pdf'],
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
				if ($(target + '_Progress').hasClassName('uploadError'))
				{
					$(target + '_Progress').className = '';
					$(target + '_Error').value = 0;
				}
				$(target + '_Progress').innerHTML = 'Start uploading file: ' + filename;
				// disabled stuff here
			},
			onProgress: function(id, filename, loaded, total, target) {
				console.log('Progress for file: "%s", ID: "%s", loaded: "%s", total: "%s" => "%s"', id, filename, loaded, total, target);
				var percent = Math.round((loaded / total) * 100);
				$(target + '_Progress').innerHTML = 'Uploading: ' + filename + ', ' + percent + '%' + ' (' + formatBytes(loaded) + '/' + formatBytes(total) + ')';
			},
			onComplete: function(id, filename, responseJSON, target) {
				console.log('File upload for file "%s", id "%s" done with status "%s" => "%s", And success: %s', filename, id, responseJSON, target, responseJSON.result.success);
				if (responseJSON.result.success)
				{
					$(target + '_Progress').innerHTML = 'Uploaded: ' + filename + ' (' + responseJSON.filesize_formated + ')';
					// also write hidden vars for this (file name, etc)
					// for that we replace the divName part from the target and get just the pos number ?
					// $(target + 'Name').value = filename;
					// $(target + 'NameUpload').value = responseJSON.filename;
					// $(target + 'Type').value = responseJSON.type;
					// $(target + 'Size').value = responseJSON.filesize;
				}
				else
				{
					// set the error class
					$(target + '_Progress').className = 'uploadError';
					// flag error
					$(target + '_Error').value = 1;
					// and write the error
					$(target + '_Progress').innerHTML = 'UPLOAD FAILED FOR FILE: ' + filename;
				}
				// renable stuff here
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
	<div>File upload via AJAX</div>
	<div class="flx-s">
		<div id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_MF5tISORiay7" class="normal qq-file-upload-button">Upload File</div>
		<div id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_MF5tISORiay7_Progress"></div>
		<input type="hidden" name="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_MF5tISORiay7_Error" id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_MF5tISORiay7_Error" value="">
	</div>
	<div class="flx-s">
		<div id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_Ww9iWKrl3Xou" class="normal qq-file-upload-button">Upload File</div>
		<div id="Uploader_3WD7MAFmjAux_dlvvu13tezNj_XeSO0Ovauli0_Ww9iWKrl3Xou_Progress"></div>
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
