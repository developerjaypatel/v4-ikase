<?php namespace Api;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Reimplements the following:
 * - path arguments as the first function parameters as Slim 1(?)
 * - possibilities for returning a string from endpoint methods at Slim v3
 * Request and Response objects are accessible from the globals $request and $response
 * Also adds ability to turn a returned array into a JSON payload.
 * @see RequestResponse
 * @see RequestResponseArgs
 */
class LegacyStrategy implements \Slim\Interfaces\InvocationStrategyInterface {

	public function __invoke(
		callable $callable,
		ServerRequestInterface $request,
		ResponseInterface $response,
		array $routeArguments
	): ResponseInterface {
		foreach ($routeArguments as $k => $v) {
			$request = $request->withAttribute($k, $v);
		}

		$GLOBALS['request'] = $request;
		$GLOBALS['response'] = $response;
		$result = $callable(...array_values(array_merge($routeArguments)));

		if ($result instanceof ResponseInterface) {
			return $result;
		} else {
			if (is_string($result)) {
				$response->getBody()->write($result);
			} elseif (is_array($result)) {
				$response->getBody()->write(json_encode($result));
			}
			return $response;
		}
	}

}
