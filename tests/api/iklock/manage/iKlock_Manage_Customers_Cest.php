<?php namespace api\iklock\manage;

class iKlock_Manage_Customers_Cest {

    public function _before(\ApiTester $I) {
        $I->amUsingEndpoint('iklock/manage/customers');
    }

    public function checkZip(\ApiTester $I) {
        $zip = [
            'zip_code'     => faker()->numerify('#####'),
            'city'         => faker()->city,
            'county'       => faker()->citySuffix,
            'state_name'   => faker()->state,
            'state_prefix' => faker()->stateAbbr,
            'area_code'    => faker()->numerify('##'),
            'time_zone'    => faker()->timezone,
            'lat'          => faker()->latitude,
            'lon'          => faker()->longitude,
        ];
        $I->haveInDatabase('zip_code', $zip);
        $I->sendGET('check_zip.php', ['query' => $zip['zip_code']]);
        $I->seeResponseEquals("{$zip['city']}|{$zip['state_prefix']}|{$zip['county']}");
    }

    public function attorneyList(\ApiTester $I) {
        $I->wantTo('check empty list');
        $I->truncateInDatabase('cse_attorney');
        $I->seeNumRecords(0, 'cse_attorney');
        $I->sendGET('attorney_list.php');
        $I->seeResponseEquals('');

        $I->wantTo('check list with entries');
        $users = $I->haveMultiple('cse_user', 2);
        $attorneys = $I->haveMultiple('cse_attorney', 3, [
            'deleted' => 'N',
            'user_id' => $users[0]->getKey()
        ]);

        //adding two entries that shouldn't be present in the final list
        $I->have('cse_attorney', ['user_id' => $users[1]->getKey()]);
        $I->have('cse_attorney', ['deleted' => 'Y']);
        $I->seeNumRecords(5, 'cse_attorney'); //sanity check

        $I->sendGET('attorney_list.php', ['cus_id' => $users[0]->customer_id]);
        $resp = $I->grabResponse();
        $entries = explode("\n", $resp);
        $I->assertEquals(sizeof($attorneys), sizeof($entries));
        foreach ($attorneys as $attorney) {
            $I->seeResponseContains($attorney->first_name);
        }

        $selected = faker()->randomElement($attorneys);
        $I->sendGET('attorney_list.php', ['cus_id' => $selected->customer_id, 'attorney_id' => $selected->attorney_id]);
        $I->seeResponseContains($selected->first_name);
        $I->dontSeeResponseContains("\n"); //single entry
    }

    //tested with runOrDie() and with updateOrDie()
    public function delete(\ApiTester $I) {
        $customer = $I->have('cse_customer', ['deleted' => 'N']);

        $I->sendPOST("customer_delete.php", ['cus_id' => -1]);
        $I->seeResponseContains('wrong');
        $I->sendGET("customer_delete.php", ['cus_id' => $customer->getKey()]);
        $I->seeResponseContains('wrong');

        $I->sendPOST("customer_delete.php", ['cus_id' => $customer->getKey()]);
        $I->seeResponseContains('deleted');
        $I->seeInDatabase('cse_customer', $customer->getKeyPair() + ['deleted' => 'Y']);
    }

    public function search(\ApiTester $I) {
        $I->wantTo('check security redirects');
        $I->stopFollowingRedirects();
        $I->sendGET('customer_search.php');
        $I->seeResponseCodeIsRedirection();

        $I->amUsingEndpoint('iklock/api');
        $I->authenticateAsTest('owner');
        $I->amUsingEndpoint('iklock/manage/customers');
        $customers = $I->haveMultiple('cse_customer', 2, ['deleted' => 'N']);

        $I->wantTo('search for a single customer');
        $name    = $customers[0]->cus_name;
        $length  = strlen($name);
        $keyword = substr($name, faker()->numberBetween(0, $length / 2), faker()->numberBetween($length / 2, $length));
        $I->sendGET('customer_search.php', compact('keyword'));
        $I->seeResponseContains(ucfirst(strtolower($customers[0]->cus_name_first)));
        $I->seeResponseContains(ucfirst(strtolower($customers[0]->cus_name_middle)));
        $I->seeResponseContains(ucfirst(strtolower($customers[0]->cus_name_last)));
        $I->countInResponse(1, '<tr>'); //only one line returned

        $I->wantTo('search for a single deleted customer');
        $I->updateInDatabase('cse_customer', ['deleted' => 'Y'], $customers[0]->getKeyPair());
        $I->sendGET('customer_search.php', compact('keyword'));
        $I->countInResponse(0, '<tr>');

        $I->wantTo('search for more than one customer');
        $first_name = faker()->firstName;
        $siblings  = $I->haveMultiple('cse_customer', 2, ['deleted' => 'N', 'cus_name_first' => $first_name]);
        $I->sendGET('customer_search.php', ['keyword' => $first_name]);
        $I->countInResponse(2, '<tr>'); //FIXME: the problem is probably here
        $I->seeResponseContains(ucfirst(strtolower($siblings[0]->cus_name_first)));
        $I->seeResponseContains(ucfirst(strtolower($siblings[1]->cus_name_first)));
    }
}
