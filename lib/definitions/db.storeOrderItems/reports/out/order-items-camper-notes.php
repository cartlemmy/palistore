<?php
//ALLOWED_TYPES:text/html

require_once(SL_INCLUDE_PATH."/class.slValue.php");

$rep = new slReportOut();

$rep->setTitle("Camper Notes - ".valueToString((int)$this->inputValues["paliSession"],"db/storeOrderItems/paliSession"));

$rep->beginPrintableHTML('<link href="'.webPath(SL_BASE_PATH."/css/store-font.css").'" rel="stylesheet">');

ob_start();

?><script type="text/javascript">
function markAsPrinted(ids) {
	sl.coreOb.net.send("lib-req",["store/orderItem","markAsPrinted",ids],{"queueTime":0},function(response){
		console.log(response);
	});
}
</script>
<style>
@media print
{    
    .no-print, .no-print *
    {
        display: none !important;
    }
}
</style>
<span class="no-print">
	<button style="zoom:150%" onclick="markAsPrinted([ids]);">Mark as Printed</button><br />
	<h3><?=$rep->getTitle();?></h3>
</span>
<?php
	$ids = array();
		if ($this->res) {
			$cnt = 0; $page = 1;
			while ($row = $this->res->fetchAsText()) {
				$ids[] = (int)$row["id"];
				if (trim($row["camperNote"]) != "" && !in_array($row["status"],array("in-cart","cancelled","delivered"))) {
					if ($res = $GLOBALS["slCore"]->db->select("db/storeOrders", array("id"=>$row["orderId"]))) {
						$order = $res->fetch();
					} else {
						$order = array("name"=>"N/A","phone"=>"N/A","email"=>"N/A");
					}
					if (($cnt % 4) == 0) {
						echo "<div style='clear:both;break-before:always;page-break-before:always;text-align:right;'>PG $page</div>";
						$page++;
					}
	?>
	<div style="float:left;width:4in;">
		<div style="height:1in;border:1px solid #DDD;border-bottom:none;"><div style="padding:0.1in;">
		<div class="infobox"><label><b>en-us|Camper Name: </b></label><?=$row["camper"];?></div>
		<div class="infobox"><label><b>en-us|Item: </b></label><?=$row["itemName"];?><?php
			if (setAndTrue($row,"option")) echo ", <b>".$row["optionType"]."</b>:".$row["option"];
			foreach ($cfg["subOptions"] as $n=>$v) {
				if ($row[$n]) echo ", <b>$v</b>:".$row[$n];
			}
		?></div>
		<div class="infobox"><label>en-us|Parent: </label><?=$order["name"].", ".$order["phone"].", ".$order["email"];?></div>
		</div></div>
		<div style="border:1px solid #DDD;padding:0.1in;height:3.8in;font-size:14pt;font-family:store-note;overflow:hidden;"><?=str_replace("\n","<br />",htmlspecialchars($row["camperNote"]));?></div>
	</div>
	<?php
					$cnt ++;
				}
			}
		} else {
			?><div class="warning">en-us|There are no results for the selected parameters.</div><?php
		}

$c = ob_get_clean();
echo str_replace("[ids]",json_encode($ids),$c);

return array("type"=>$rep->getType(),"file"=>$rep->endPrintableHTML());
