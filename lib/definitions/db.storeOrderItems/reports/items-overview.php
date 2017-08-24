<?php

if (!class_exists('orderItemsOverview')) {
	class orderItemsOverview {
		private $res;
		private $dir;
		public $app;
		
		public $inputs = array(
			"storeOrders.ordered"=>array("type"=>"dateRange","label"=>"en-us|Order Date Range"),
			"storeOrderItems.shipDate"=>array("type"=>"dateRange","label"=>"en-us|Ship Date Range")
		);
		
		public $inputValues = array();
		
		public $outputOptions = array(
			"list"=>array("name"=>"en-us|List")
		);
		
		public function getQueryAsText() {
			$t = array();
			foreach ($this->inputs as $n=>$o) {
				if ($this->inputValues[$n]) $t[] = translate($o["label"])." is ".valueToString($this->inputValues[$n],$o["type"]);
			}
			return $t ? implode(", and ", $t) : "ALL";
		}
		
		function __construct($app) {
			$this->app = $app;
			$this->dir = dirname(__FILE__);
			
			foreach ($this->inputs as $n=>$o) {
				$this->inputValues[$n] = null;
			}
		}
		
		public function query() {
			$where = array("(`storeOrderItems`.`status`='ordered' OR `storeOrderItems`.`status`='prep-ready' OR `storeOrderItems`.`status`='deliv-ready' OR `storeOrderItems`.`status`='delivering' OR `storeOrderItems`.`status`='delivered')");
			
			$range = explode("-",$this->inputValues["storeOrderItems.shipDate"]);
			if (setAndTrue($range,0)) $where[] = "`storeOrderItems`.`shipDate`>=".(int)$range[0];
			if (setAndTrue($range,1)) $where[] = "`storeOrderItems`.`shipDate`<".ceil($range[1]);
			
			$range = explode("-",$this->inputValues["storeOrders.ordered"]);
			if (setAndTrue($range,0)) $where[] = "`storeOrders`.`ordered`>=".(int)$range[0];
			if (setAndTrue($range,1)) $where[] = "`storeOrders`.`ordered`<".ceil($range[1]);
			
			$query = "SELECT `storeOrderItems`.* FROM `storeOrders`, `storeOrderItems` WHERE ".($where ? implode(" AND ",$where) : "1")." AND `storeOrderItems`.`orderId`=`storeOrders`.`id`";
			//echo $query; exit();
			$this->res = $GLOBALS["slCore"]->db->query("db/storeOrderItems", $query);
		}

		public function setInputValues($iv) {
			foreach ($this->inputs as $n=>$o) {
				if (isset($iv[$n])) $this->inputValues[$n] = $iv[$n];
			}			
		}
		
		public function getOutFile($name) {
			return  $this->dir."/out/items-overview-".safeFile($name).".php";
		}
		
		public function generate($name,$mimeType) {
			$file = $this->getOutFile($name);
			
			require_once(SL_WEB_PATH."/inc/store/class.store.php");
			
			$cfg = store::getConfig();
			if (is_file($file)) {
				return array("success"=>true,"data"=>require($file));
			}
			return array("success"=>false,"error"=>"'$file' not found.");
		}
	}
}

return array("class"=>"orderItemsOverview","name"=>"en-us|Order Items Overview");
