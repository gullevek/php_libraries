<?
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2005/07/13
* SHORT DESCRIPTION:
* Create categories for CMS
* HISTORY:
* 2005/08/19 (cs) changed the mime type get from the _FILE to mime get fkt
*********************************************************************/

// DEBUG vars here
$DEBUG_ALL = 1;
$DB_DEBUG = 1;
$DEBUG_TMPL = 1;

//------------------------------ header
require("header.inc");
$MASTER_TEMPLATE_NAME = 'cms_popup.tpl';
$TEMPLATE_NAME = 'cms_files.tpl';
$PAGE_WIDTH = 750;
require("set_paths.inc");
//------------------------------ header

//------------------------------ processing data start
$form_name = $_GET['form'];
$value_name = $_GET['name'];
$data_id = $_GET['id'];
$show_type = $_GET['type']; // P (pic), F (flash), V (video), D (document: word), A (document: pdf), B (binary); , separated string

// default view is list
if (!$view && !$data_id) {
	$view = 'list';
} elseif (!$view && $data_id) {
	$view = 'list';
}
// default is online
if (!isset($online)) {
	$online = 't';
}
// if not set, it is a fresh load
if (!$show_amount) {
	$fresh_load = 1;
}
// the next two are for page view
if (!$start_row) {
	$start_row = 0;
}
if (!$show_amount) {
	$show_amount = 12;
}
if (!$show_type) {
	$show_type = 'P';
}

// yes no list (online)
$yesno_list['f'] = 'No';
$yesno_list['t'] = 'Yes';
// order list
$sort_order_list['date_created'] = 'ID / Insert time'; // default
$sort_order_list['file_name'] = 'File Name';
$sort_order_list['file_size'] = 'File Size';
$sort_order_list['mime_type'] = 'Mime Type';
$sort_order_list['name_en'] = 'Alt Name English';
$sort_order_list['name_ja'] = 'Alt Name Japanese';
$sort_order_list['date_updated'] = 'Updated';
if (!$sort_order) {
	$sort_order = 'date_created';
}
$sort_direction_list['ASC'] = 'Normal';
$sort_direction_list['DESC'] = 'Reverse';
if (!$sort_direction) {
	$sort_direction = 'ASC';
}
// set if we need to write to any of the set live queues
// a) on page save with set_live
// b) global page with live_queue
// set via QUEUE variable

