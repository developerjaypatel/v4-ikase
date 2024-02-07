<?php namespace api\iklock;

class iKlockCest {

	use \BasicApiTests;

	public function _before(\ApiTester $I) {
		$I->amUsingEndpoint('iklock/api');
	}

}
