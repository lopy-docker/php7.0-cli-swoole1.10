<?php

class ApcuExtInstaller extends ExtInstaller{

    protected $extName = "apcu";


    protected $extSourceUrl = "https://github.com/krakjoe/apcu/archive/v5.1.16.tar.gz";


    protected $extNameExtracted = "apcu-5.1.16";
    

    protected function getCurrentPath()
    {
        return __DIR__ ;
    }

    protected function beforeSetOption()
    {
        $this->optionParams = [
            "[XDebug]",
            "apc.enable_cli=1",
            "apc.ttl=10",
            "apc.use_request_time=0",
        ];
    }
}
