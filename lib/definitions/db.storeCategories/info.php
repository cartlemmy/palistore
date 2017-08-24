<?php

function db_storeCategory_update($v,&$items,$tableInfo) {
	$items["nameSafe"] = safeName($v);
}

return array(
	"coreTable"=>true,
	"name"=>"en-us|Categories",
	"singleName"=>"en-us|Category",
	"table"=>"db/storeCategories",
	"key"=>"id",
	"unique"=>"nameSafe",
	"displayName"=>array("item.name"),
	"nameField"=>"name",
	"nameSafeField"=>"nameSafe",
	"orderby"=>"name",
	"required"=>"name",
	"fields"=>array(
		"name"=>array(
			"label"=>"en-us|Name",
			"searchable"=>true,
			"cleaners"=>"trim,name",
			"updateFunction"=>"db_storeCategory_update"
		),
		"parent"=>array(
			"label"=>"en-us|Parent Category",
			"type"=>"object",
			"ref"=>"db/storeCategories"
		)
	)
);
