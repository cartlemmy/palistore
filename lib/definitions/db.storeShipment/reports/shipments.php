<?php

if (!class_exists('storeShipmentReport')) {
	class storeShipmentReport {
		private $res;
		private $dir;
		public $app;
		public $orderBy = -1;
		
		public $inputs;
		
		public $inputValues = array();
		
		public $outputOptions = array(
			"list"=>array("name"=>"en-us|List"),
			//"editable-list"=>array("name"=>"en-us|Editable List")
		);
		
		function __construct($app) {
			$this->app = $app;
			$this->dir = dirname(__FILE__);
			
			$this->inputs = array(
				"paliSession"=>array("type"=>"objectDropDown","label"=>"en-us|Session","ref"=>"db/paliSessions","where"=>'item.id == 0 || sl.date("Y",item.startDate)=='.PALI_STORE_YEAR),
				//"status"=>array("type"=>"select","label"=>"en-us|Status","options"=>array(""=>"en-us|Any")),
				/*"status"=>array("type"=>"select","label"=>"en-us|Status","options"=>array(
					""=>"en-us|Any",
					"nonPos"=>"en-us|Not Entered in POS",
					"nonLO"=>"en-us|Not Taken to lower office",
					"nonDelivered"=>"en-us|Not Delivered"
				)),*/
				"shipDate"=>array("type"=>"dateRange","label"=>"en-us|Ship Date")
			);
		
			foreach ($this->inputs as $n=>$o) {
				$this->inputValues[$n] = null;
			}
			
			$info = $GLOBALS["slCore"]->db->getTableInfo("db/storeShipment");
			
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
			
			if ($this->inputValues["paliSession"]) {
				$where[] = "`paliSession`=".(int)$this->inputValues["paliSession"];
			} elseif ($this->inputValues["paliSession"] === '0') {
				$where[] = "`paliSession`=0";
			}
			
			$range = explode("-",$this->inputValues["shipDate"]);
			if (setAndTrue($range,0)) $where[] = "`shipDate`>=".(int)$range[0];
			if (setAndTrue($range,1)) $where[] = "`shipDate`<".ceil($range[1]);
			
			$this->res = $GLOBALS["slCore"]->db->select("db/storeShipment", $where ? implode(" AND ",$where) : "1");
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
			return $this->dir."/out/shipments-".safeFile($name).".php";
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

return array("class"=>"storeShipmentReport","name"=>"en-us|Shipments");
