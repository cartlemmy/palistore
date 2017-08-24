<?php

$this->setCaching(false);

echo json_encode(!!$GLOBALS["slCore"]->db->select("db/paliSessions",array("endDate"=>array(">",time())),array("limit"=>1)));
exit();