// create 0 entries for: templates, menu, data_group?
if ($cms->action == 'new' && $cms->action_yes == 'true') {
	unset($file_uid);
	unset($file_id);
	unset($file_type);
	$new_okay = 1;
}
// file type: P picture, M mouse over picutre, F flash, V video, B binary
if ($cms->action == 'save') {
	if (!$file_type) {
		$file_type = 'B';
	}
	$file_ok = false;
	if (!$_FILES['file_up']['name'] && !$file_uid) {
		$cms->messages[] = array('msg' => 'No file name given', 'class' => 'error');
		$error = 1;
	}
	if (!$_FILES['file_up']['name'] && $file_uid) {
		$file_ok = true;
	}
	// echo "FILE TYPE: ".$_FILES['file_up']['type']."<br>";
	foreach (split(',', $show_type) as $_show_type) {
		// check if the uploaded filename matches to the given type
		if ($_FILES['file_up']['name'] && preg_match("/jpeg|png|gif/", $_FILES['file_up']['type']) && preg_match("/P/", $show_type)) {
			$file_ok = true;
		}
		if ($_FILES['file_up']['name'] && preg_match("/swf/", $_FILES['file_up']['type']) && preg_match("/F/", $show_type)) {
			$file_ok = true;
		}
		if ($_FILES['file_up']['name'] && preg_match("/video/", $_FILES['file_up']['type']) && preg_match("/V/", $show_type)) {
			$file_ok = true;
		}
		if ($_FILES['file_up']['name'] && preg_match("/msword|vnd.oasis.opendocument.text/", $_FILES['file_up']['type']) && preg_match("/D/", $show_type)) {
			$file_ok = true;
		}
		if ($_FILES['file_up']['name'] && preg_match("/pdf/", $_FILES['file_up']['type']) && preg_match("/A/", $show_type)) {
			$file_ok = true;
		}
		if ($_FILES['file_up']['name'] && preg_match("/B/", $show_type)) {
			$file_ok = true;
		}
	}
	// write out error messages according to show type
	if (!$file_ok) {
		if (preg_match("/P/", $show_type)) {
			$cms->messages[] = array('msg' => 'File is not a JPEG/PNG/GIF file', 'class' => 'error');
			$error = 1;
		}
		if (preg_match("/F/", $show_type)) {
			$cms->messages[] = array('msg' => 'File is not a Flash File', 'class' => 'error');
			$error = 1;
		}
		if (preg_match("/V/", $show_type)) {
			$cms->messages[] = array('msg' => 'File is not a Video', 'class' => 'error');
			$error = 1;
		}
		if (preg_match("/D/", $show_type)) {
			$cms->messages[] = array('msg' => 'File is not a DOC/ODT file', 'class' => 'error');
			$error = 1;
		}
		if (preg_match("/A/", $show_type)) {
			$cms->messages[] = array('msg' => 'File is not a PDF file', 'class' => 'error');
			$error = 1;
		}
		if (preg_match("/B/", $show_type)) {
			$cms->messages[] = array('msg' => 'No valid file was given', 'class' => 'error');
			$error = 1;
		}
	}
	// binary: all okay
	// if no error, save data
	if (!$error) {
		if ($_FILES['file_up']['name']) {
			$mime_type = $_FILES['file_up']['type'];
			$file_size = $_FILES['file_up']['size'];
			$file_name = $_FILES['file_up']['name'];
			// get picture size
			list($width, $height) = getimagesize($_FILES['file_up']['tmp_name']);
			$cms->debug('upload', "Width: $width X Height: $height");
			// set the file type and the target folder
			if (preg_match("/jpeg|png|gif/", $mime_type)) {
				$file_type = 'P';
			} elseif (preg_match("/swf/", $mime_type)) {
				$file_type = 'F';
			} elseif (preg_match("/video/", $mime_type)) {
				$file_type = 'V';
			} elseif (preg_match("/msword|vnd.oasis.opendocument.text/", $mime_type)) {
				$file_type = 'D';
			} elseif (preg_match("/pdf/", $mime_type)) {
				$file_type = 'A';
			} elseif ($mime_type) {
				$file_type = 'B';
			}
		}
		// if we have an id -> updated
		if ($file_uid) {
			$q = "UPDATE file SET";
			$q_set = " name_en = '".addslashes($name_en)."', name_ja = '".addslashes($name_ja)."', file_name = '".addslashes($file_name)."', online = '".$online."' ";
			if ($_FILES['file_up']['name']) {
				$q_set .= ", type = '".$file_type."', mime_type = '$mime_type', file_size = $file_size, size_x = $width, size_y = $height ";
			}
			$q .= $q_set."WHERE file_uid = '".$file_uid."'";
			$cms->db_exec($q);
			if (QUEUE == 'live_queue') {
				$sql_action = 'UPDATE';
				$sql_data = $q_set;
			}
		} else {
			// insert new data
			$file_uid = md5(uniqid(rand(), true));
			$q = "INSERT INTO file (name_en, name_ja, file_name, online, mime_type, file_size, size_x, size_y, file_uid, type) VALUES (";
			$q .= "'".addslashes($name_en)."', '".addslashes($name_ja)."', '".addslashes($file_name)."', '".$online."', '".$mime_type."', ";
			$q .= "$file_size, ".(($width) ? $width : 'NULL').", ".(($height) ? $height : 'NULL').", '".$file_uid."', '".$file_type."')";
			$cms->db_exec($q);
			$file_id = $cms->insert_id;
			// if queue
			if (QUEUE == 'live_queue') {
				$sql_data = $q;
				$sql_action = 'INSERT';
			}
		}
		$size_x = $width;
		$size_y = $height;
		$file = DEV_SCHEMA."_".$file_uid;
		// now upload the file
		if ($_FILES['file_up']['name']) {
			$upload_file = ROOT.MEDIA.$cms->data_path[$file_type].$file;
			// wipe out any old tmp data for this new upload
			if (is_array(glob(ROOT.TMP."thumb_".$file."*"))) {
				foreach (glob(ROOT.TMP."thumb_".$file."*") as $filename) {
					@unlink($filename);
				}
			}
			# copy file to correct path
			$error = move_uploaded_file($_FILES['file_up']['tmp_name'], $upload_file);

			$cms->debug('file_upload', "UP: $upload_file");
			$cms->debug('file_upload', "Orig: ".$cms->print_ar($_FILES['file_up']));

			// because I get bogus error info from move_uploaded_file ...
			$error = 0;
			if ($error) {
				$cms->debug('file_upload', "ERROR: $error | INI FSize: ".ini_get("upload_max_filesize"));
				$cms->messages[] = array('msg' => 'File upload failed', 'class' => 'error');
				$q = "DELETE FROM file WHERE file_uid = '".$file_uid."'";
				$cms->db_exec($q);
				unset($file_id);
				unset($file_uid);
				$view = 'list';
			} else {
				$cms->messages[] = array('msg' => 'File upload successful', 'class' => 'warning');
				// $view = 'list';
			}
		} // if file upload
		// create thumbs + file size
		$picture = $cms->cache_pictures.$cms->adbCreateThumbnail($file, 400, 280, $file_type, '', $cms->cache_pictures_root);
		$picture_small = $cms->cache_pictures.$cms->adbCreateThumbnail($file, 80, 60, $file_type, '', $cms->cache_pictures_root);
		$file_size = $cms->adbByteStringFormat($file_size);
		// for live queue this is here needed
		if (QUEUE == 'live_queue') {
			$q = "INSERT INTO ".GLOBAL_DB_SCHEMA.".live_queue (queue_key, key_value, key_name, type, target, data, group_key, action";
			if ($_FILES['file_up']['name']) {
				$q .= ", file";
			}
			$q .= ") VALUES ('".$cms->queue_name."', '".$file_uid."', 'file_uid', '".$sql_action."', 'file', '".$cms->db_escape_string($sql_data)."', '".$cms->queue_key."', '".$cms->action."'";
			if ($_FILES['file_up']['name']) {
				$q .= ", '".ROOT.MEDIA.$cms->data_path[$file_type].$file."#".ROOT.MEDIA.$cms->data_path[$file_type].PUBLIC_SCHEMA."_".$file_uid."'";
			}
			$q .= ")";
			$cms->db_exec($q);
		}
	} // if not error
}
if ($cms->action == 'delete' && $cms->action_yes == 'true') {
	$file_uid = $cms->action_id;
	$q = "SELECT type FROM file WHERE file_uid = '".$file_uid."'";
	list ($file_type) = $cms->db_return_row($q);
	// get the file type for the file path
	$q = "DELETE FROM file WHERE file_uid = '".$file_uid."'";
	$cms->db_exec($q);
	if (QUEUE == 'set_live') {
		$q = "INSERT INTO ".GLOBAL_DB_SCHEMA.".set_live (table_name, pkid, delete_flag) VALUES ('".$cms->page_name."', ".$file_uid.", 't')";
		$cms->db_exec($q);
	}
	if (QUEUE == 'live_queue') {
		$q = "INSERT INTO ".GLOBAL_DB_SCHEMA.".live_queue (queue_key, key_value, key_name, type, target, data, group_key, action, file) VALUES (";
		$q .= "'".$cms->queue_name."', '".$file_uid."', 'file_uid', 'DELETE', 'file', '', '".$cms->queue_key."', '".$cms->action."', '".ROOT.MEDIA.$cms->data_path[$file_type].PUBLIC_SCHEMA."_".$file_uid."')";
	}
	@unlink(ROOT.MEDIA.$cms->data_path[$file_type].DEV_SCHEMA."_".$file_uid);
	unset($file_uid);
	unset($file_id);
	$delete_done = 1;
	$view = 'list';
}
if ($cms->action == 'load') {
	$file_uid = $cms->action_id;
	// load the data
	$q = "SELECT file_id, name_en, name_ja, file_name, online, mime_type, file_size, size_x, size_y, type FROM file WHERE file_uid = '".$file_uid."'";
	list($file_id, $name_en, $name_ja, $file_name, $online, $mime_type, $file_size, $size_x, $size_y, $file_type) = $cms->db_return_row($q);
	// create thumbnail for edit view
	$file = DEV_SCHEMA."_".$file_uid;
	// thumbnails are only valid for pictures
	$picture = $cms->cache_pictures.$cms->adbCreateThumbnail($file, 400, 280, $file_type, '', $cms->cache_pictures_root);
	$picture_small = $cms->cache_pictures.$cms->adbCreateThumbnail($file, 80, 60, $file_type, '', $cms->cache_pictures_root);
	$file_size = $cms->adbByteStringFormat($file_size);
	// view to edit
	$view = 'edit';
}
if ($cms->action == 'add_new') {
	$view = 'edit';
}
if ($cms->action == 'view_files' && $cms->action_yes == 'true') {
	$view = 'list';
}
// set delete live
if ($cms->action_flag == 'set_live' && $cms->action = 'set_delete') {
	$q = "SELECT file_uid, pkid, type FROM ".LOGIN_DB_SCHEMA.".set_live sl, file f WHERE sl.pkid = f.file_uid table_name = '".$cms->page_name."' AND delete_flag = 't'";
	while ($res = $cms->db_return($q)) {
		$q_del = "DELETE FROM ".PUBLIC_SCHEMA.".file WHERE file_uid = '".$res['pkid'].'"';
		$cms->db_exec($q_del);
		@unlink(ROOT.MEDIA.$cms->data_path[$res['type']].PUBLIC_SCHEMA."_".$res['file_uid']);
	}
	$q = "DELETE FROM ".LOGIN_DB_SCHEMA.".set_live WHERE table_name = '".$cms->page_name."' AND delete_flag = 't'";
	$cms->db_exec($q);
}
if (DEV_SCHEMA != PUBLIC_SCHEMA) {
	// read out possible deleted, to add "delete from live"
	$q = "SELECT pkid FROM ".LOGIN_DB_SCHEMA.".set_live WHERE table_name = '".$cms->page_name."' AND delete_flag = 't'";
	while ($res = $cms->db_return($q, 3)) {
		$cms->DATA['set_delete'][]['pkid'] = $res['pkid'];
	}
}
// get th max entries
$q = "SELECT COUNT(file_uid) FROM file ";
$q_search_where = "WHERE type in ('".str_replace(',', "','", $show_type)."') ";
if ($search_what) {
	$q_search_where .= "AND LOWER(name_en) LIKE '%".addslashes(strtolower($search_what))."%' OR name_ja LIKE '%".addslashes($search_what)."%' OR LOWER(file_name) LIKE '%".addslashes(strtolower($search_what))."%' ";
}
$q .= $q_search_where;
// get selection from show_type
list ($file_count) = $cms->db_return_row($q);

