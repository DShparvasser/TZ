<?php

class CustomLogger
{
    public function writeLog($fileName, $data)
    {
        $file = fopen('log/'.$fileName, 'a+');
        fwrite($file, '[' . date("Y-m-d H:i:s") . ']: ' . json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL);
        fclose($file);
    }
}