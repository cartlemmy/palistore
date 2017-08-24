<?php $imgSize = "320w"; ?>
<a name="<?=$item["nameSafe"];?>" style="display:block;position:relative;top:-64px;visibility:hidden"></a>
<form id="item-form-<?=$item["id"];?>" action="<?=WWW_RELATIVE_BASE;?>store-add/?<?=$item["nameSafe"];?>" method="post"><div class="store-item<?=$item["featured"]?" featured":"";?>">
	<input type="hidden" name="i" value="<?=$item["nameSafe"];?>">
	
	<div class="image"><img id="item-image-<?=$item["id"];?>" style="cursor:pointer" onclick="zoomImage(this)" src="<?=$item["imageSrc"].".".$imgSize.".".$item["imageExt"];?>"></div>
	
	<div id="item-priceFormatted-<?=$item["id"];?>" class="price"><?=$item["priceFormatted"];?></div>
	
	<h3 id="item-name-<?=$item["id"];?>"><?=str_replace('Ts', '<span style="text-transform:none">Ts</span>', $item["name"]);?></h3>
	<span id="item-descriptionHTML-<?=$item["id"];?>" class="desc"><?=$item["descriptionHTML"];?></span><br />
	<div class="opt-container">
	<?php
		$ioRef = "itemOpt".$item["id"];
		if ($item["options"]) {
			$js = false;	
			foreach ($item["options"] as $optName=>$options) {
				if (store::optCount($options) == 1) {
				} else {
					
					if (!$js) {
						?><script type="text/javascript">var <?=$ioRef;?>=new itemOptions(<?=$item["id"].",".json_encode($item["options"]).",'$imgSize',".json_encode($optName);?>);</script><?php
						$js = true;
					}
					echo "<div class=\"option\">\n";
					echo "<label>".$optName."</label>\n";
					echo "<select onchange='".$ioRef.".change(".json_encode($optName).",this)'>\n";
					foreach ($options as $option) {
						echo "<option value=\"".$option["id"]."\"".(isset($info["si"]) && $info["si"] == $option["id"] ? " SELECTED" : "").">".$option["option"]."</option>\n";
					}
					echo "</select>\n</div>\n";
				}
			}
		}

		if ($subOpts = $itemOb->getSubOptions()) {
			foreach ($subOpts as $o) {
				echo "<div class=\"option\">\n";
				echo "<label>en-us|".$o["label"]."</label>\n";
				echo "<select id=\"".$o["n"]."\" name=\"".$o["n"]."\">\n";
				foreach ($o["opts"] as $n=>$option) {
					echo "<option value=\"".htmlspecialchars($n)."\"".(isset($info[$o["n"]]) && $info[$o["n"]] == $option ? " SELECTED" : "").">en-us|".htmlspecialchars($option)."</option>\n";
				}
				echo "</select>\n</div>\n";
			}
		}

		?>
		<div style="clear:both"></div>
	</div>
	
	<div style="clear:both"></div>
	<div>
		<script type="text/javascript">
		function <?=$ioRef;?>Validate(e) {
			return true;
			var t, errors = [], sel;
			<?php if ($item["options"]) { ?>
				if ((t = <?=$ioRef;?>.validate()) !== true) errors.push(t);
			<?php } ?>

			<?php 
			if ($subOpts = $itemOb->getSubOptions()) {
				foreach ($subOpts as $o) {
					?>
					sel = document.getElementById("<?=$o["n"];?>");
					if (sel.options[sel.selectedIndex].value == "0") {
						errors.push(<?=json_encode("Please select ".$o["label"]);?>);
					}
					<?php
				}
			}
			?>
			if (errors.length) {
				alert(errors.join("\n"));
				e.preventDefault();
				return false;
			}
		};
		
		</script>
		<button type="submit" style="float:right" class="pali-button-small pali-blue" onclick="return <?=$ioRef;?>Validate(event);"><span class="store-icons plus"></span>en-us| ADD TO CART</button>
		<div style="clear:both"></div>
	</div>
</div></form>
