<?php

$pos = isset($_GET["pos"]) ? (int)$_GET["pos"] : 0;
$idOffset = 100000;
$len = 10;
$this->setCaching(false);

require_once(SL_INCLUDE_PATH.'/class.bitcheck.php');

echo "<pre>";
$tables = array(
	'storeItems','storeOrderHistory','storeOrderItemHistory',
	'storeOrderItems','storeOrders'
);
$idFields = array("id","optionParent","orderId","orderItemId","item");
$inserted = 0;

if ($res = json_decode(file_get_contents('https://db.paliinstitute.com/store/tmp?start='.$pos.'&len='.$len), true)) {
	foreach ($tables as $tableI=>$table) {
		echo $table."\n";
		foreach ($res[$tableI] as $row) {
			$row["storeInstance"] = 2;
			foreach ($row as $n=>$v) {
				if (in_array($n, $idFields) && $row[$n] != 0) $row[$n] += $idOffset;
			}
			//$GLOBALS["showQuery"] = 1;
			$info = array();
			
			if (isset($row["ts"])) $info[] = date("Y-m-d g:ia", $row["ts"]);
			if (isset($row["name"])) $info[] = $row["name"];
			if (isset($row["itemName"])) $info[] = "item:".$row["itemName"];
						
			echo "\t ".$GLOBALS["slCore"]->db->insert("db/".$table,$row)." ".implode(" ", $info)."\n";
			$inserted ++;
		}
		echo "\n";
	}
}

$pos += 10;
if ($inserted) {
	?><script>window.location.href="?pos=<?=$pos;?>"</script><?php
}
