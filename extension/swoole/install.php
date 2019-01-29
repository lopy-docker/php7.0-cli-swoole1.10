<?php
class SwooleExtInstaller extends ExtInstaller{

    /**
     * extension name
     * eg: apcu mysql pdo_mysql ...
     */
    protected $extName = 'swoole';
    
    /**
     * eg: https://github.com/swoole/swoole-src/archive/v1.10.5.tar.gz
     */
    protected $extSourceUrl = "https://github.com/swoole/swoole-src/archive/v1.10.5.tar.gz";

    /**
     * the dir of extracted source file
     * eg: swoole-src-1.10.5
     */
    protected $extNameExtracted = "swoole-src-1.10.5";

    /**
     * the filename of ini file
     * Nullable
     */
    protected $extIniFileName = '';

    /**
     * filename of source file downloaded
     * Nullable
     */
    protected $extSourceName = "";
    
    /**
     * set params in command of ./configure
     * @var string[]
     * Nullable
     */
    protected $extConfigureParams = [];

    /**
     * set options in ini file
     * @var string[]
     * Nullable
     */
    protected $optionParams = [];
    
    protected function getCurrentPath()
    {
        return __DIR__ ;
    }

    protected function beforeInstall()
    {

    }

    protected function beforeSetOption()
    {

    }

    protected function beforeExtract()
    {

    }
}