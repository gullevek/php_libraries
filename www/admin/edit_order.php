<?php
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2001/07/11
* SHORT DESCRIPTION:
* sets the order from a table (edit_)
* HISTORY:
* 2005/07/11 (cs) adept to new edit interface
* 2002-10-18: little include changes
* 2001-07-11: erste Version
**********************************************************************/

$DEBUG_ALL = 1;
$DB_DEBUG = 1;

extract($_GET, EXTR_SKIP);
extract($_POST, EXTR_SKIP);

include("config.inc");
// set session name
define('SET_SESSION_NAME', EDIT_SESSION_NAME);
// overrride debug flags
if (!DEBUG) {
	$DEBUG_ALL = 0;
	$PRINT_ALL = 0;
	$DB_DEBUG = 0;
	$ECHO_ALL = 0;
}

// default lang
if (!$lang) {
	$lang = DEFAULT_LANG;
}

$table_width = 600;
if (!$table_width) {
	$table_width = PAGE_WIDTH;
}

ob_end_flush();
$login = new CoreLibs\ACL\Login($DB_CONFIG[LOGIN_DB], $lang);
$db = new CoreLibs\DB\IO($DB_CONFIG[MAIN_DB]);
$db->dbExec("SET search_path TO ".LOGIN_DB_SCHEMA);
$smarty = new CoreLibs\Template\SmartyExtend($lang);
if (TARGET == 'live' || TARGET == 'remote') {
	// login
	$login->debug_output_all = DEBUG ? 1 : 0;
	$login->echo_output_all = 0;
	$login->print_output_all = DEBUG ? 1 : 0;
	// form
	$db->debug_output_all = DEBUG ? 1 : 0;
	$db->echo_output_all = 0;
	$db->print_output_all = DEBUG ? 1 : 0;
}
// set the template dir
if (defined('LAYOUT')) {
	$smarty->setTemplateDir(LAYOUT.DEFAULT_TEMPLATE.TEMPLATES);
	$DATA['css'] = LAYOUT.DEFAULT_TEMPLATE.CSS;
	$DATA['js'] = LAYOUT.DEFAULT_TEMPLATE.JS;
} else {
	$smarty->setTemplateDir(TEMPLATES.DEFAULT_TEMPLATE);
	$DATA['css'] = CSS.DEFAULT_TEMPLATE;
	$DATA['js'] = JS.DEFAULT_TEMPLATE;
}

// order name is _always_ order_number for the edit interface

// follwing arrays do exist here:
// $position ... has the positions of the array (0..max), cause in a <select>
//               I can't put an number into the array field, in this array,
//               there are the POSITION stored, that should CHANGE there order (up/down)
// $row_data_id ... has ALL ids from the sorting part
// $row_data_order ... has ALL order positions from the soirting part
if (count($position)) {
	$original_id = $row_data_id;

	// FIRST u have to put right sort, then read again ...
	// hast to be >0 or the first one is selected and then there is no move
	if ($up && $position[0] > 0) {
		for ($i = 0; $i < count($position); $i++) {
			// change position order
			// this gets temp, id before that, gets actual (moves one "down")
			// this gets the old before (moves one "up")
			// is done for every element in row
			// echo "A: ".$row_data_id[$position[$i]]." (".$row_data_order[$position[$i]].") -- ".$row_data_id[$position[$i]-1]." (".$row_data_order[$position[$i]-1].")<br>";
			$temp_id = $row_data_id[$position[$i]];
			$row_data_id[$position[$i]] = $row_data_id[$position[$i]-1];
			$row_data_id[$position[$i]-1] = $temp_id;
			// echo "A: ".$row_data_id[$position[$i]]." (".$row_data_order[$position[$i]].") -- ".$row_data_id[$position[$i]-1]." (".$row_data_order[$position[$i]-1].")<br>";
		} // for
	} // if up

	// the last position id from position array is not to be the count-1 of row_data_id array, or it is the last element
	if ($down && ($position[count($position) - 1] != (count($row_data_id) - 1))) {
		for ($i = count($position) - 1; $i >= 0; $i --)	{
			// same as up, just up in other way, starts from bottom (last element) and moves "up"
			// element before actuel gets temp, this element, becomes element after this,
			// element after this, gets this
			$temp_id = $row_data_id[$position[$i] + 1];
			$row_data_id[$position[$i] + 1] = $row_data_id[$position[$i]];
			$row_data_id[$position[$i]] = $temp_id;
		} // for
	} // if down

	// write data ... (which has to be abstrackt ...)
	if (($up && $position[0] > 0) || ($down && ($position[count($position) - 1]!=(count($row_data_id) - 1)))) {
		for ($i = 0; $i < count($row_data_id); $i ++) {
			$q = "UPDATE ".$table_name." SET order_number = ".$row_data_order[$i]." WHERE ".$table_name."_id = ".$row_data_id[$i];
			$q = $db->dbExec($q);
		} // for all article ids ...
	} // if write
} // if there is something to move

