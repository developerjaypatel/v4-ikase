<?php namespace Api\Middleware;
use Api\Bootstrap;
use Slim\Psr7;
use Psr\Http\Server\RequestHandlerInterface;
use Fig\Http\Message\StatusCodeInterface as Status;

class Authorize {

	static bool $resetTimeout = false;
	protected static string $loginTablePrefix;
	protected static string $sessionKey = 'user_name';

	function __invoke(Psr7\Request $req, RequestHandlerInterface $handler) {
		if (isset($_SESSION['timeout']) && ($_SESSION['timeout'] + 8 * HOUR) < time()) {
			static::logout(); //FIXME: merge the logout functions
		} else {
			if (static::$resetTimeout) {
				$_SESSION['timeout'] = time();
			}

			// If a user is not logged in at all, return a 401
			if (empty($_SESSION['user'])) {
				return $this->errorResponse(Status::STATUS_UNAUTHORIZED, 'Not logged in... sign in for me, will you?');
			}

			// Validate the role to make sure they can access the route - if not, 403
			// We will assume admin role can access everything
			if (!in_array($_SESSION['user_role'], ['user', 'admin', 'masteradmin', 'owner'])) {
				return $this->errorResponse(Status::STATUS_FORBIDDEN, 'You shall not pass!');
			}
		}

		return $handler->handle($req);
	}

	protected function errorResponse(int $status, string $body) {
		$res = new Psr7\Response;
		$res->getBody()->write($body);
		return $res->withStatus($status);
	}

	protected static function logout() {
		$prefix = static::$loginTablePrefix;
		$sessionUser = $_SESSION[static::$sessionKey] ?? '';

		if ($sessionUser) {
			try {
				$userField = $prefix? 'user_name' : 'username'; //standardized code...
				$stmt = getConnection()->prepare("INSERT INTO {$prefix}userlogin
					    (`$userField`, `user_uuid`, `status`, `ip_address`, `dateandtime`, `login_date`, `customer_id`)
						VALUES (:username, :id, 'OUT', :ip, '".date("Y-m-d H:i")."','".date("Y-m-d")."', :customer)");
				$stmt->bindParam('username', $sessionUser);
				$stmt->bindParam('id', $_SESSION['user_id']);
				$stmt->bindParam('ip', $_SERVER['REMOTE_ADDR']);
				$stmt->bindParam('customer', $_SESSION['user_customer_id']);
				$stmt->execute();
			} catch(\PDOException $e) {
				return ['error' => ['text' => $e->getMessage()]];
			}
		}

		// generate new session id and delete old session in store
		$_SESSION = [];
		session_regenerate_id(true); //there was a silent try/catch here, why? this is not supposed to throw exceptions

		return ['success' => ['text' => 'You are logged out...']];

	}

	public static function addLoginEndpoints($loginTablePrefix, $sessionKey = null) {
		static::$loginTablePrefix = $loginTablePrefix;
		static::$sessionKey = $sessionKey ?? static::$sessionKey;

		//FIXME: DRY these functions and move them here!
		Bootstrap::slim()->post('/login', 'login_encrypt');
		Bootstrap::slim()->post('/masterlogin', 'login_master')->add(self::class);
		Bootstrap::slim()->post('/logout', fn() => static::logout());
	}
}
