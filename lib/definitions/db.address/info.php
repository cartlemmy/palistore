<?php

return array(
	"coreTable"=>true,
	"name"=>"en-us|Addresses",
	"singleName"=>"en-us|Address",
	"table"=>"db/address",
	"key"=>"id",
	"displayName"=>array("item.full"),
	"nameField"=>"full",
	"fields"=>array(
		"full"=>array(
			"label"=>"en-us|Full Address",
			"searchable"=>true,
			"readOnlyField"=>true
		),
		"name"=>array(
			"label"=>"en-us|Name",
			"searchable"=>true,
			"cleaners"=>"trim"
		),
		"created"=>array(
			"label"=>"en-us|Created",
			"type"=>"date",
			"readOnlyField"=>true
		)
	)
);
