<?php

return array(
	"name"=>"en-us|Order Item History",
	"singleName"=>"en-us|Item Event",
	"table"=>"db/storeOrderItemHistory",
	"key"=>"id",
	"displayName"=>array("item.ts+' #'+item.orderId"),
	"nameField"=>"id",
	"orderby"=>"ts",
	"orderdir"=>"desc",
	"disableDelete"=>true,
	"fields"=>array(
		"ts"=>array(
			"label"=>"en-us|Timestamp",
			"type"=>"date",
			"readOnlyField"=>true
		),
		"status"=>array(
			"label"=>"en-us|Status",
			"type"=>"select",
			"default"=>"in-cart",
			"options"=>array(
				"in-cart"=>"en-us|In Cart",
				"ordered"=>"en-us|Ordered",
				"prep-ready"=>"en-us|Ready for Preparation",
				"deliv-ready"=>"en-us|Ready for Delivery",
				"delivering"=>"en-us|Delivery in Progress",
				"delivered"=>"en-us|Delivered",
				"cancelled"=>"en-us|Cancelled"
			)
		),
		"extra"=>array(
			"label"=>"en-us|Extra",
			"type"=>"extra",
			"readOnlyField"=>true,
			"exclude"=>array("session","fullResponse","created","itemCount","statusUpdateTS","userId","isParent")
		),
	)
);
