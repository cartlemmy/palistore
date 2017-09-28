<?php

return array(
	"name"=>"en-us|Order History",
	"singleName"=>"en-us|Order Event",
	"table"=>"db/storeOrderHistory",
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
				""=>"en-us|New",
				"ordered"=>"en-us|Ordered",
				"prep-ready"=>"en-us|Ready for Preparation",
				"deliv-ready"=>"en-us|Ready for Delivery",
				"ready"=>"en-us|Ready",
				"multi"=>"en-us|Varying Item Statuses",
				"delivering"=>"en-us|Delivery in Progress",
				"delivered"=>"en-us|Delivered",
				"payment-failed"=>"en-us|Payment Failed",
				"cancelled"=>"en-us|Cancelled",
				"refunded"=>"en-us|Refunded"
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
