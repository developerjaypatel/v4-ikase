<?php

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class ApiTester extends \Codeception\Actor {

    use _generated\ApiTesterActions {
        sendHEAD as baseHEAD;
        sendOPTIONS as baseOPTIONS;
        sendGET as baseGET;
        sendPOST as basePOST;
        sendPUT as basePUT;
        sendPATCH as basePATCH;
        sendDELETE as baseDELETE;
        sendLINK as baseLINK;
        sendUNLINK as baseUNLINK;
    }

    protected string $basePath = '';

    /**
     * Defines the base path for all API calls, and sets the header <code>Accept: application/json</code>.
     * @param string $basePath
     */
    public function amUsingEndpoint(string $basePath) {
        $this->basePath = trim($basePath, '/').'/'; //makes sure it ends in a single slash, and doesn't start with one
    }

    public function grabEndpoint() { return $this->basePath; }

    public function sendHEAD($url, $params = []) { $this->baseHEAD($this->basePath.$url, $params); }

    public function sendOPTIONS($url, $params = []) { $this->baseOPTIONS($this->basePath.$url, $params); }

    public function sendGET($url, $params = []) { $this->baseGET($this->basePath.$url, $params); }

    public function sendPOST($url, $p = [], $files = []) { $this->basePOST($this->basePath.$url, $p, $files); }

    public function sendPUT($url, $p = [], $files = []) { $this->basePUT($this->basePath.$url, $p, $files); }

    public function sendPATCH($url, $p = [], $files = []) { $this->basePATCH($this->basePath.$url, $p, $files); }

    public function sendDELETE($url, $p = [], $files = []) { $this->baseDELETE($this->basePath.$url, $p, $files); }

    public function sendLINK($url, $linkEntries) { $this->baseLINK($this->basePath.$url, $linkEntries); }

    public function sendUNLINK($url, $linkEntries) { $this->baseUNLINK($this->basePath.$url, $linkEntries); }

    public function authenticateAsTest($role = 'user', $customerId = null):int {
        $customerId = $customerId ?? faker()->unique()->randomNumber;
        $this->sendGET("test/login/$role/$customerId");
        $this->seeResponseContainsJson([
            'user'          => 'test',
            'user_role'     => $role,
            'user_plain_id' => $customerId,
        ]);
        return $customerId;
    }

    /**
     * Wraps 2XX code checking, Content-Type and (optionally) JSON partial presence.
     * @param array|bool $json If an array is given, checks if the response contains that JSON; if true, simply checks
     *                         for a "success" key in the root; falsey does no check.
     * @see  seeResponseCodeIsJson
     * @see  seeResponseContainsJson
     * @see  seeResponseCodeIsSuccessful
     * @todo turn this into something like seeJsonError in case { success: "yey" } is also a standard?
     */
    public function seeResponseIsSuccessfulJson($json = null) {
        $this->seeResponseCodeIsSuccessful();
        $this->seeResponseIsJson();
        if (is_array($json)) {
            $this->seeResponseContainsJson($json);
        } elseif ($json === true) {
            $this->seeResponseJsonMatchesJsonPath('$.success');
        }
    }

    /**
     * Checks for the standard error we use: { error: { text: "whoops" } }
     * @param string $msg
     */
    public function seeJsonErrorMessage(string $msg) {
        $this->seeResponseContainsJson(['error' => ['text' => $msg]]);
    }

    /**
     * A "real" JSON error, complete with HTTP error status.
     * @param string   $msg
     * @param int|null $code If not given, the method checks for a 4XX error.
     * @see seeJsonErrorMessage
     */
    public function seeJsonError(string $msg, int $code = 0) {
        if ($code) {
            $this->seeResponseCodeIs($code);
        } else {
            $this->seeResponseCodeIsClientError();
        }
        $this->seeJsonErrorMessage($msg);
    }

    public function countInResponse(int $total, string $substr) {
        $this->assertEquals($total, substr_count($this->grabResponse(), $substr));
    }
}
