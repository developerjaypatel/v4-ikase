<?php namespace api\iklock\manage;

class iKlock_Manage_Cards_Cest {

    public function _before(\ApiTester $I) {
        $I->amUsingEndpoint('iklock/manage/cards');
    }

    public function list(\ApiTester $I) {
        $I->seeNumRecords(0, 'cards');
        $I->sendGET('card_list.php');
        $I->seeResponseEquals('');

        $cards = $I->haveMultiple('cards', 3);
        $I->sendGET('card_list.php');
        foreach ($cards as $card) {
            $I->seeResponseContains($card['First Name']);
        }
    }
}
