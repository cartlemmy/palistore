<?php

require_once(SL_INCLUDE_PATH."/class.slValue.php");
require_once(SL_INCLUDE_PATH."/value/class.valueCurrency.php");

function db_storeItems_name_update($v,&$items,$tableInfo) {
	$items["nameSafe"] = safeName($items["name"].($items["optionType"]?" ".$items["optionType"]." ".$items["option"]:""));
}

function db_storeItems_price_update($v,&$items,$tableInfo) {
	$val = new slValue("db/storeItems","price");
	$val->value = $v;
	$val->update();
	$items["priceUSD"] = $val->getFloat();
}

function db_storeItems_update($newData,$oldData,$info) {
	if (setAndTrue($newData,"_INSERTED")) return;
	if ($newData["optionParent"] == 0) {
		$updateCols = array("name","namePlural","categories","description","subOptions","image","price","noTax");
		if ($res = $GLOBALS["slCore"]->db->select("db/storeItems", array("optionParent"=>$newData["_KEY"]))) {
			while ($row = $res->fetch()) {
				$update = array();
				foreach ($updateCols as $n) {
					if (trim($row[$n]) == "" || $row[$n] == $oldData[$n]) {
						$update[$n] = $newData[$n];
					} else {
						$d = str_replace($oldData[$n], $newData[$n], $row[$n]);
						if ($d != $row[$n]) $update[$n] = $d;
					}
				}
				if ($update) $GLOBALS["slCore"]->db->update("db/storeItems", $update, array("id"=>$row["id"]));
			}
		}
	}
}

return array(
	"name"=>"en-us|Items",
	"singleName"=>"en-us|Item",
	"table"=>"db/storeItems",
	"key"=>"id",
	"displayName"=>array("item.name"),
	"nameField"=>"name",
	"nameSafeField"=>"nameSafe",
	"orderby"=>"sortOrder",
	"required"=>"name",
	"updateFunction"=>"db_storeItems_update",
	"oldData"=>1,
	"disableDelete"=>true,
	"optionGroup"=>array(
		"name"=>"en-us|Option",
		"parent"=>"optionParent",
		"nameField"=>"option",
		"typesField"=>"optionType",
		"fields"=>array(
			"description","image","price","active","quantity"
		),
		"defaultFields"=>array(
			"name","namePlural","description","image","price","active","categories","subOptions"
		),
		"exclusiveFields"=>array(
			"option","optionType"
		)
	),
	"fields"=>array(
		"name"=>array(
			"label"=>"en-us|Name",
			"searchable"=>true,
			"cleaners"=>"trim",
			"dependency"=>"option,optionType,name",
			"updateFunction"=>"db_storeItems_name_update"
		),
		"namePlural"=>array(
			"label"=>"en-us|Name (plural)",
			"searchable"=>true,
			"cleaners"=>"trim",
			"viewable"=>false
		),
		"optionType"=>array(
			"label"=>"en-us|Option Type",
			"type"=>"select",
			"options"=>array(
				""=>"en-us|Select One...",
				"color"=>"en-us|Color",
				"size"=>"en-us|Size",
				"style"=>"en-us|Style"
			),
			"dependency"=>"option,optionType,name",
			"updateFunction"=>"db_storeItems_name_update"
		),
		"option"=>array(
			"label"=>"en-us|Option",
			"searchable"=>true,
			"cleaners"=>"trim",
			"dependency"=>"option,optionType,name",
			"updateFunction"=>"db_storeItems_name_update"
		),
		"subOptions"=>array(
			"label"=>"en-us|Sub Options",
			"cleaners"=>"trim",
			"type"=>"text",
			"multi"=>true
		),
		"description"=>array(
			"label"=>"en-us|Description",
			"type"=>"textarea",
			"searchable"=>true,
			"cleaners"=>"trim"
		),
		"image"=>array(
			"label"=>"en-us|Image",
			"type"=>"image",
			"viewable"=>false,
			"useAsIcon"=>true
		),
		"price"=>array(
			"label"=>"en-us|Price",
			"type"=>"currency",
			"updateFunction"=>"db_storeItems_price_update"
		),
		"quantity"=>array(
			"label"=>"en-us|Quantity",
			"type"=>"number"
		),
		"adjustedQuantity"=>array(
			"label"=>"en-us|Adj. Quantity",
			"type"=>"number"
		),
		"categories"=>array(
			"label"=>"en-us|Categories",
			"singleLabel"=>"en-us|Category",
			"multi"=>true,
			"type"=>"object",
			"ref"=>"db/storeCategories"
		),
		"active"=>array(
			"label"=>"en-us|Active",
			"type"=>"select",
			"indexIsValue"=>true,
			"options"=>array(
				"en-us|Not Active",
				"en-us|Active"
			),
			"massChange"=>true
		),
		"sold"=>array(
			"label"=>"en-us|Number Sold",
			"type"=>"number"
		),
		"sortOrder"=>array(
			"label"=>"en-us|Sort Order",
			"type"=>"number"
		),
		"addon"=>array(
			"label"=>"en-us|Add-on Item",
			"type"=>"select",
			"indexIsValue"=>true,
			"options"=>array(
				"en-us|--",
				"en-us|Add-on"
			)
		),
		"featured"=>array(
			"label"=>"en-us|Featured",
			"type"=>"select",
			"indexIsValue"=>true,
			"options"=>array(
				"en-us|--",
				"en-us|Featured"
			)
		)
	)
);
