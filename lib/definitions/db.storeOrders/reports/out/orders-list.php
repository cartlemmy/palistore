<?php
//ALLOWED_TYPES:text/html,text/csv

require_once(SL_INCLUDE_PATH."/class.slValue.php");

$rep = new slReportOut();

$rep->setType($mimeType);
$rep->setTitle("Orders - Order Date: ".valueToString($this->inputValues["day"],"dateRange"));

$rep->setCols(array(
	"en-us|Order #",
	"en-us|Parent Name",
	"en-us|Phone Number",
	"en-us|E-mail",
	"en-us|Status",
	"en-us|Price",
	"en-us|POS"
));


if ($this->res) {
	$statusTotals = array();
	$total = new slValue("db/storeOrderItems","price");
	while ($order = $this->res->fetchAsText()) {
		if (!isset($statusTotals[$order["status"]])) $statusTotals[$order["status"]] = new slValue("db/storeOrderItems","price");
		$total->add($order["total"]);
		$statusTotals[$order["status"]]->add($order["total"]);
		
		$rep->addRow(array(
			$order["id"],
			$order["name"],
			$order["phone"],
			$order["email"],
			valueToString($order["status"],"db/storeOrderItems/status"),
			valueToString($order["total"],"currency"),
			$order["posEntered"]?"&#10004;":""
		));
	}
	
	if (count($statusTotals) > 1) {
		foreach ($statusTotals as $status=>$tot) {
			$rep->addTotalRow(valueToString($status,"db/storeOrderItems/status")." Total",array("price"=>$tot->toString()));
		}
	}
		
	$rep->addTotalRow("en-us|Grand Total",array("price"=>$total->toString()));
	
} else {
	$rep->addInfoRow("en-us|There are no results for the selected parameters.","warning");
}		

return array("type"=>$rep->getType(),"file"=>$rep->generateFile());
