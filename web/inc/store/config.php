<?php

return array(
	"storeName"=>"Pali Adventures",
	"db"=>null,
	"fromEmail"=>array(
		"email"=>"store@palimountain.com",
		"name"=>"Pali Adventures Store"
	),
	"table"=>array(
		"item"=>"db/storeItems",
		"category"=>"db/storeCategories",
		"cart"=>"db/storeOrderItems",
		"order"=>"db/storeOrders",
		"order-history"=>"db/storeOrderHistory",
		"order-item-history"=>"db/storeOrderItemHistory",
		"shipment"=>"db/storeShipment"
	),
	"shippingTable"=>dirname(__FILE__).'/shipping-table',
	"disableOffsiteDelivery"=>true,
	"user"=>"super",
	"cartItemLiveSeconds"=>3600, // Number of seconds items stay in the cart
	"allowedImageSize"=>array("160w","194w","320w","1024w"),
	"inventoryControl"=>false,
	"ssValidateFile"=>SL_WEB_PATH."/inc/storeSSValidate.php",
	"links"=>array(
		"view-store"=>"store/",
		"view-cart"=>"store-cart/",
		"check-out"=>"store-check-out/"
	),
	"defaults"=>array(
		"storeOrderItems"=>array(
			"deliveryType"=>"on-site",
			"shipAddress1"=>"30778 Highway 18",
			"shipCity"=>"Running Springs",
			"shipState"=>"ca",
			"shipPostalCode"=>"92382",
			"shipCountry"=>"us"
		)
	),
	"authorizeNet"=>array(
		"APILoginID"=>"3nFVLqW36c4h",
		"transactionKey"=>"9V7t83q3ewU7D93q",
		"secretQuestion"=>"Stevens",
		"md5"=>"HQLZX-OFMJF-WRMRF"
	),
	"testMode"=>"off",
	"orderNumberPrefix"=>"PA",
	"subOptions"=>array(
		"optSize"=>"en-us|Size",
		"optStyle"=>"en-us|Style"
	),
	"noTax"=>false,
	"addonThreshold"=>0	
);
