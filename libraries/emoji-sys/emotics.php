<?php
defined('_JEXEC') or die;
$db = JFactory::getDbo();
$query = $db->getQuery(true);

$type   = $app->input->getCmd('type', '');

if($type == "select" || $type== "")
{
	// Create a new query object.
	
	 
	// Select all records from the user profile table where key begins with "custom.".
	// Order it by the ordering field.
	$query->select($db->quoteName(array('a.*')));
	$query->from($db->quoteName('#__emotics', 'a'));
	//$query->where($db->quoteName('profile_key') . ' LIKE '. $db->quote('\'custom.%\''));
	 
	// Reset the query using our newly populated query object.
	$db->setQuery($query);
	 
	// Load the results as a list of stdClass objects (see later for more options on retrieving data).
	$results = $db->loadObjectList();

	$ret_value = json_encode($results);
	echo $ret_value;	
}
else if($type == "update")
{
	$act_type = 'stream';
	$ip_addr = "localhost";
	$act_id = 15;
	$love_cnt = 1;
	$like_cnt = 2;
	$haha_cnt = 3;
	$wow_cnt = 4;
	$sad_cnt = 5;
	$angry_cnt = 6;

	// Insert columns.
	$columns = array('act_type', 'act_id', 'ip_addr', 'love_cnt', 'like_cnt', 'haha_cnt', 'wow_cnt', 'sad_cnt', 'angry_cnt' );
	 
	// Insert values.
	$values = array($db->quote($act_type), $act_id, $db->quote($ip_addr), $love_cnt, $like_cnt, $haha_cnt, $wow_cnt, $sad_cnt, $angry_cnt);
	 
	// Prepare the insert query.
	$query
	    ->insert($db->quoteName('#__emotics'))
	    ->columns($db->quoteName($columns))
	    ->values(implode(',', $values));
	 
	// Set the query using our newly populated query object and execute it.
	$db->setQuery($query);
	$db->execute();
	echo "insert";
}


?>