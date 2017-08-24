<?php

if (!class_exists('camperNotesReport')) {
	class camperNotesReport {
		private $res;
		private $dir;
		public $app;
		public $orderBy = -1;
		
		public $inputs = array(
			"paliSession"=>array("type"=>"objectDropDown","label"=>"en-us|Session","ref"=>"db/paliSessions"),
			"orderedAfter"=>array("type"=>"date","label"=>"en-us|Ordered After")
		);
		
		public $inputValues = array();
		
		public $outputOptions = array(
			"camper-notes"=>array("name"=>"en-us|Camper Notes")
		);
		
		function __construct($app) {
			$this->app = $app;
			$this->dir = dirname(__FILE__);
			
			foreach ($this->inputs as $n=>$o) {
				$this->inputValues[$n] = null;
			}
			
			$info = $GLOBALS["slCore"]->db->getTableInfo("db/storeOrderItems");
			
		}
		
		public function query() {
			$where = array("(`status`='ordered' OR `status`='prep-ready' OR `status`='deliv-ready' OR `status`='delivering' OR `status`='delivered') AND `notePrinted`=0");

			if ($this->inputValues["paliSession"]) $where[] = "`paliSession`=".(int)$this->inputValues["paliSession"];
			if ($this->inputValues["orderedAfter"]) $where[] = "`ordered`>=".(int)$this->inputValues["orderedAfter"];
			
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

return array("class"=>"camperNotesReport","name"=>"en-us|Camper Notes");
