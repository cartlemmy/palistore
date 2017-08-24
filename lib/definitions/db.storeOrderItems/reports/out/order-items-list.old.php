<?php
//ALLOWED_TYPES:text/html,text/csv

require_once(SL_INCLUDE_PATH."/class.slValue.php");

$rep = new slReportOut();

$rep->setType($mimeType);

$rep->setTitle("Order Items List - ".valueToString((int)$this->inputValues["paliSession"],"db/storeOrderItems/paliSession"));

$rep->setCols(array(
	"en-us|Camper Name",
	"en-us|Item",
	"en-us|Parent Name",
	"en-us|Phone Number",
	"en-us|E-mail",
	"en-us|Status",
	"en-us|Price"
));

$rep->setOrderBy($this->orderBy);

ob_start();
?><script type="text/javascript">
function fieldChanged(el) {
	var v, n = el.id.split("-"), id = n.pop();
	
	if (el.nodeName == "SELECT") {
		v = el.options[el.selectedIndex].value;
	} else {
		v = el.value;
	}
	
	sl.coreOb.net.send("lib-req",["store/orderItem","update",id,n.join("-"),v],{"queueTime":0},function(response){
		//console.log(response);
		if (response && response.success) {
		}
	});
	
}
</script><?php

$rep->addHead(ob_get_clean());

if ($this->res) {
	$total = new slValue("db/storeOrderItems","price");
	while ($row = $this->res->fetchAsText()) {
		if ($res = $GLOBALS["slCore"]->db->select("db/storeOrders", array("id"=>$row["orderId"]))) {
			$order = $res->fetch();
			$total->add($row["price"]);
			
			$opts = $row["itemName"];
			
			if (setAndTrue($row,"option")) $opts .= "<br />".$row["optionType"].": ".$row["option"];
					
			foreach ($cfg["subOptions"] as $n=>$v) {
				if ($row[$n]) $opts .= "<br />$v: ".$row[$n];
			}

			$rep->addRow(array(
				$row["camper"],
				$opts,
				$order["name"],
				$order["phone"],
				$order["email"],
				isset($editableList) ? editableStatus($row) : $row["status"],
				valueToString($row["price"],"currency")
			),array("click"=>"sl.coreOb.open('store/order-item/?db/storeOrderItems&".$row["id"]."')"));
		}
	}
	$rep->addTotalRow("en-us|Total",array("price"=>$total->toString()));
} else {
	$rep->addInfoRow("en-us|There are no results for the selected parameters.","warning");
}

return array("type"=>$rep->getType(),"file"=>$rep->generateFile());


function editableStatus($row) {
	$info = $GLOBALS["slCore"]->db->getTableInfo("db/storeOrders");
	$rv = "<select id=\"status-".$row["id"]."\" onchange=\"fieldChanged(this)\">";
	foreach ($info["fields"]["status"]["options"] as $n=>$v) {
		$rv .= "<option value=\"".$n."\"".($n == $row["status"] ? " SELECTED" : "").">".$v."</option>";
	}
	$rv .= "</select>";
	return $rv;
}