if ($cms->action == 'browse') {
	// browse in the list of data
	switch ($cms->action_id) {
		case "<<<<":
			$start_row = 0;
			break;
		case "<":
			$start_row -= $show_amount;
			break;
		case ">":
			$start_row += $show_amount;
			break;
		case ">>>>":
			$start_row = $file_count - $show_amount;
			break;
		case "gopage":
			// for page is page -1, so page 1 start from 0, etc
			$start_row = ((($cms->action_value - 1) > 0) ? ($cms->action_value - 1) * $show_amount : 0);
			$current_page = $cms->action_value;
			break;
	}
}

// check overflow
if ($start_row < 0) {
	$start_row = 0;
}
if ($start_row > $file_count) {
	$start_row = $file_count - $show_amount;
}

// if we have a "fresh_load"
if ($fresh_load) {
	$count = 1;
	$q = "SELECT file_uid FROM file ";
	if ($q_search_where) {
		$q .= $q_search_where;
	}
	$q .= "ORDER BY ".$sort_order." ".$sort_direction." ";
	while ($res = $cms->db_return($q)) {
		// if data_id is set and not file_id, go to the page where the current highlight is, but only if this is a "virgin" load of the page
		if ($data_id && ($data_id == $res['file_uid'])) {
			$current_page = floor(($count / $show_amount));
			$start_row = $current_page * $show_amount;
			$current_page ++;
		}
		$count ++;
	}
}

