<?php

namespace Utils;

class ScriptRunner
{
    /**
     * Check if a string is valid base64 encoded
     */
    private static function isBase64Encoded($value)
    {
        if (empty($value)) {
            return false;
        }
        $decoded = base64_decode($value, true);
        return $decoded !== false && base64_encode($decoded) === $value;
    }

    public static function executeJs($parameters, $script)
    {
        LogManager::getInstance()->debug(
            sprintf(
                'ScriptRunner: parameters:%s / script: %s',
                json_encode($parameters),
                $script
            )
        );

        // Only base64 encode the script if it's not already encoded
        $encodedScript = self::isBase64Encoded($script) ? $script : base64_encode($script);

        $command = sprintf(
            'node %sexecute/execute.js %s %s',
            APP_BASE_PATH,
            base64_encode(json_encode($parameters)),
            $encodedScript
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
