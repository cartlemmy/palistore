<?php
//ALLOWED_TYPES:text/html,text/csv

require_once(SL_INCLUDE_PATH."/class.slValue.php");
require(SL_WEB_PATH."/inc/store/class.storeItem.php");

$rep = new slReportOut();

$rep->setTitle("Order Items List - ".$this->getQueryAsText());

$rep->setType($mimeType);

$rep->setCols(array(
	"en-us|Item",
	"en-us|Options",
	"en-us|Count",
	"en-us|Price"
));

function itemsOverviewSort($a,$b) {
	return strcmp($a["name"],$b["name"]);
}


if ($this->res) {
	$count = 0;
	$total = new slValue("db/storeOrderItems","price");
	$items = array();
	while ($row = $this->res->fetchAsText()) {
		if ($r2 = $GLOBALS["slCore"]->db->select("db/storeOrders", array("id"=>$row["orderId"]))) {
			$order = $r2->fetch();
			if ($order["status"]=='' || $order["status"]=='payment-failed' || $order["status"]=='cancelled' || $order["status"]=='refunded' || $order["status"]=='in-cart') continue;
		}
		$total->add($row["price"]);
		$itemOb = new storeItem("OI:".$row["id"]);
		
		$options = array();
		if (setAndTrue($itemOb->cartItem,"option")) $options[] = $itemOb->cartItem["optionType"].": ".$itemOb->cartItem["option"];

		foreach ($cfg["subOptions"] as $n=>$v) {
			if ($itemOb->cartItem[$n]) $options[] = "$v: ".$itemOb->cartItem[$n];
		}
		
		$oh = $row["itemName"]."-".implode("-",$options);
		$optHash = md5($oh."-".$row["price"]);
		
		if (!isset($items[$optHash])) $items[$optHash] = array("oh"=>$oh,"name"=>$row["itemName"],"options"=>$options,"count"=>0,"price"=>$row["price"]);
				
		$items[$optHash]["count"] ++;
		$count++;
	}
	
	uasort($items,"itemsOverviewSort");
	
	$prevOh = "";
	foreach ($items as $item) {
		$rep->addRow(array(
			$item["name"],
			implode("<br />",$item["options"]),
			$item["count"],
			valueToString($item["price"],"currency")
		),$prevOh==$item["oh"]?array("style"=>"border-top-style:dashed"):false);
		$prevOh = $item["oh"];
	}
	
	$rep->addTotalRow("en-us|Total",array("count"=>$count,"price"=>$total->toString()));
} else {
	$rep->addInfoRow("en-us|There are no results for the selected parameters.","warning");
}

return array("type"=>$rep->getType(),"file"=>$rep->generateFile());