// page forward/back buttons settings
if ($start_row > 0) {
	$cms->DATA['show_back'] = 1;
}
$cms->DATA['page_number'] = ceil($start_row / $show_amount) + 1;
$cms->DATA['page_count'] = ceil($file_count / $show_amount);
if ($cms->DATA['page_count'] > 2 && !$current_page) {
	$current_page = 1;
}
if (($start_row + $show_amount) < $file_count) {
	$cms->DATA['show_forward'] = 1;
}

$q = "SELECT file_id, name_en, name_ja, file_name, online, mime_type, file_size, size_x, size_y, file_uid, type FROM file ";
// if search what, search in name_en, name_ja, file_name for the string
if ($q_search_where) {
	$q .= $q_search_where;
}
$q .= "ORDER BY ".$sort_order." ".$sort_direction." ";
$q .= "LIMIT ".$show_amount." OFFSET ".$start_row;
while ($res = $cms->db_return($q)) {
	$data_files[] = array (
		'id' => $res['file_id'],
		'name_en' => $res['name_en'],
		'name_ja' => $res['name_ja'],
		'file_name' => $res['file_name'],
		'online' => $res['online'],
		'mime_type' => $res['mime_type'],
		'file_size' => $cms->adbByteStringFormat($res['file_size']),
		'size_x' => $res['size_x'],
		'size_y' => $res['size_y'],
		'file_uid' => $res['file_uid'],
		'file_type' => $res['type'],
		'picture' => $cms->cache_pictures.$cms->adbCreateThumbnail(DEV_SCHEMA.'_'.$res['file_uid'], 80, 60, $res['type'], '', $cms->cache_pictures_root)
	);
}

