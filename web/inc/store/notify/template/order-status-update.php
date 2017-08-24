<h3>Order Status Updated</h3>
<p>Order #<?=$ob->getOrderNumber();?> status changed to '<?=valueToString($ob->get("status"),"db/storeOrderItems/status");?>'.</p>
<!--<p><a href="<?=WWW_BASE."admin/order-status?".$ob->get("id");?>">Manage Order</a></p>-->
<br />
<table cellpadding="4">
	<tbody>
		<tr>
			<td>Name:</td>
			<td><?=$ob->get("name");?></td>
		</tr>
		<tr>
			<td>E-mail:</td>
			<td><a href="mailto:<?=$ob->get("email");?>"><?=$ob->get("email");?></a></td>
		</tr>
		<tr>
			<td>Phone:</td>
			<td><?=$ob->get("phone");?></td>
		</tr>
		<tr>
			<td>Parent Name:</td>
			<td><?=$ob->get("parentName");?></td>
		</tr>
		<tr><td></td><td></td></tr>
		<tr>
			<td>Transaction ID:</td>
			<td><?=$ob->get("transactionID");?></td>
		</tr>
		<?php
			if ($ob->get("status") == "prep-ready") {
			$response = $ob->get("fullResponse");
		?>
			<tr>
				<td>Payment Processor:</td>
				<td><?=$ob->get("paymentProcessor");?></td>
			</tr>
			<?php if (isset($response["x_card_num"])) { ?>
			<tr>
				<td>CC #:</td>
				<td><?=$response["x_card_num"];?></td>
			</tr>
			<?php } ?>
			<tr>
				<td>Total:</td>
				<td><?=$ob->get("total");?></td>
			</tr>
		<?php } ?>
	</tbody>
</table>

<div class="info-section">
	<h3>en-us|Items</h3>
	<table style="width:100%" cellpadding="4">
		<thead><tr>
			<th style="text-align:left">Item</th>
			<th style="text-align:left">Status</th>
			<th style="text-align:left">Price</th>
		</tr></thead>
		<tbody>
			<?php
			$items = $ob2->getCartItems();

			foreach ($items as $item) {
				$itemOb = new storeItem("OI:".$item);
				$item = $itemOb->get();
				$cartItem = $itemOb->cartItem;
				echo "<tr>";
				echo "<td>".$cartItem["itemName"]."<br />";
				echo "For: ".$cartItem["camper"]."<br />";
				echo $cartItem["sess"]["name"];
				
				if (setAndTrue($cartItem,"option")) echo "<br />".$cartItem["optionType"].": ".$cartItem["option"];
				
				foreach ($cfg["subOptions"] as $n=>$v) {
					if (setAndTrue($cartItem,$n)) echo "<br />$v: ".$cartItem[$n];
				}
				echo "</td>";
				
				echo "<td>".valueToString($cartItem["status"],"db/storeOrderItems/status")."</td>";
				echo "<td>".valueToString($cartItem["price"],"currency")."</td>";
				echo "</tr>\n";
			}
			
			foreach ($ob2->totals as $row) {
				?><tr><td><?=$row["name"];?>: </td><td><?=$row["value"];?></td></tr><?php
			}
			?>
		</tbody>
	</table>
</div>
