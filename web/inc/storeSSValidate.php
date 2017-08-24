<?php

file_put_contents(SL_DATA_PATH.'/store-debug.txt', json_encode($validateItems, JSON_PRETTY_PRINT));

/*$cartErrors = array();
foreach ($_POST as $n=>$v) {
	if (substr($n,0,13) == "deliveryType-") {
		$n = substr($n,13);
		if (!in_array($v, array("on-site", "address"))) {
			echo "<!-- HERE -->";
		}
	}
}*/

foreach ($validateItems["cart"] as $item) {
	//if ($validateItems["where"] == "checkout") {
		if (isset($item["deliveryType"]["new"])) {
			if (in_array($item["deliveryType"]["new"], array("on-site", "address"))) {
				switch ($item["deliveryType"]["new"]) {
					case "on-site":
						if (trim($item["camper"]["new"]) == "") {
							$this->cartError(
								$item["id"]["new"], "camper", 
								"Please provide the name of the camper you are sending this item to."
							);

						} else {
							//Check DB
							
						}
						if (!$item["paliSession"]["new"]) {
							$this->cartError(
								$item["id"]["new"], "paliSession", 
								"Please select the session you want this item delivered to."
							);
						}
						break;
					
					case "address":
						if (!$item["address"]["new"]) {
							$this->cartError(
								$item["id"]["new"], "address", 
								"Please select the address you want this item delivered to."
							);
						}						
						break;
				}
			} else {
				$this->cartError(
					$item["id"]["new"], "deliveryType", 
					"Please select a delivery type."
				);
			}
		}
	//}
}
