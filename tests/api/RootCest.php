<?php namespace api;

/**
 * "Non-API" API calls.
 * Although those calls are not inside a Slim application, they still accomplish quite small tasks and spit out JSON,
 * so... it seemed better to treat them as API instead of unit tests?
 */
class RootCest {

	function _before(\ApiTester $I) {
		$I->amUsingEndpoint('');
	}

	function demos(\ApiTester $I) {
        $newDownload = function($startDate, $endDate) {
            return [
                'downloadkey' => faker()->unique()->numerify('####'),
                'injury_id'   => faker()->numerify('####'), //TODO: should be an actual FK
                'sent_by'     => faker()->name,
                'file'        => faker()->numerify('####/####'),
                'expires'     => faker()->dateTimeBetween($startDate, $endDate)->format('Y-m-d H:i:s'),
            ];
        };

        $valid = $newDownload('tomorrow', 'next year');
        $path = UPLOADS_PATH.$valid['file'].DC;
        $payload = 'success';
        mkdir($path, 0777, true);
        $I->writeToFile("{$path}demographics.html", $payload);

        $id = $I->haveInDatabase('cse_downloads', $valid);
        $I->sendGET("demos.php?key={$valid['downloadkey']}");
        $I->seeResponseEquals($payload);
        $I->seeInDatabase('cse_downloads', ['downloads_id' => $id, 'downloads' => 1]);

        $invalid = $newDownload('last year', 'yesterday');
        $I->haveInDatabase('cse_downloads', $invalid);
        $I->sendGET("demos.php?key={$invalid['downloadkey']}");
        $I->seeJsonErrorMessage('This download has expired.');
    }
}
