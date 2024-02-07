<?php namespace api;

//defines the "cryptographic key"
require_once(dirname(dirname(__DIR__)).'/api/chain_pack.php');

class MainApiCest {

    use \BasicApiTests {
        getCityState as private; //effectively disables running this test
    }

    function _before(\ApiTester $I) {
        $I->amUsingEndpoint('api');
    }

    function checkZip(\ApiTester $I) {
        //the simple use of FLOAT in the database creates inconsistent decimal places, so we do a pessimistic round down
        $lat      = round(faker()->latitude, 3);
        $lon      = round(faker()->longitude, 3);
        $zip_code = faker()->postcode;
        $I->haveInDatabase('zip_code', compact('lat', 'lon', 'zip_code'));

        $I->authenticateAsTest();
        $I->sendGET('checkzip/'.substr($zip_code, 0, 2));
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseEquals('');

        //FIXME: broken because of session shenanigans around Bootstrap::session():44
//        $I->sendGET('checkzip/'.substr($zip_code, 0, 3));
//        $I->seeResponseIsSuccessfulJson(['lattitude' => $lat, 'longitude' => $lon]);
    }

    /**
     * @incomplete Won't really work, because that crazy, legacy session code that ended up in {@link \Api\Bootstrap::session()} won't even allow $_SESSION to persist....?????
     * @param \ApiTester $I
     */
    function levels(\ApiTester $I) {
        $I->authenticateAsTest('user');
        $I->sendGET('levels');
        $I->seeResponseIsSuccessfulJson(['level' => 0]);

        $I->authenticateAsTest('admin');
        $I->sendGET('levels');
        $I->seeResponseIsSuccessfulJson(['level' => 1]);

        $I->authenticateAsTest('masteradmin');
        $I->sendGET('levels');
        $I->seeResponseIsSuccessfulJson(['level' => 1]);
    }

    //TODO: move specific "pack" tests to separated test files

    /**
     * this test is to check basic PHPDocx support is working
     * @incomplete Won't really work, because that crazy, legacy session code that ended up in {@link \Api\Bootstrap::session()} won't even allow $_SESSION to persist....?????
     * @param \ApiTester $I
     * @throws \Exception
     */
    function createLetterEnvelope(\ApiTester $I) {
        $customer = $I->haveInDatabase('cse_customer', [
            'letter_name'   => 'letter',
            'customer_uuid' => faker()->randomNumber,
            'cus_street'    => faker()->streetAddress,
            'cus_city'      => faker()->city,
            'cus_state'     => faker()->state,
            'cus_zip'       => faker()->postcode,
        ]);

        $I->authenticateAsTest('user', $customer);
        //TODO: still missing some entries to return a proper envelope, but at least we can guarantee PHPDocx is working
        $I->sendPOST('letter/envelope', ['corporation_id' => 123]);
        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();

        $file = $I->grabDataFromResponseByJsonPath('$.file')[0];
        $I->sendGET($file);
        $I->assertGreaterThan(0, $I->grabHttpHeader('Content-Length'));
        $I->assertStringContainsStringIgnoringCase('word', $I->grabHttpHeader('Content-Type'));
    }

    /**
     * @incomplete Won't really work, because that crazy, legacy session code that ended up in {@link \Api\Bootstrap::session()} won't even allow $_SESSION to persist....?????
     * @param \ApiTester $I
     */
    function getLetter(\ApiTester $I) {
        $userId   = $I->authenticateAsTest();
        $letterId = $I->haveInDatabase('cse_letter', ['customer_id' => $userId]);
        $I->sendGET("letter/$letterId");
        $I->seeResponseIsSuccessfulJson(['id' => $letterId]);
    }

    //could be used in the future as initial testbed for MySQL replacement
    function resetPassword(\ApiTester $I) {
        $oldPwd = faker()->unique()->password;
        $newPwd = faker()->unique()->password;

        $user = [
            'user_email' => faker()->companyEmail,
            'user_name'  => faker()->userName,
            'user_uuid'  => faker()->randomNumber,
            'pwd'        => encrypt($oldPwd, CRYPT_KEY),
        ];
        $id   = $I->haveInDatabase('cse_user', $user);

        $I->sendPOST('request/act', ['id' => $id, 'password' => $newPwd]);
        $I->seeResponseIsSuccessfulJson(['success' => 'password updated']);
        $I->canSeeInDatabase('cse_user', ['user_name' => $user['user_name'], 'pwd' => encrypt($newPwd, CRYPT_KEY)]);
    }

    /**
     * @incomplete DOESN'T EVEN WORK! The main SQL is missing a field with no default value :|
     * @param \ApiTester $I
     */
    function EAMSCarriersUpdateData(\ApiTester $I) {
        $I->truncateInDatabase('cse_eams_carriers'); //make sure we always get the INSERT version
        $I->seeNumRecords(0, 'cse_eams_carriers');
        $I->sendGET('carriers_update_data.php');
        $I->assertGreaterThan(0, $I->countInDatabase('cse_eams_carriers'));
    }

    /**
     * @incomplete DOESN'T EVEN WORK! The main SQL is missing a field with no default value :|
     * @param \ApiTester $I
     */
    function EAMSRepsUpdateData(\ApiTester $I) {
        $I->truncateInDatabase('cse_eams_reps'); //make sure we always get the INSERT version
        $I->seeNumRecords(0, 'cse_eams_reps');
        $I->sendGET('reps_update_data.php');
        $I->assertGreaterThan(0, $I->countInDatabase('cse_eams_reps'));
    }

    function specialty(\ApiTester $I) {
        $specialty = $I->have('cse_medical_specialties');
        $I->sendGET("specialty/{$specialty['specialty_id']}");
        $I->seeResponseIsSuccessfulJson($specialty);
    }

    function specialties(\ApiTester $I) {
        $specialties = $I->haveMultiple('cse_medical_specialties', 3);
        $specialties = array_map(fn (\Module\PDOMuffin $s) => $s->getAttributes(), $specialties);
        $I->sendGET('specialties');
        $I->seeResponseIsSuccessfulJson($specialties);
    }
}
