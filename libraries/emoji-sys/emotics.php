<?php
defined('_JEXEC') or die;
$db = JFactory::getDbo();
$query = $db->getQuery(true);

$type   = $app->input->getCmd('type', '');
$act_type = $_REQUEST['act_type'];
$act_id = $_REQUEST['act_id'];

if(($act_type == "") || ($act_id == "")) {
	echo "Error Activity Type & ID";
	return;
}

if($type == "select" || $type== "")
{
	// Create a new query object.
		 
	// Select all records from the user profile table where key begins with "custom.".
	// Order it by the ordering field.
	$query->select(array('sum(a.love_cnt) as love_cnt, sum(a.like_cnt) as like_cnt
		, sum(a.haha_cnt) as haha_cnt , sum(a.wow_cnt) as wow_cnt , sum(a.sad_cnt) as sad_cnt
		, sum(a.angry_cnt) as angry_cnt'));
	
	
	$query->from($db->quoteName('#__emotics', 'a'));
	$query->where($db->quoteName('act_type') . ' ='. $db->quote($act_type), 'AND')
			->where($db->quoteName('act_id') . ' ='. $db->quote($act_id))
			->group($db->quoteName('act_id'));
	 
	// Reset the query using our newly populated query object.
	$db->setQuery($query);
	 
	// Load the results as a list of stdClass objects (see later for more options on retrieving data).
	$results = $db->loadObjectList();

	$ret_value = json_encode($results);
	echo $ret_value;	
}
else if($type == "update")
{
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
	    $ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
	    $ip = $_SERVER['REMOTE_ADDR'];
	}

	$query->select($db->quoteName(array('a.*')));
	$query->from($db->quoteName('#__emotics', 'a'));
	$query->where($db->quoteName('act_type') . ' ='. $db->quote($act_type), 'AND')
			->where($db->quoteName('act_id') . ' ='. $db->quote($act_id), 'AND')
			->where($db->quoteName('ip_addr') . ' ='. $db->quote($ip));
	 
	// Reset the query using our newly populated query object.
	$db->setQuery($query);
	 
	// Load the results as a list of stdClass objects (see later for more options on retrieving data).
	$results = $db->loadObjectList();
	$bExists = false;

	foreach($results as $row)
	{
	    $bExists = true;
		$prevObj = $row;
	}
	
	
	$object = new stdClass();
		
	if($bExists)
	{
		$object->love_cnt = $prevObj->love_cnt;
		$object->like_cnt = $prevObj->like_cnt;
		$object->haha_cnt = $prevObj->haha_cnt;
		$object->wow_cnt = $prevObj->wow_cnt;
		$object->sad_cnt = $prevObj->sad_cnt;
		$object->angry_cnt = $prevObj->angry_cnt;		
	}
	
	$emo_type = $_REQUEST['emo_type'];

	if($emo_type == "Love"){
	  $object->love_cnt = 1;
	}
	else if($emo_type == "Like"){
	  $object->like_cnt = 1;
	}
	else if($emo_type == "HaHa"){
	  $object->haha_cnt = 1;
	}
	else if($emo_type == "Wow"){
	  $object->wow_cnt = 1;
	}
	else if($emo_type == "Sad"){
	  $object->sad_cnt=1;
	}
	else if($emo_type == "Angry"){
	  $object->angry_cnt = 1;
	}

	// Must be a valid primary key value.
	if($bExists){
		$object->id = $prevObj->id;
	}

	$object->act_type = $act_type;
	$object->act_id = $act_id;
	$object->ip_addr = $ip;
	 
	print_r($object);

	if($bExists)
		$result = JFactory::getDbo()->updateObject('#__emotics', $object, 'id');
	else
		$result = JFactory::getDbo()->insertObject('#__emotics', $object);

	echo "Update";
}

?>
