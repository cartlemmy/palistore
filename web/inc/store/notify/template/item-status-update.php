<?php

$sess = $ob->getOI("sess");
?><h3>Item Status Updated</h3>
<p>Item #<?=$ob->getOI("id");?> status changed to '<?=valueToString($ob->getOI("status"),"db/storeOrderItems/status");?>'.</p>
<!--<p><a href="<?=WWW_BASE."admin/item-status?".$ob->getOI("id");?>">Manage Order Item</a></p>-->
<br />
<table cellpadding="4">
	<tbody>
		<tr>
			<td>Item:</td>
			<td><?=$ob->get("name");?></td>
		</tr>
		<?php 
			if ($opts = $ob->getOI("subOptions")) {
				foreach ($opts as $n=>$v) {
					?><tr>
						<td><?=translate($n);?>:</td>
						<td><?=$v;?></td>
					</tr><?php
				}
			} 
		?>
		<tr>
			<td>Camper:</td>
			<td><?=$ob->getOI("camper");?></td>
		</tr>
		<tr>
			<td>Session:</td>
			<td><?=$sess["name"];?></td>
		</tr>
		<tr><td></td><td></td></tr>
		<tr>
			<td>E-mail:</td>
			<td><a href="mailto:<?=$ob2->get("email");?>"><?=$ob2->get("email");?></a></td>
		</tr>
		<tr>
			<td>Phone:</td>
			<td><?=$ob2->get("phone");?></td>
		</tr>
		<tr>
			<td>Parent Name:</td>
			<td><?=$ob2->get("parentName");?></td>
		</tr>
		<tr>
			<td>Order #:</td>
			<td><?=$ob2->getOrderNumber();?></td>
		</tr>
	</tbody>
</table>