$cms->DATA['show_type'] = $show_type;
$cms->DATA['data_files'] = $data_files;
$cms->DATA['view'] = $view;
$cms->DATA['images_path'] = MEDIA.$cms->data_path[$file_type];
// get vars for position (only when)
$cms->DATA['form_name'] = $form_name;
$cms->DATA['value_name'] = $value_name;
$cms->DATA['file_id'] = $file_id;
$cms->DATA['file_uid'] = $file_uid;

// write back all the other vars
if (!($delete_done || $new_okay)) {
	// data name
	$cms->DATA['file_name'] = $file_name;
	$cms->DATA['name_en'] = $name_en;
	$cms->DATA['name_ja'] = $name_ja;
	$cms->DATA['mime_type'] = $mime_type;
	$cms->DATA['file_size'] = $file_size;
	$cms->DATA['size_x'] = $size_x;
	$cms->DATA['size_y'] = $size_y;
	$cms->DATA['online'] = $online;
	$cms->DATA['picture'] = $picture;
	$cms->DATA['picture_small'] = $picture_small;
	$cms->DATA['file_type'] = $file_type;
}
$cms->DATA['sort_order_list'] = $sort_order_list;
$cms->DATA['sort_order'] = $sort_order;
$cms->DATA['sort_direction_list'] = $sort_direction_list;
$cms->DATA['sort_direction'] = $sort_direction;
$cms->DATA['search_what'] = $search_what;
$cms->DATA['current_page'] = $current_page;
$cms->DATA['yesno_list'] = $yesno_list;
$cms->DATA['start_row'] = $start_row;
$cms->DATA['show_amount'] = $show_amount;
if ($data_id) {
	$cms->DATA['data_id'] = $data_id;
}
$cms->DATA['top'] = 0;
$cms->DATA['left'] = 0;

//------------------------------ processing data end

//------------------------------ smarty start
require("smarty.inc");
//------------------------------ smarty end

//------------------------------ footer
require("footer.inc");
//------------------------------ footer
