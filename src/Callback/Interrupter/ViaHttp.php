<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\async\Callback\Interrupter;

use zaboy\async\Callback\CallbackException;
use Interop\Container\ContainerInterface;
use Opis\Closure\SerializableClosure;
use zaboy\async\Callback\Interfaces\ServicesInitableInterface;
use zaboy\async\Callback\Interfaces\InterrupterInterface;
use zaboy\async\Promise\Client as PromiseClient;
use zaboy\async\Json\JsonCoder;

/**
 * viaHttp
 *
 *
 * #see http://stackoverflow.com/questions/962915/how-do-i-make-an-asynchronous-get-request-in-php?rq=1
 * @category   async
 * @package    zaboy
 */
class ViaHttp implements InterrupterInterface
{

    const DEFAULT_URL = 'http://zaboy-async.loc/test/callback-interrupter';

    //const DEFAULT_URL = 'http://zaboy-async.loc/api/rest/callback-interrupter';

    public function interrupt($value, PromiseClient $promise, callable $callback)
    {
        $url = static::DEFAULT_URL;
        $promiseId = $promise->getId();
        $arrayData = compact('callback', 'value', 'promiseId');
        $serializedData = serialize($arrayData);
        $data64 = base64_encode($serializedData);
        return $this->async_http_post($url, $data64);
    }

    protected function async_http_post($url, $data64string)
    {

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $run = 'D:\OpenServer\modules\git\bin\curl -X POST -H "Content-Type:text/plain"'; //application/json
            $run.= ' -d "' . $data64string . '" ' . '"' . $url . '"';
            $run.= " > nul 2>&1 &";
        } else {
            $run = 'curl -X POST -H "Content-Type:text/plain"'; //application/json
            $run.= ' -d "' . $data64string . '" ' . '"' . $url . '"';
            $run.= " > /dev/null 2>&1 &";
        }
        exec($run, $output, $exit);
        return $exit == 0;
    }

}
