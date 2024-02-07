<?php namespace api;

class RemindDevApiCest {

	use \BasicApiTests;

	public function _before(\ApiTester $I) {
		$I->amUsingEndpoint('remind/developer/api');
	}

}
