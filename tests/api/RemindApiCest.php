<?php namespace api;

class RemindApiCest {

	use \BasicApiTests;

	public function _before(\ApiTester $I) {
		$I->amUsingEndpoint('remind/api');
	}

}
