<?php

abstract class ExtInstaller
{

    /**
     * extension name
     * eg: apcu mysql pdo_mysql ...
     */
    protected $extName = '';


    /**
     * the filename of ini file
     * 如果为空，则为 docker-php-ext-enable 自动生成filename
     * eg: 10-apc mysql 20-xxx_ddd-pdo_mysql
     */
    protected $extIniFileName = '';

    /**
     * eg: https://github.com/swoole/swoole-src/archive/v1.10.5.tar.gz
     */
    protected $extSourceUrl = "";

    /**
     * 下载的ext源码文件名，如果不填，
     * 如果该项不填，则会重命名为 extSourceName=$extName".tar.gz"
     * eg: apcu.tar.gz mysql.tar.gz pdo_mysql.tar
     *
     * 目前支持 tar.gz tar tar.xz 一个一个的加
     */
    protected $extSourceName = "";


    /**
     * the dir of extracted source file
     * eg: swoole-src-1.10.5
     */
    protected $extNameExtracted = "";

    /**
     * set params in command of ./configure
     * @var string[]
     */
    protected $extConfigureParams = [];

    /**
     * set options in ini file
     * @var string[]
     */
    protected $optionParams = [];


    /**
     * 安装
     * @throws Exception
     */
    final public function install()
    {
        //
        //判断是否存在源码


        $currentDir = $this->getCurrentPath();
        chdir($currentDir);
        $sourceName = $this->getExtSourceFileName();
        $fileInfo = new SplFileInfo($currentDir . '/' . $sourceName);

        var_dump($fileInfo->getRealPath());
        if (!file_exists($sourceName)) {
            //从源码下载
            $this->getSource();
        }


        $this->beforeExtract();
        //解压
        //fileinfo
        $extractObj = new Extract($fileInfo);
        $extractObj->run();


        $this->beforeInstall();
        //system('cd "' . $this->extNameExtracted.'" && phpize && ');
        chdir($this->extNameExtracted);
        $commandList = [
            'phpize',
            './configure ' . join(' ', $this->extConfigureParams),
            'make',
            'make install',
        ];


        //install
        $command = join(' && ', $commandList);
        var_dump($command);
        system($command);

        //enable
        $command = 'docker-php-ext-enable --ini-name ' . $this->getExtIniName() . ' ' . $this->extName;
        var_dump($command);
        system($command);

        //configure
        $fullFilename = $this->getExtIniFilename();
        $this->beforeSetOption();
        foreach ($this->optionParams as $val) {
            $command = 'echo "' . $val . '" >> "' . $fullFilename . '"';
            var_dump($command);
            system($command);
        }

    }


    /**
     * 当前文件夹
     */
    protected abstract function getCurrentPath();


    /**
     * @return string
     */
    protected function getExtSourceFileName()
    {

        if (empty($this->extSourceName)) {
            return $this->extName . '.tar.gz';
        } else {
            return $this->extSourceName;
        }
    }


    protected function getExtIniName()
    {
        $ini = empty($this->extIniFileName) ? "docker-php-ext-" . $this->extName : $this->extIniFileName;
        return $ini . '.ini';
    }

    /**
     * 获取文件路径
     */
    protected function getExtIniFilename()
    {
        $iniFile = getenv("PHP_INI_DIR") . '/conf.d/' . $this->getExtIniName();
        return $iniFile;
    }

    /**
     * 在当前文件夹下执行下载操作
     */
    public function getSource()
    {
        if (empty($this->extSourceUrl)) {
            throw new Exception('source file is not empty');
        }

        $sourceFileName = $this->getExtSourceFileName();
        $command = "curl -L {$this->extSourceUrl} -o {$sourceFileName}";
        var_dump($command);
        system($command);

        return $sourceFileName;
    }


    //
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

class Extract
{

    /**
     * @var SplFileInfo
     */
    protected $fileInfo = null;

    /**
     * Extract constructor.
     * @param SplFileInfo $fileInfo
     * @throws Exception
     */
    public function __construct($fileInfo)
    {
        if (!file_exists($fileInfo->getRealPath())) {
            throw new Exception('file is not exists');
        }
        $this->fileInfo = $fileInfo;
    }

    public function run()
    {
        if (ExtString::endsWith($this->fileInfo->getFilename(), 'tar.gz')) {
            $name = 'tarGz';
        } else if (ExtString::endsWith($this->fileInfo->getFilename(), 'tar')) {
            $name = 'tar';

        } else if (ExtString::endsWith($this->fileInfo->getFilename(), 'tar.xz')) {
            $name = 'tarXz';

        } else {
            throw new Exception('this is no method to analyze this file. [' . $this->fileInfo->getFilename() . ']');
        }

        $this->{$name}();
    }

