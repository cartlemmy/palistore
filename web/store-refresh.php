<?php

$this->setTitle("en-us|Store Refresh");

$this->loginCheck();

if (!$GLOBALS["slSession"]->user->hasPermission("super OR admin OR store")) {
	?><p class="error">en-us|You do not have permission to access this page.</p><?php
	return;
}

require_once(SL_WEB_PATH."/inc/paliDB/config.php");
require_once(SL_WEB_PATH."/inc/paliDB/class.APIQuery.php");

echo '<pre>';
if (($rv = APIQuery::request("get-sessions",array("year"=>PALI_YEAR,"showPast"=>false),3600)) && $rv["success"]) {
	$sessions = $rv["res"];
	$updated = false;
	foreach ($sessions as $sess) {
		$update = array(
			"name"=>"Session ".$sess["name"].": ".date("M j",strtotime($sess["start"]))." - ".date("M j",strtotime($sess["end"])),
			"type"=>"one-week",
			"startDate"=>strtotime($sess["start"]),
			"endDate"=>strtotime($sess["end"])						
		);
		if (!$GLOBALS["slCore"]->db->select("db/paliSessions",array("startDate"=>$update["startDate"],"endDate"=>$update["endDate"]),array("limit"=>1))) {
			echo "Added ".$update["name"]."\n";
			$GLOBALS["slCore"]->db->insert("db/paliSessions",$update);
			$updated = true;
		}
	}
	
	if (!$updated) {
		echo "Store is already ready for ".PALI_YEAR;
	}
}
echo '</pre>';
