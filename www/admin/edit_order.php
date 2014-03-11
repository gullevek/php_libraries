<?
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
	// overrride debug flags
	if (!DEBUG)
	{
		$DEBUG_ALL = 0;
		$PRINT_ALL = 0;
		$DB_DEBUG = 0;
		$ECHO_ALL = 0;
//		$DEBUG_TMPL = 0;
	}
	// set session name
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
	require(LIBS."Class.Login.inc");
	require(LIBS."Class.DB.IO.inc");
	require(LIBS.'Class.Smarty.Extend.inc');

	// default lang
	if (!$lang)
		$lang = DEFAULT_LANG;

	$table_width = 600;
	if (!$table_width)
		$table_width = PAGE_WIDTH;

	ob_end_flush();
	$login = new login($DB_CONFIG[LOGIN_DB], $lang);
	$db = new db_io($DB_CONFIG[MAIN_DB]);
	$db->db_exec("SET search_path TO ".LOGIN_DB_SCHEMA);
	$smarty = new SmartyML($lang);
	if (TARGET == 'live' || TARGET == 'remote')
	{
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
	if (defined('LAYOUT'))
	{
		$smarty->setTemplateDir(LAYOUT.DEFAULT_TEMPLATE.TEMPLATES);
		$DATA['css'] = LAYOUT.DEFAULT_TEMPLATE.CSS;
		$DATA['js'] = LAYOUT.DEFAULT_TEMPLATE.JS;
	}
	else
	{
		$smarty->setTemplateDir(TEMPLATES.DEFAULT_TEMPLATE);
		$DATA['css'] = CSS.DEFAULT_TEMPLATE;
		$DATA['js'] = JS.DEFAULT_TEMPLATE;
    }

	// order name is _always_ order_number for the edit interface

	// follwing arrays do exist here:
	// $position ... has the positions of the array (0..max), cause in a <select> I can't put an number into the array field, in this array, there are the POSITION stored, that should CHANGE there order (up/down)
	// $row_data_id ... has ALL ids from the sorting part
	// $row_data_order ... has ALL order positions from the soirting part
	if (count($position))
	{
		$original_id = $row_data_id;

		// FIRST u have to put right sort, then read again ...
		if ($up && $position[0] > 0) // hast to be >0 or the first one is selected and then there is no move
		{
			for ($i = 0; $i < count($position); $i++)
			{
				// change position order
				// this gets temp, id before that, gets actual (moves one "down")
				// this gets the old before (moves one "up")
				// is done for every element in row
//echo "A: ".$row_data_id[$position[$i]]." (".$row_data_order[$position[$i]].") -- ".$row_data_id[$position[$i]-1]." (".$row_data_order[$position[$i]-1].")<br>";
				$temp_id = $row_data_id[$position[$i]];
				$row_data_id[$position[$i]] = $row_data_id[$position[$i]-1];
				$row_data_id[$position[$i]-1] = $temp_id;
//echo "A: ".$row_data_id[$position[$i]]." (".$row_data_order[$position[$i]].") -- ".$row_data_id[$position[$i]-1]." (".$row_data_order[$position[$i]-1].")<br>";
			} // for
		} // if up

		if ($down && ($position[count($position) - 1] != (count($row_data_id) - 1))) // the last position id from position array is not to be the count-1 of row_data_id array, or it is the last element
		{
			for ($i = count($position) - 1; $i >= 0; $i --)
			{
				// same as up, just up in other way, starts from bottom (last element) and moves "up"
				// element before actuel gets temp, this element, becomes element after this,
				// element after this, gets this
				$temp_id = $row_data_id[$position[$i] + 1];
				$row_data_id[$position[$i] + 1] = $row_data_id[$position[$i]];
				$row_data_id[$position[$i]] = $temp_id;
			} // for
		} // if down

		// write data ... (which has to be abstrackt ...)
		if (($up && $position[0] > 0) || ($down && ($position[count($position) - 1]!=(count($row_data_id) - 1))))
		{
			for ($i = 0;$i < count($row_data_id); $i ++)
			{
//				$q="UPDATE broschueren SET broschuere_order=".$row_data_order[$i]." WHERE unique_id='".$row_data_id[$i]."'";
				$q = "UPDATE ".$table_name." SET order_number = ".$row_data_order[$i]." WHERE ".$table_name."_id = ".$row_data_id[$i];
//echo "Q: $q<br>";
				$q = $db->db_exec($q);
			} // for all article ids ...
		} // if write
	} // if there is something to move

	// get ...
	$q = "SELECT ".$table_name."_id, name, order_number FROM ".$table_name." ";
	if ($where_string)
		$q .= "WHERE $where_string "; 
	$q .= "ORDER BY order_number";

	while ($res = $db->db_return($q))
	{
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
	$HEADER['HTML_TITLE'] = ((!$L_TITLE) ? $smarty->l10n->__($G_TITLE) : $smarty->l10n->__($L_TITLE));

	$DATA['table_width'] = $table_width;

	// error msg
	if ($error)
	{
		$messages[] = array('msg' => $msg, 'class' => 'error', 'width' => $table_width);
	}
	$DATA['form_error_msg'] = $messages;

	// all the row data
	$options_id = array();
	$options_name = array();
	$options_selected = array();
	for ($i = 0; $i < count($row_data); $i ++)
	{
		$options_id[] = $i;
		$options_name[] = $row_data[$i]["name"];
		// list of points to order
		for ($j = 0; $j < count($position); $j++)
		{
			// if matches, put into select array
			if ($original_id[$position[$j]] == $row_data[$i]["id"])
				$options_selected[] = $i;
		}
	}
	$DATA['options_id'] = $options_id;
	$DATA['options_name'] = $options_name;
	$DATA['options_selected'] = $options_selected;

	// hidden list for the data (id, order number)
	$row_data_id = array();
	$row_data_order = array();
	for ($i = 0; $i < count($row_data); $i++)
	{
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
	$DEBUG_DATA['Id'] = '$Id: edit_order.php 4897 2014-02-06 08:16:56Z gullevek $';

	// create main data array
	$CONTENT_DATA = array_merge($HEADER, $DATA, $DEBUG_DATA);
	// data is 1:1 mapping (all vars, values, etc)
	while (list($key, $value) = each($CONTENT_DATA))
	{
		$smarty->assign($key, $value);
	}
	$smarty->display('edit_order.tpl');

	echo $login->print_error_msg();
	echo $db->print_error_msg();
?>