<?php

class storeTax {
	function getTaxRate($postalCode, $country) {
		return 0.07750;
		/*if ($postalCode == "92382" && $country == "us") {
			return 0.08;
		}
		die("Off site sales tax is not set up yet");*/
	}
}
