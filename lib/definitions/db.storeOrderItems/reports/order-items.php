<?php

if (!class_exists('orderItemsReport')) {
	class orderItemsReport {
		private $res;
		private $dir;
		public $app;
		public $orderBy = -1;
		
		public $inputs = array(
			"paliSession"=>array("type"=>"objectDropDown","label"=>"en-us|Session","ref"=>"db/paliSessions"),
			//"status"=>array("type"=>"select","label"=>"en-us|Status","options"=>array(""=>"en-us|Any")),
			"status"=>array("type"=>"select","label"=>"en-us|Status","options"=>array(
				""=>"en-us|Any",
				"nonPos"=>"en-us|Not Entered in POS",
				"nonLO"=>"en-us|Not Taken to lower office",
				"nonDelivered"=>"en-us|Not Delivered"
			)),
			"ordered"=>array("type"=>"dateRange","label"=>"en-us|Ordered Date/Time")
		);
		
		public $inputValues = array();
		
		public $outputOptions = array(
			"list"=>array("name"=>"en-us|List"),
			"editable-list"=>array("name"=>"en-us|Editable List")
		);
		
		function __construct($app) {
			$this->app = $app;
			$this->dir = dirname(__FILE__);
			
			foreach ($this->inputs as $n=>$o) {
				$this->inputValues[$n] = null;
			}
			
			$info = $GLOBALS["slCore"]->db->getTableInfo("db/storeOrderItems");
			
			/*$this->inputs["status"]["options"] = array_merge(
				$this->inputs["status"]["options"],
				$info["fields"]["status"]["options"]
			);*/
		}
		
		public function query() {
			$acceptableStatus = "(`status`='ordered' OR `status`='prep-ready' OR `status`='deliv-ready' OR `status`='delivering' OR `status`='delivered')";
			if ($this->inputValues["status"]) {
				//$where = array("`status`=".slMysql::safe($this->inputValues["status"]));
				switch ($this->inputValues["status"]) {
					case "nonPos":
						$where = array($acceptableStatus,"`posEntered`=0"); break;
						
					case "nonLO":
						$where = array($acceptableStatus,"`lowerOffice`=0"); break;
					
					case "nonDelivered":
						$where = array($acceptableStatus,"`status`!='delivered'"); break;
				}
			} else {
				$where = array($acceptableStatus);
			}
			if ($this->inputValues["paliSession"]) $where[] = "`paliSession`=".(int)$this->inputValues["paliSession"];
			
			//if ($this->inputValues["orderedAfter"]) $where[] = "`ordered`>=".(int)$this->inputValues["orderedAfter"];
			$range = explode("-",$this->inputValues["ordered"]);
			if (setAndTrue($range,0)) $where[] = "`ordered`>=".(int)$range[0];
			if (setAndTrue($range,1)) $where[] = "`ordered`<".ceil($range[1]);
				
			$this->res = $GLOBALS["slCore"]->db->select("db/storeOrderItems", $where ? implode(" AND ",$where) : "1");
		}
		
		public function setInputValues($iv) {
			foreach ($this->inputs as $n=>$o) {
				if (isset($iv[$n])) $this->inputValues[$n] = $iv[$n];
			}			
		}
		
		public function setOrderBy($orderBy) {
			$this->orderBy = $orderBy;
		}
		
		public function getOutFile($name) {
			return $this->dir."/out/order-items-".safeFile($name).".php";
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

return array("class"=>"orderItemsReport","name"=>"en-us|Order Items by Session");
