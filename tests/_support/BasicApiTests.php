<?php

use Codeception\Util\HttpCode;

trait BasicApiTests {

    public function hello(\ApiTester $I) {
        $name = faker()->firstName;
        $I->sendGET("test/hello/$name");
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseEquals("hello $name");
    }

    public function unauthenticated(\ApiTester $I) {
        $I->sendGET('test/isAuth');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    /**
     * @incomplete Won't really work, because that crazy, legacy session code that ended up in {@link \Api\Bootstrap::session()} won't even allow $_SESSION to persist....?????
     * @param \ApiTester $I
     */
    public function insufficientRole(\ApiTester $I) {
        $I->sendGET('test/login/anonymous');
        $I->seeResponseIsSuccessfulJson([
            'user'      => 'test',
            'user_role' => 'anonymous',
        ]);
        $I->sendGET('test/isAuth');
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }

    public function auth(\ApiTester $I) {
        $I->authenticateAsTest();
        $I->sendGET('test/isAuth');
        $I->seeResponseCodeIsSuccessful();
    }

    /** @incomplete doesn't seem to work as it needs an API key */
    public function getCityState(\ApiTester $I) {
        $whiteHouseZIP = 20500;
        $I->sendGET("citystate/$whiteHouseZIP");
        $I->seeResponseIsSuccessfulJson(/* ... */);
    }

    /** @depends auth
     */
    public function logout(\ApiTester $I) {
        $I->authenticateAsTest(); //session values are already verified here
        $I->sendPOST('logout');
        $I->seeResponseIsSuccessfulJson(true);
        $I->sendGET('test/session');
        $I->dontSeeResponseJsonMatchesJsonPath('$.user');
    }

}