// get ...
$q = "SELECT ".$table_name."_id, name, order_number FROM ".$table_name." ";
if ($where_string) {
	$q .= "WHERE $where_string ";
}
$q .= "ORDER BY order_number";

while ($res = $db->dbReturn($q)) {
	$row_data[] = array (
		"id" => $res[$table_name."_id"],
		"name" => $res["name"],
		"order" => $res["order_number"]
	);
} // while read data ...

// define all needed smarty stuff for the general HTML/page building
$DATA['css'] = LAYOUT.DEFAULT_TEMPLATE.CSS;
$DATA['js'] = LAYOUT.DEFAULT_TEMPLATE.JS;
$HEADER['CSS'] = CSS;
$HEADER['DEFAULT_ENCODING'] = DEFAULT_ENCODING;
$HEADER['JS'] = JS;
$HEADER['STYLESHEET'] = $EDIT_STYLESHEET;
$HEADER['JAVASCRIPT'] = $EDIT_JAVASCRIPT;
// html title
$HEADER['HTML_TITLE'] = (!$L_TITLE) ? $smarty->l10n->__($G_TITLE) : $smarty->l10n->__($L_TITLE);

$DATA['table_width'] = $table_width;

// error msg
if ($error) {
	$messages[] = array('msg' => $msg, 'class' => 'error', 'width' => $table_width);
}
$DATA['form_error_msg'] = $messages;

// all the row data
$options_id = array();
$options_name = array();
$options_selected = array();
for ($i = 0; $i < count($row_data); $i ++) {
	$options_id[] = $i;
	$options_name[] = $row_data[$i]["name"];
	// list of points to order
	for ($j = 0; $j < count($position); $j++) {
		// if matches, put into select array
		if ($original_id[$position[$j]] == $row_data[$i]["id"]) {
			$options_selected[] = $i;
		}
	}
}
$DATA['options_id'] = $options_id;
$DATA['options_name'] = $options_name;
$DATA['options_selected'] = $options_selected;

// hidden list for the data (id, order number)
$row_data_id = array();
$row_data_order = array();
for ($i = 0; $i < count($row_data); $i++) {
	$row_data_id[] = $row_data[$i]["id"];
	$row_data_order[] = $row_data[$i]["order"];
}
$DATA['row_data_id'] = $row_data_id;
$DATA['row_data_order'] = $row_data_order;

// hidden names for the table & where string
$DATA['table_name'] = $table_name;
$DATA['where_string'] = $where_string;

// debug data, if DEBUG flag is on, this data is print out
$DEBUG_DATA['DEBUG'] = $DEBUG_TMPL;

// create main data array
$CONTENT_DATA = array_merge($HEADER, $DATA, $DEBUG_DATA);
// data is 1:1 mapping (all vars, values, etc)
while (list($key, $value) = each($CONTENT_DATA)) {
	$smarty->assign($key, $value);
}
$smarty->display('edit_order.tpl');

echo $login->printErrorMsg();
echo $db->printErrorMsg();

# __END__

