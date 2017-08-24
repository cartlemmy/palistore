<?php

class storeAddressBook {
	private $curI = 1;
	private $i = 1;
	
	public function __construct() {
		while ($this->entryFromRef($this->curI++)) {}
		$this->curI --;
		$this->reset();
	}
	
	public function reset() {
		$this->i = 0;
	}
	
	public function fetch() {
		$this->i ++;
		return $this->getEntry($this->i);
	}
	
	public function getEntry($i) {
		if ($i === "NEW") return $this->newEntry();
		
		if (($entry = &$this->entryFromRef($i)) !== false) {
			return $this->bookEntry($i, $entry);
		}
		return false;
	}
	
	public function newEntry() {
		$i = $this->curI++;
		$n = "_STORE_ADDR_ENTRY_".$i;
		
		$_SESSION[$n] = array();
		return $this->bookEntry($i, $_SESSION[$n]);
	}
	
	private function bookEntry($id, $data) {
		$b = new storeAddressBookEntry($id, $data);
		$b->on("update", array($this, 'entryUpdate'));
		return $b;
	}
	
	public function entryUpdate($entry, $n, $v) {
		$_SESSION["_STORE_ADDR_ENTRY_".$entry->id][$n] = $v;
	}
	
	public function entryFromRef($i) {
		if (isset($_SESSION["_STORE_ADDR_ENTRY_".$i])) return $_SESSION["_STORE_ADDR_ENTRY_".$i];
		return false;
	}
}

class storeAddressBookEntry {
	private $data;
	private $receivers = array();
	public $id = "";

	public function __construct($id, $data) {
		$this->id = $id;
		$this->data = $data;
	}
	
	public function on($event, $cb) {
		$id = $event."-".$this->id."-".spl_object_hash($cb[0]).'-'.$cb[1];
		$this->receivers[$id] = array($event, $cb);
		return $id;
	}
	
	private function emit() {
		$args = func_get_args();
		$event = array_shift($args);
		foreach ($this->receivers as $receiver) {
			if ($receiver[0] == $event) {
				call_user_func_array($receiver[1], $args);
			}
		}
	}
	
	public function get($n, $def = "") {
		if ($n == '*') return $this->data;
		return isset($this->data[$n]) ? $this->data[$n] : $def;
	}
	
	public function getName() {
		return $this->get("name", $this->get("shipAddress1"));		
	}
	
	public function set($n, $v) {
		if (!isset($this->data[$n]) || "".$this->data[$n] !== "".$v) {
			$this->data[$n] = $v;
			$this->emit("update", $this, $n, $v);
		}
	}
}
