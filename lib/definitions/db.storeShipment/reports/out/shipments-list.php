<?php
//ALLOWED_TYPES:text/html,text/csv

require_once(SL_INCLUDE_PATH."/class.slValue.php");

$rep = new slReportOut();

$rep->setType($mimeType);

$rep->setTitle("Shipments - ".valueToString((int)$this->inputValues["paliSession"],"db/storeShipment/paliSession"));

$rep->setCols(array(
	"en-us|Destination",
	"en-us|Status",
	"en-us|Ship Date",
	/*"en-us|POS",
	"en-us|Lower Office",
	"en-us|Delivered",
	"en-us|Price"*/
));

$rep->setOrderBy($this->orderBy);

$ids = array();
if ($this->res) {
	//$total = new slValue("db/storeShipment","price");
	while ($row = $this->res->fetchAsText(true)) {
		//if ($res = $GLOBALS["slCore"]->db->select("db/storeOrders", array("id"=>$row["orderId"]))) {
			//$order = $res->fetch();
			$ids[] = $row["id"];
			
			//$total->add($row["price.raw"]);
			
			$rep->addRow(array(
				$row["name"],
				$row["status"],
				$row["shipDate"]
			),array("click"=>"sl.coreOb.open('edit/?db/storeShipment&".$row["id"]."')"));
		}
	//}
	
	/*if (isset($editableList)) {
		$rep->addRow(array(
			"Check All",
			"",
			checkAllCol("pos"),
			checkAllCol("lowerOffice"),
			checkAllCol("delivered"),
			checkAllCol("notePrinted"),
			""
		));
	}*/	
	
	//$rep->addTotalRow("en-us|Total",array("price"=>$total->toString()));
} else {
	$rep->addInfoRow("en-us|There are no results for the selected parameters.","warning");
}


ob_start();
?><script type="text/javascript">
var ids = <?=json_encode($ids);?>;
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

function statusColUpdate(el) {
	var n = el.id.split("-"), id = n.pop();

	sl.coreOb.net.send("lib-req",["store/orderItem","statusCol",id,n.join("-"),el.checked],{"queueTime":0},function(response){
		//console.log(response);
		if (response && response.success) {
		}
	});
}

function statusColCheckAll(el,n) {
	for (var i in ids) {
		var id = ids[i];
		
		var cEl = document.getElementById(n+'-'+id);

		var oldChecked = cEl.checked;
		cEl.checked = el.checked;
		
		if (oldChecked != cEl.checked) statusColUpdate(cEl); 
	}
}
</script><?php

$rep->addHead(ob_get_clean());

return array("type"=>$rep->getType(),"file"=>$rep->generateFile());

function statusCol($n,$row,$editable) {
	switch ($n) {
		case "pos":
			$checked = !!$row["posEntered"];
			break;
		
		case "lowerOffice":
			$checked = !!$row["lowerOffice"];
			break;
		
		case "notePrinted":
			$checked = !!$row["notePrinted"];
			break;
				
		case "delivered":
			$checked = $row["status"] == "delivered";
			break;
	}
	/*if ($n == "notePrinted" && trim($row["camperNote"]) == "") {
		return "N/A";
	} else*/if ($editable) {
		return "<input type=\"checkbox\" id=\"".$n."-".$row["id"]."\" style=\"zoom:150%\"".($checked?" CHECKED":"")." onchange=\"statusColUpdate(this)\">";
	} else {
		return $checked?"&#10004;":"";
	}
}

function checkAllCol($n) {
	return "<input type=\"checkbox\" style=\"zoom:150%\" onchange=\"statusColCheckAll(this,'$n')\">";
}

function editableStatus($row) {
	$info = $GLOBALS["slCore"]->db->getTableInfo("db/storeShipment");
	$rv = "<select id=\"status-".$row["id"]."\" onchange=\"fieldChanged(this)\">";
	foreach ($info["fields"]["status"]["options"] as $n=>$v) {
		$rv .= "<option value=\"".$n."\"".($n == $row["status"] ? " SELECTED" : "").">".$v."</option>";
	}
	$rv .= "</select>";
	return $rv;
}
