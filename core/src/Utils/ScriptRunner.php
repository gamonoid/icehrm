<?php

namespace Utils;

class ScriptRunner
{
    public static function executeJs($parameters, $script)
    {
        return exec(sprintf(
            'node %sexecute/execute.js %s %s',
            APP_BASE_PATH,
            base64_encode(json_encode($parameters)),
            base64_encode($script)
        ));
    }
}
