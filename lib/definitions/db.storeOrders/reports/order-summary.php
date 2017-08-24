<?php

if (!class_exists('orderSummaryReport')) {
	class orderSummaryReport {
		private $res;
		private $dir;
		public $app;
				
		public $inputs = array(
			"day"=>array("type"=>"dateRange","label"=>"en-us|Order Range")
		);
		
		public $inputValues = array();
		
		public $outputOptions = array(
			"list"=>array("name"=>"en-us|List")
		);
		
		function __construct($app) {
			$this->app = $app;
			$this->dir = dirname(__FILE__);
			
			foreach ($this->inputs as $n=>$o) {
				$this->inputValues[$n] = null;
			}
		}
		
		public function query() {
			$this->res = $GLOBALS["slCore"]->db->select("db/storeOrders", $this->inputValues["_RAW_QUERY"]." AND (`status`!='' AND `status`!='payment-failed' AND `status`!='cancelled' AND `status`!='refunded' AND `status`!='in-cart')");
		}
		
		public function setInputValues($iv) {

			$range = isset($iv["day"]) ? explode("-",$iv["day"]) : array(0,0);
			$where = array();
			if ($range[0]) $where[] = "`ordered`>=".(int)$range[0];
			if ($range[1]) $where[] = "`ordered`<".ceil($range[1]);

			$this->inputValues["_RAW_QUERY"] = count($where) ? implode(" AND ", $where) : "1";
		}
					
		public function getOutFile($name) {
			return  $this->dir."/out/orders-".safeFile($name).".php";
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

return array("class"=>"orderSummaryReport","name"=>"en-us|Order Summary");