    protected function tarGz()
    {
        $command = 'tar -zxvf ' . $this->fileInfo->getFilename();
        system($command);
    }

    protected function tar()
    {
        $command = 'tar -xvf ' . $this->fileInfo->getFilename();
        system($command);
    }

    protected function tarXz()
    {
        //tar xvJf  your.tar.xz
        $command = 'tar -xvJf ' . $this->fileInfo->getFilename();
        system($command);
    }
}


class ExtString
{

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    public static function startsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if ($needle !== '' && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string $haystack
     * @param  string|array $needles
     * @return bool
     */
    public static function endsWith($haystack, $needles)
    {
        foreach ((array)$needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }

    public static function camelize($name)
    {
        $return = str_replace(['-', '_'], [' ', ' '], $name);
        $return = ucwords($return);
        return preg_replace('/\s+/', '', $return);
    }
}


class ExtOperate
{

    private $optionName = '';

    private $optionGenerate = false;
    private $optionGetSource = false;


    //
    private $extClassName = '';
    private $extFilename = '';
    private $extDirName = '';

    public function __construct($options)
    {
        if (!isset($options['n']) || empty($options['n'])) {

            $message = <<<info
params n g s
    -n : the ext name,required, eg: -n mysql
    -g : generate ext,require -n
    -s : get source code of the ext,require -n

run eg:
    exec "php ExtInstaller -n mysql"  to install mysql extension
    exec "php ExtInstaller -n mysql -g" to generate file for installing mysql extension 
    exec "php ExtInstaller -n mysql -s" to get source code archive from the file prepare installing mysql extension 
info;

            throw new Exception($message);
        }


        $this->optionName = $options['n'];
        $this->optionGenerate = isset($options['g']) ? true : false;
        $this->optionGetSource = isset($options['s']) ? true : false;


        $this->init();
    }

    protected function init()
    {
        $dirName = $filename = __DIR__ . '/' . $this->optionName;
        $filename = $dirName . '/install.php';
        $className = ExtString::camelize($this->optionName) . 'ExtInstaller';

        //filter

        //value

        $this->extDirName = $dirName;
        $this->extFilename = $filename;
        $this->extClassName = $className;
    }


    /**
     * @throws Exception
     */
    public function run()
    {
        if ($this->optionGenerate) {
            $this->generate();
            return;
        }

        if ($this->optionGetSource) {
            $this->source();
            return;
        }

        $this->install();
    }


    /**
     * @return ExtInstaller
     * @throws Exception
     */
    protected function getExtObj()
    {
        if (!file_exists($this->extFilename)) {
            throw new Exception('the installer is not exists');
        }
        include_once $this->extFilename;

        $class = $this->extClassName;
        return new $class();
    }

    /**
     * @throws Exception
     */
    protected function install()
    {
        $this->getExtObj()->install();
    }

    /**
     * @throws Exception
     */
    protected function source()
    {
        chdir($this->extDirName);
        //
        $this->getExtObj()->getSource();
    }

    /**
     * @throws Exception
     */
    protected function generate()
    {
        if (file_exists($this->extDirName)) {
            throw new Exception('dir or file is already exists');
        }


        mkdir($this->extDirName, 0755);
        $fileContent = <<<content
<?php
class {$this->extClassName} extends ExtInstaller{

    /**
     * extension name
     * eg: apcu mysql pdo_mysql ...
     */
    protected \$extName = '{$this->optionName}';
    
    /**
     * eg: https://github.com/swoole/swoole-src/archive/v1.10.5.tar.gz
     */
    protected \$extSourceUrl = "";

    /**
     * the dir of extracted source file
     * eg: swoole-src-1.10.5
     */
    protected \$extNameExtracted = "";

    /**
     * the filename of ini file
     * Nullable
     */
    protected \$extIniFileName = '';

    /**
     * filename of source file downloaded
     * Nullable
     */
    protected \$extSourceName = "";
    
    /**
     * set params in command of ./configure
     * @var string[]
     * Nullable
     */
    protected \$extConfigureParams = [];

    /**
     * set options in ini file
     * @var string[]
     * Nullable
     */
    protected \$optionParams = [];
    
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
content;

        //write file
        file_put_contents($this->extFilename, $fileContent);
    }
}


try {
    $operation = new ExtOperate(getopt("n:gs"));
    $operation->run();
} catch (Exception $exception) {
    var_dump($exception->getMessage());
}
