<?php 
namespace Api;
use Fig\Http\Message\StatusCodeInterface as Status;

error_reporting(0);
/**
 * Abstract class to group common bootstrap procedures in all APIs.
 */
abstract class Bootstrap {

    /**
     * used to quickly toggle that batshitcrazy code that breaks some parts of so-called "normal" logic but allows
     * other weird parts of the system to work (e.g. logging as another user)
     * @fixme PLEASE FIX THAT AND REMOVE ALL THIS!!!!!!
     */
    private const ENABLE_CRAZY_LEGACY_SESSION_WRITING_LOGIC = false;

    //FIXME: it seems the login functions ALSO write to session files???

    /**
     * @param int  $maxAge
     * @param bool $expireOnInactivity Defines whether the session should expire after $maxAge of inactivity or
     *                                 after $maxAge since creation
     */
    public static function session(int $maxAge = 8 * HOUR, bool $expireOnInactivity = true) {
        $GLOBALS['version'] = date("mdY")."1259"; //FIXME: is this even used?

        $path = ROOT_PATH.'sessions'.DC;
        ini_set('session.gc_maxlifetime', $maxAge);
        session_save_path($path);
        session_name('IKASE_SESSION'); //avoids cookie clash if you have other projects at localhost

        if ($expireOnInactivity) {
            //manually set the session cookie every time the user gets in, so it only expires after $max_age inactivity
            session_start();
            setcookie(session_name(), session_id(), time() + $maxAge, '/', null, false);
        } else {
            session_set_cookie_params($maxAge, '/', null, false);
            session_start();
        }

		if (isset($_GET["session_id"])) { //FIXME: THIS IS COMPLETELY INSECURE!!!!!!
			$current_session_id = $_GET["session_id"];
		} elseif (isset($_SESSION["user"])) {
			$current_session_id = $_SESSION["user"];
		} else {
			$current_session_id = "";
		}

        //FIXME: any reason for REINVENTING sessions? this was NOT persisting further changes!!!
        // this file is also written at: api/login (twice, one with new data), api/relogin, api/masterlogin, and WAS
        // cleaned (but not removed!) at all logout functions
        if (self::ENABLE_CRAZY_LEGACY_SESSION_WRITING_LOGIC) {
            if ($current_session_id != "") {
                $filename = "{$path}data_{$current_session_id}.txt";
                if (!file_exists($filename)) {
                    $fp = fopen($filename, 'w');
                    fwrite($fp, json_encode($_SESSION));
                    fclose($fp);
                }
                $handle     = fopen($filename, "r");
                $contents   = fread($handle, filesize($filename));
                $arrSession = json_decode($contents);

                foreach ($arrSession as $sindex => $session) {
                    $_SESSION[$sindex] = $session;
                }
            }

            if (isset($_GET["old_session_id"])) { //FIXME: THIS IS COMPLETELY INSECURE!!!!!!
                if ($_GET["old_session_id"] != "") {
                    $fp = fopen("{$path}data_{$_GET["old_session_id"]}.txt", 'w');
                    fwrite($fp, json_encode($_SESSION));
                    fclose($fp);
                }
            }
        }

        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = time();
        } elseif (time() - $_SESSION['CREATED'] > $maxAge) {
            session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
            $_SESSION['CREATED'] = time();  // update creation time
        }

