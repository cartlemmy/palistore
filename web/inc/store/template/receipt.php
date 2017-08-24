<div class="col-sm-6">
	<div class="info-section" style="height:250px">
	<h3>en-us|Purchase Information</h3>
	<table><tbody>
		<tr><td class="td-label" style="width:50%">en-us|Name</td><td style="width:50%"><?=$order->get("name");?></td></tr>
		<tr><td class="td-label">en-us|E-mail</td><td><?=$order->get("email");?></td></tr>
		<tr><td class="td-label">en-us|Phone</td><td><?=$order->get("phone");?></td></tr>
		<tr><td class="td-label">en-us|Parent Name</td><td><?=$order->get("parentName");?></td></tr>
		<tr><td colspan="2">&nbsp;</td></tr>
		<tr><td class="td-label">en-us|Total</td><td><?=$cart->total->toString();?></td></tr>
	</tbody></table>
	</div>
</div>

<div class="col-sm-6">
	<div class="info-section" style="height:250px">
	<h3>en-us|Order Summary</h3>
	<table><tbody>
		<tr><td class="td-label" style="width:50%">en-us|Order #</td><td style="width:50%"><?=$order->getOrderNumber();?></td></tr>
	<?php
		foreach ($cart->totals as $row) {
			?><tr><td class="td-label"><?=$row["name"];?></td><td><?=$row["value"];?></td></tr><?php
		}
	?>
	<?php if (setAndTrue($this->cfg,"noTax")) { ?>
	<tr>
		<td></td><td style="font-style:italic">en-us|Tax is included in the total.</td>
	</tr>
	<?php } ?>
	</tbody></table>
	<?php if ($order->get("status") != "ordered") {
		echo "<i>".format(translate("This order has been %%."),valueToString($order->get("status"),"db/storeOrders/status"))."</i>";
	}	?>
	</div>
</div>

<?php 
$shipments = $order->getShipmentStatus();
foreach ($shipments as $shipment) {
?>
<div class="col-sm-12">
	<div class="info-section">
		<p>
			<?=str_replace("\n", "<br />", $shipment["details"]);?>
		</p>
		<table class="table-striped">
			<thead><tr>
				<th>Item</th>
				<th>Price</th>
			</tr></thead>
			<tbody>
				<?php
					foreach ($shipment["items"] as $item) {
						echo "<tr>";
						echo "<td><b></b>".$item["itemName"]."</b>";
						if (setAndTrue($item,"camper")) echo "<br />For: ".$item["camper"];
												
						if (setAndTrue($item,"option")) echo "<br />".$item["optionType"].": ".$item["option"];
						
						foreach ($cfg["subOptions"] as $n=>$v) {
							if (setAndTrue($item,$n)) echo "<br />$v: ".$item[$n];
						}
						echo "</td>";
						
						echo "<td>".valueToString($item["price"],"currency")."</td>";
						echo "</tr>\n";
					}
				?>
			</tbody>
		</table>
	</div>
</div>
<?php } ?>
<div class="col-sm-12">
	<div class="info-section">
		<h3>en-us|Need Help?</h3>
		<table><tbody>
			<tr>
				<td>Call Us:</td>
				<td>1-909-867-5743</td>
			</tr>
			<tr>
				<td>E-mail Us:</td>
				<td><a href="mailto:<?=$cfg["fromEmail"]["email"]."?subject=".urlencode("Order #".$order->getOrderNumber());?>"><?=$cfg["fromEmail"]["email"];?></a></td>
			</tr>
			<tr><td colspan="2">Contact Form:</td></tr>
			<tr><td colspan="2"><?php require(SL_WEB_PATH."/inc/store-contact.php");?></td></tr>
		</tbody></table>
	</div>
</div>
