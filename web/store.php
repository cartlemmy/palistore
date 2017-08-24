<?php

$this->forceHTTPS();

$this->setTitle("en-us|Store");
//$this->set("content-type","sidebar");

$this->set("store",1);

$this->addScript("js/itemOptions.js");
$this->addScript("js/zoomImage.js");

$this->set("zoomImage",true);
$this->setCaching(false);

if (setAndTrue($_SERVER,'HTTP_REFERER')) {
	file_put_contents(SL_DATA_PATH.'/store-referrers',$_SERVER['HTTP_REFERER']."\n", FILE_APPEND);
}
//echo "<!--".md5("OPEN_STORE!")."-->"; exit();
if (isset($_GET["o"]) && $_GET["o"] == md5("OPEN_STORE!") || setAndTrue($_SESSION,"DEV_ACCESS")) $_SESSION["BYPASS_CLOSED"] = 1;

if (!isset($_SESSION["BYPASS_CLOSED"]) && !$GLOBALS["slCore"]->db->select("db/paliSessions",array("endDate"=>array(">",time())),array("limit"=>1))) {
//if (!isset($_SESSION["BYPASS_CLOSED"])) {
	$this->showPageContent("store-closed");
	return;
}

require(SL_WEB_PATH."/inc/store/class.storeItemList.php");

$store = new store();

store::redirectCheck();

$si = new storeItemList();
$si->selectFirstOption = false;

$si->select();

$this->showPageContent("store-welcome");

?><form action="<?=WWW_RELATIVE_BASE;?>store/" method="post">
<button type="submit" name="view-cart" value="1" style="float:right" class="pali-button-small pali-blue"><span class="store-icons cart"></span>en-us| VIEW CART</button>
</form>
<div class="cb"></div>
<?php

$si->showList();

?>
<div class="cb"></div>
<form action="<?=WWW_RELATIVE_BASE;?>store/" method="post">
<button type="submit" name="view-cart" value="1" style="float:right" class="pali-button-small pali-blue"><span class="store-icons cart"></span>en-us| VIEW CART</button>
</form>
