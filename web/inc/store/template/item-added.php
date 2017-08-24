<div>

<div class="success"><?=format(translate("en-us|%% item added to your cart"),$item["cnt"]);?>

<div class="store-item added">
	<div class="image"><img src="<?=$item["imageSrc"].".160w.".$item["imageExt"];?>"></div>
	
	<h3><?=$item["name"];?></h3>
	<span class="price"><?=$item["priceFormatted"];?></span>

	<?php
	if ($item["cartQuantity"] > 1) {
		echo "<div class=\"notify\">".format(translate("en-us|You have %% %% in your cart."),$item["cartQuantity"],$item["namePlural"])."</div>";
	}
	?>
	
	<div style="clear:both"></div>
</div>
</div>

</div>
