<?php

$this->setCaching(false);

require_once(SL_WEB_PATH."/inc/paliDB/config.php");
require_once(SL_WEB_PATH."/inc/paliDB/class.APIQuery.php");

$fp = fopen(SL_DATA_PATH."/camper-prior-not-current.csv","w");

fputcsv($fp,array("Name","Address","Address 2","City, State, Zip, Country","Campers"));

if (($rv = APIQuery::request("get-family-info",array("startYear"=>"2016","range"=>"ALL","priorYearInfo"=>1),0)) && $rv["success"]) {
	
	foreach ($rv["res"] as $family) {
		$uids = array();
		foreach ($family["campers"] as $camper) {
			$uids[] = $camper["uid"];
		}
		
		$priorUids = array();
		if (isset($family["prior"])) {
			foreach ($family["prior"] as $prior) {
				foreach ($prior["campers"] as $camper) {
					if (!in_array($camper["uid"],$uids)) {
						
						$campers = array();
		
						foreach ($family["campers"] as $camper) {
							$campers[] = $camper["fname"];
						}
						
						fputcsv($fp,array(
							$family["fname"]." ".$family["lname"],
							$family["address"],
							$family["address2"],
							$family["city"].", ".$family["state"]." ".$family["zip"]." ".$family["country"],
							implode(", ",$campers)
						));	
						
						
						continue (3);
					}
				}
			}
		}
		
		
	}
}

fclose($fp);

exit();

