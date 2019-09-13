<?php

$DEBUG_ALL = 1;
$ECHO_ALL = 0;
$PRINT_ALL = 1;

// load the Basic && qq file uploader here
require 'config.php';
// class load
$base = new CoreLibs\Basic();
$base->debug('AJAX UPLOAD', 'START Backend');
// set max upload size
$MAX_UPLOAD_SIZE = $base->StringByteFormat(ini_get('upload_max_filesize'));

// test for qqFileUploader (AJAX side)
$allowedExtensions = array ('csv', 'zip', 'jpg', 'pdf', 'bz2');
$sizeLimit = $MAX_UPLOAD_SIZE; // as set in php ini
$base->debug('AJAX UPLOAD', 'Size: '.$sizeLimit.', Memory Limit: '.ini_get('memory_limit'));
$uploader = new FileUpload\qqFileUploader($allowedExtensions, $sizeLimit);
// either in post or get
$_action= $_POST['action'] ? $_POST['action'] : $_GET['action'];
$_task_uid = $_POST['task_uid'] ? $_POST['task_uid'] : $_GET['task_uid'];
$get_post['start'] = microtime(true);
$base->debug('AJAX UPLOAD', 'Action: '.$_action.', Task UID: '.$_task_uid.' => '.$base->dateStringFormat($get_post['start']));

$upload_path = ROOT.MEDIA.UPLOADS;
$get_post['result'] = $uploader->handleUpload($upload_path, false);
$base->debug('AJAX UPLOAD', 'Memory peak: '.$base->ByteStringFormat(memory_get_usage()).' | '.$base->ByteStringFormat(memory_get_peak_usage()));

// set file name
$get_post['filename'] = $uploader->uploadFileName;
$get_post['type'] = $uploader->uploadFileExt;
$get_post['filesize'] = filesize($uploader->uploadFileName);
$get_post['filesize_formated'] = $base->ByteStringFormat($get_post['filesize']);
$get_post['end'] = microtime(true);
$get_post['time'] = $get_post['end'] - $get_post['start'];

$base->debug('AJAX RESULT', $base->printAr($get_post));
// return data
$output = htmlspecialchars(json_encode($get_post), ENT_NOQUOTES);
// $base->debug('AJAX JSON', $output);
print $output;

$base->printErrorMsg();

// __END__