        $_SESSION['LAST_ACTIVITY'] = time(); // update last activity timestamp

    }

    /**
     * Returns a singleton of {@link \Slim\App}, correctly configured at the
     * first time it's called.
     * @param string $basePath
     * @param bool   $resetAuthTimeout Configures {@link \Api\Middleware\Authorize}
     *                                 upon first call
     * @return \Slim\App
     */
    public static function slim(string $basePath = '', $resetAuthTimeout = false) {
        static $app;
        if (!$app) {
            Middleware\Authorize::$resetTimeout = $resetAuthTimeout;

            $app = \Slim\Factory\AppFactory::create();
            $app->setBasePath($basePath);
            $app->addErrorMiddleware(ISNT_PROD, false, false)
                ->getDefaultErrorHandler()
                ->registerErrorRenderer('application/json', JsonErrorRenderer::class);
            set_error_handler([self::class, 'errorExceptionizer']);
            //TODO: configure proper logging
            //TODO: configure route caching in production

            $app->getRouteCollector()->setDefaultInvocationStrategy(new LegacyStrategy);
        }
        return $app;
    }

    /**
     * Just throws the thrown error as an exception, to be handled somewhere else.
     * @param int    $code
     * @param string $msg
     * @param string $file
     * @param int    $line
     * @throws \ErrorException
     * @noinspection PhpUnused
     */
    public static function errorExceptionizer(int $code, string $msg, ?string $file, ?int $line) {
        if (!($code & error_reporting())) {
            return false;
        }

        switch ($code) {
            case E_ERROR: //actually this can't be handled by set_error_handler(), but we left here for the sake of it
            case E_USER_ERROR:
            case E_RECOVERABLE_ERROR:
                $severity = 'Error';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $severity = 'Warning';
                break;
            case E_NOTICE:
            case E_USER_NOTICE:
                $severity = 'Notice';
                break;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $severity = 'Deprecated';
                break;
            default:
                $severity = 'Unrecognized error';
        }
        throw new \ErrorException("$severity: $msg", $code, $code, $file, $line);
    }

    /**
     * Imports into Slim API calls that are common throughout all APIs (or most of them).
     * Requires login_encrypt() and login_master() to be defined - to be respectively mapped to /login and /masterlogin.
     * @param bool   $hash
     * @param bool   $citystate
     * @param string $loginTablePrefix
     * @param string $sessionKey
     */
    public static function addCommonCalls(bool $hash, bool $citystate, $loginTablePrefix, $sessionKey = 'user_name') {
        static::testEndpoints();
        Middleware\Authorize::addLoginEndpoints($loginTablePrefix, $sessionKey);

        if ($hash) {
            static::addHash();
        }
        if ($citystate) {
            static::addGetCityState();
        }
    }

    protected static function testEndpoints() {
        if (!ISNT_PROD) {
            return;
        }

        static::slim()->group('/test', function (\Slim\Routing\RouteCollectorProxy $app) {
            $app->get('/hello/{name}', fn($name) => "hello $name"); //sanity check

            //should simulate everything the session should have for an "authentic user" and return it for inspection
            $app->get('/login/{role}[/{customerId}]', function ($role, $customerId = null) {
                //FIXME: why is timeout created at login, but session timestamps not?
                $_SESSION['timeout']            = time();
                $_SESSION['user']               = 'test';
                $_SESSION['user_role']          = $role;
                $_SESSION['user_plain_id']      = (int)$customerId ?? rand(1, 1000);
                $_SESSION['user_customer_id']   = (int)$customerId ?? rand(1, 1000);
                $_SESSION['user_customer_name'] = ucfirst($_SESSION['user']);
                return $_SESSION;
            });

            $app->get('/session', fn() => $_SESSION);

            $app->get('/isAuth', fn() => $GLOBALS['response']->withStatus(Status::STATUS_NO_CONTENT))
                ->add(Middleware\Authorize::class);
        });
    }

    protected static function addHash() {
        static::slim()->get('/hash/{password}', fn($password) => encrypt($password, CRYPT_KEY));
    }

    protected static function addGetCityState() {
        static::slim()->get('/citystate/{zip}', function ($zip) {
            $address_info =
                file_get_contents("http://maps.googleapis.com/maps/api/geocode/json?address=$zip&sensor=true");
            $json         = json_decode($address_info);
            $city         = $state = $country = '';

            if (count($json->results) > 0) {
                $components = $json->results[0]->address_components;

                foreach ($components as $index => $component) {
                    $type = $component->types[0];

                    if (!$city && ($type == "sublocality_level_1" || $type == "locality")) {
                        $city = trim($component->short_name);
                    }
                    if (!$state && $type == "administrative_area_level_1") {
                        $state = trim($component->short_name);
                    }
                    if (!$country && $type == "country") {
                        $country = trim($component->short_name);

                        if ($country != "US") {
                            $city  = '';
                            $state = '';
                            break;
                        }
                    }

                    if ($city != '' && $state != '' && $country != '') {
                        break; //we're done
                    }
                }
            }

            return ['city' => $city, 'state' => $state, 'country' => $country];
        });
    }
}
