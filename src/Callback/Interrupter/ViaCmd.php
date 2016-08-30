<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 25.08.16
 * Time: 16:17
 */

namespace zaboy\async\Callback\Interrupter;

use zaboy\async\Callback\CallbackException;
use zaboy\async\Callback\Interfaces\InterrupterInterface;
use zaboy\async\Promise\Client as PromiseClient;

/** todo: temp dirs */
class ViaCmd implements InterrupterInterface
{
    const DEFAULT_PATH = 'scripts/viaCmd.php';

    public function interrupt($value, PromiseClient $promise, callable $callback)
    {
        $path = static::DEFAULT_PATH;
        $promiseId = $promise->getId();
        $arrayData = compact('callback', 'value', 'promiseId');
        $serializedData = serialize($arrayData);
        $data64 = base64_encode($serializedData);
        return $this->asyncCmdCall($path, $data64);
    }

    protected function asyncCmdCall($script, $data64string)
    {
        if(!is_file($script)){
            throw new CallbackException("The handler script \"". $script ."\" does not exist in the folder \"script\"");
        }

        $stdOutFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('stdout_', 1);
        $stdErrFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('stderr_', 1);

        $cmd = "php " . $script . " " . $data64string . "  1>{$stdOutFilename} 2>{$stdErrFilename} & echo $!";

        $output = trim(shell_exec($cmd));
        return $output;
    }
}
