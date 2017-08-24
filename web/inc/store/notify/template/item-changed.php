<?php

$sess = $ob["item"]->getOI("sess");
$info = translate($GLOBALS["slCore"]->db->getTableInfo("db/storeOrderItems"));

?><h3>Item Updated</h3>
<p>Item #<?=$ob["item"]->getOI("id");?></p>
<!--<p><a href="<?=WWW_BASE."admin/item-status?".$ob["item"]->getOI("id");?>">Manage Order Item</a></p>-->
<br />
<table cellpadding="4">
	<tbody>
		<?php foreach ($ob["to"] as $n=>$v) { if (!is_array($v)) {?>
			<tr>
				<td><?=isset($info["fields"][$n]["label"]) ? $info["fields"][$n]["label"] : ucfirst($n);?>: </td>
				<td><?=$ob["from"][$n]?"From '".valueToString($ob["from"][$n],"db/storeOrderItems/".$n)."'":""?></td>
				<td>To '<?=valueToString($v,"db/storeOrderItems/".$n);?>'</td>
			</tr>
		<?php }} ?>
	</tbody>
</table>
