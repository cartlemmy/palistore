<?php

//Run:every hour

return;

require(SL_WEB_PATH."/inc/store/class.store.php");
require(SL_WEB_PATH."/inc/store/class.storeOrder.php");

$store = new store();

storeOrder::updateOrders();
