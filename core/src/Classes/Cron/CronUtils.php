<?php
namespace Classes\Cron;

class CronUtils
{
    public $clientBasePath;
    public $cronFile;

    private static $me = null;

    private function __construct($clientBasePath, $cronFile)
    {
        $this->clientBasePath = $clientBasePath."/";
        $this->cronFile = $cronFile;
    }

    public static function getInstance($clientBasePath, $cronFile)
    {
        if (empty(self::$me)) {
            self::$me = new CronUtils($clientBasePath, $cronFile);
        }
        return self::$me;
    }

    public function run()
    {
        $ams = scandir($this->clientBasePath);
        $count = 0;
        foreach ($ams as $am) {
            if (is_dir($this->clientBasePath.$am) && $am != '.' && $am != '..') {
                $command =  "php ".$this->clientBasePath.$am."/".$this->cronFile;
                if (file_exists($this->clientBasePath.$am."/".$this->cronFile)) {
                    echo "Run:".$command."\r\n";
                    error_log("Run:".$command);
                    passthru($command, $res);
                    echo "Result :".$res."\r\n";
                    error_log("Result :".$res);

                    $count++;
                    if ($count > 25) {
                        sleep(1);
                        $count = 0;
                    }
                } else {
                    echo "Error (File Not Found):".$command."\r\n";
                    error_log("Error (File Not Found):".$command);
                }
            }
        }
    }
}
