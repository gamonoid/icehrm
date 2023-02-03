<?php

namespace Utils;

class ScriptRunner
{
    public static function executeJs($parameters, $script)
    {
        LogManager::getInstance()->debug(
            sprintf(
                'ScriptRunner: parameters:%s / script: %s',
                json_encode($parameters),
                $script
            )
        );

        $command = sprintf(
            'node %sexecute/execute.js %s %s',
            APP_BASE_PATH,
            base64_encode(json_encode($parameters)),
            base64_encode($script)
        );

        LogManager::getInstance()->debug(
            sprintf(
                'ScriptRunner: command :%s',
                $command
            )
        );

        $result =  exec($command);

        LogManager::getInstance()->debug(
            sprintf(
                'ScriptRunner: result :%s',
                $result
            )
        );

        return $result;
    }
}
