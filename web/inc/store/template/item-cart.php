<a name="ci-<?=$cartItem["id"];?>" class="anchor"></a>
<div class="store-item">
	<div class="image"><img src="<?=$item["imageSrc"].".194w.".$item["imageExt"];?>"></div>
			
	<div class="cart-div c1">
		<div class="cart-name"><?=str_replace('Ts', '<span style="text-transform:none">Ts</span>', $item["name"]);?></div>
		<span class="price"><?=$item["priceFormatted"];?></span>
		
		<div class="cart-sub-opts"><?php
			if (setAndTrue($cartItem,"option")) echo ucwords($cartItem["optionType"]).": ".$cartItem["option"]."<br />";
		
			if ($cartItem["subOptions"]) {
				$so = array();
				foreach ($this->cfg["subOptions"] as $n=>$v) {
					if (setAndTrue($cartItem,$n)) $so[] = "$v: ".$cartItem[$n];
				}
				echo implode("<br />",$so);
			}
		?>
		</div>
	</div>

	<div class="cart-div c2">
		<label for="camper-<?=$cartItem["id"];?>">en-us|Camper Name<br /></label><br />
		<input name="camper-<?=$cartItem["id"];?>" id="camper-<?=$cartItem["id"];?>" type="text" style="width:100%" value="<?=isset($cartItem["camper"]) ? htmlspecialchars($cartItem["camper"]) : "";?>"><br />
		<div id="msg-camper-<?=$cartItem["id"];?>"><?=$cartItem["message"]["camper"];?></div>
		<span class="hint" style="display:none" id="hint-camper-<?=$cartItem["id"];?>">en-us|Please enter the name of the camper you want this item to be sent to.</span>
	</div>

	<div class="cart-div c3">
		<label for="paliSession-<?=$cartItem["id"];?>">en-us|Deliver To</label><br />
		<?php if ($item["onSiteOnly"] || setAndTrue($GLOBALS["_STORE_CFG"],"disableOffsiteDelivery")) { ?>
		<input type="hidden" name="deliveryType-<?=$cartItem["id"];?>" value="on-site">
		<div id="msg-deliveryType-<?=$cartItem["id"];?>"><?=$cartItem["message"]["deliveryType"];?></div>
		<?php } else { ?>
		<select name="deliveryType-<?=$cartItem["id"];?>" id="deliveryType-<?=$cartItem["id"];?>" type="select" style="width:100%">
			<option value="">Select one...</option>
			<option value="on-site" <?=$cartItem["deliveryType"] == "on-site" ? " SELECTED" : "";?>>Deliver to my child at camp</option>
			<option value="address" <?=$cartItem["deliveryType"] == "address" ? " SELECTED" : "";?>>Deliver to an address</option>
		</select>
		<div id="msg-deliveryType-<?=$cartItem["id"];?>"><?=$cartItem["message"]["deliveryType"];?></div>
		<select name="address-<?=$cartItem["id"];?>" id="address-<?=$cartItem["id"];?>" type="select" style="width:100%;<?=$cartItem["deliveryType"] == "address" ? "" : "display:none";?>">
			<?php
				
				require_once(SL_WEB_PATH."/inc/store/class.storeAddressBook.php");

				$addr = new storeAddressBook();
				
				$addrOptions = array();
				
				$addrOptions[] = array(
					"id"=>"",
					"name"=>"Select one..."
				);
				
				while (($entry = $addr->fetch()) !== false) {
					$addrOptions[] = array(
						"id"=>"_ADDR:".$entry->id,
						"name"=>$entry->getName()
					);
				}
				$addrOptions[] = array(
					"id"=>"_ADDR:NEW",
					"name"=>"Other Address..."
				);
			
				foreach ($addrOptions as $data) {
					echo "<option value=\"".htmlspecialchars($data["id"])."\"".($data["id"] == $cartItem["address"] ? " SELECTED" : "").">".htmlspecialchars($data["name"])."</option>";
				}
			?>
		</select>
		<div id="msg-address-<?=$cartItem["id"];?>"><?=$cartItem["message"]["address"];?></div>
		<?php } ?>
		<select name="paliSession-<?=$cartItem["id"];?>" id="paliSession-<?=$cartItem["id"];?>" type="select" style="width:100%;<?=$cartItem["deliveryType"] == "on-site" ? "" : "display:none";?>">
			<?php
				
				$psId = $cartItem["paliSession"];
				foreach ($GLOBALS["storeSessionOptions"] as $data) {
					if (isset($data["label"])) {
						echo "<option value=\"\" DISABLED style=\"margin-top:8px;font-weight:bold\">".htmlspecialchars($data["label"])."</option>";
					} else {
						echo "<!-- ".$data["id"]." == ".$psId."--><option value=\"".htmlspecialchars($data["id"])."\"".($data["id"] == $psId ? " SELECTED" : "").">".htmlspecialchars($data["name"])."</option>";
					}
				}
			?>
		</select><br />
		<div id="msg-paliSession-<?=$cartItem["id"];?>"><?=$cartItem["message"]["paliSession"];?></div>
		<span class="hint" id="hint-paliSession-<?=$cartItem["id"];?>"></span>
	</div>
	
	<div style="clear:both;height:24px"></div>

	<div class="opt-container">
		<button type="submit" name="_REMOVE-<?=$cartItem["id"];?>" value="1" class="pali-button-small pali-red"><span class="store-icons minus"></span>en-us| REMOVE FROM CART</button>
		<button type="submit" name="_ADD_NOTE-<?=$cartItem["id"];?>" id="_ADD_NOTE-<?=$cartItem["id"];?>" value="1" class="pali-button-small pali-blue" <?=$cartItem["deliveryType"] == "on-site" ? "" : ' style="display:none"';?>><span class="store-icons note"></span>en-us| <?=trim($cartItem["camperNote"]) ? "EDIT" : "WRITE";?> NOTE TO CAMPER</button>
	</div>
	<div style="clear:both"></div>
	
	<?php if (isset($_GET["debug"])) echo '<pre>'.json_encode($cartItem, JSON_PRETTY_PRINT).'</pre>'; ?>
</div>
