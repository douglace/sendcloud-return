<?php

class SendCloudLog {

    public static function writeToJsonLog (string $message, bool $append = true):bool
    {
        $path_error = _PS_MODULE_DIR_."/sendcloud/log/errors.json";
        return (bool) file_put_contents($path_error, $message, FILE_APPEND);
    }
}