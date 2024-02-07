<?php namespace Api;

/**
 * Renders a complete stacktrace of the given error as JSON.
 */
class JsonErrorRenderer implements \Slim\Interfaces\ErrorRendererInterface {

    private function path($path, $line = null) {
        return str_after($path, ROOT_PATH).($line? ":$line" : '');
    }

    private function argument($arg) {
        if (is_object($arg)) {
            return get_class($arg);
        } elseif (is_bool($arg)) {
            return $arg? 'true' : 'false';
        } elseif (is_array($arg)) {
            return array_combine(array_keys($arg), array_map([$this, 'argument'], $arg));
        } else {
            return $arg;
        }
    }

    /**
     * Drop-in for lame <code>echo json_response(['error' => ['text' => 'blabla']]);</code>
     * @param string|\Throwable $error
     */
    public static function simple($error) {
        header('Content-Type: application/json');
        $error = $error instanceof \Throwable? $error : new \RuntimeException($error);
        die((new self)($error, ISNT_PROD));
    }

    public function __invoke(\Throwable $exception, bool $displayErrorDetails):string {
        $handle = function (\Throwable $exception) use ($displayErrorDetails, &$handle) {
            $error = ['error' => ['text' => $exception->getMessage()]];

            if ($displayErrorDetails) {
                $error['code']     = $exception->getCode();
                $error['type']     = get_class($exception);
                $error['location'] = $this->path($exception->getFile(), $exception->getLine());

                $error['trace'] = array_values(array_filter(array_map(function ($call) {
                    /**
                     * @var string|null $class
                     * @var string|null $type
                     * @var string      $function
                     * @var string|null $file
                     * @var int|null    $line
                     * @var array       $args
                     */
                    extract($call);

                    if (isset($class)) {
                        if ($class == self::class && $function == 'errorHandler') {
                            return null;
                        }

                        if (strpos($class, 'class@anonymous') !== false) {
                            $class = 'Anonymous Class: '.$this->path(str_replace("class@anonymous\0", '', $class));
                        }
                    }

                    return [
                        'location' => $this->path($file ?? '', $line ?? ''),
                        'call'     => strpos($function, '{closure}')? '« closure »' :
                            ($class ?? '').($type ?? '').$function.'()',
                        'args'     => $this->argument($args),
                    ];
                }, $exception->getTrace())));
            }

            if ($exception->getPrevious()) {
                $error['previous'] = $handle($exception);
            }

            return $error;
        };

        return json_encode($handle($exception));
    }
}
