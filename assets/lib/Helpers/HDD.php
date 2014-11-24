<?php

namespace Helpers;

class HDD{
	/**
     * @var cached reference to singleton instance
     */
    protected static $instance;

	private $this->_fileInfo = array();

	/**
     * gets the instance via lazy initialization (created on first usage)
     *
     * @return self
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * is not allowed to call from outside: private!
     *
     */
    private function __construct()
    {
    }
    /**
     * prevent the instance from being cloned
     *
     * @return void
     */
    private function __clone()
    {
    }
    /**
     * prevent from being unserialized
     *
     * @return void
     */
    private function __wakeup()
    {
    }

	/**
     * Чтобы не дергать постоянно файл который обрабатываем
     *
     * @access private
     * @param string $name ключ
     * @return string информация из pathinfo о обрабатываемом файле input
     */
    private function _pathinfo($file, $mode){
    	if(is_scalar($file) && is_scalar($mode)){
    		$file = $mode = '';
		}
		$flag = !(empty($file) || empty($mode));
		$f = MODX_BASE_PATH . $this->relativePath($file);
        if($flag && !isset($this->_fileInfo[$file], $this->_fileInfo[$file][$mode]) $this->checkFile($file)){
            $this->_fileInfo[$file][$mode] = pathinfo($f);
        }
        $out = $flag && isset($this->_fileInfo[$file][$mode]) ? $this->_fileInfo[$file][$mode] : '';
        return $out;
    }

    public function takeFileDir($file){
    	return $this->_pathinfo($file, 'dirname');
    }

    public function takeFileBasename($file){ //file name with extension
    	return $this->_pathinfo($file, 'basename');
    }

    public function takeFileName($file){
    	return $this->_pathinfo($file, 'filename');
    }

	public function takeFileExt($file){
		return strtolower($this->_pathinfo($file, 'extension'));
	}

	public function checkFile($file){
		$f = is_scalar($file) ? MODX_BASE_PATH . $this->relativePath($file) : '';
		return (!empty($f) && is_file($f) && is_readable($f));
	}

    public function checkDir($path){
        $f = is_scalar($path) ? $this->relativePath($path) : '';
        return (!empty($f) && is_dir(MODX_BASE_PATH . $f) && is_readable(MODX_BASE_PATH . $f));
    }

	public function takeFileMIME($file){
		$out = '';
        $path = $this->relativePath($file);
		if($this->checkFile($path)){
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$out = finfo_file($finfo, MODX_BASE_PATH.$path);
			finfo_close($finfo);
		}
		return $out;
	}

    public function makeDir($path, $perm = 0755){
        $flag = false;
        if (!$this->checkDir($path)){
            $path = MODX_BASE_PATH . $this->relativePath($path);
            $flag = mkdir($path, $this->toOct($perm), true);
        }
        return $flag;
    }

    /**
     * Копирование файла с проверкой на существование оригинального файла и созданием папок
     *
     * @param $from источник
     * @param $to получатель
     * @return bool статус копирования
     */
    public function copyFile($from, $to, $chmod = 0644){
        $flag = false;
        $from = MODX_BASE_PATH . $this->relativePath($from);
        $to = MODX_BASE_PATH . $this->relativePath($to);
        $dir = $this->takeFileDir($to);
        if($this->checkFile($from) && $this->makeDir($dir) && copy($from, $to)){
            chmod($to, $this->toOct($chmod));
            $flag = true;
        }
        return $flag;
    }

    /**
     * Получение относительного пути к файлу или папки
     *
     * @param string $path путь из которого нужно получить относительный
     * @param string $owner начальный путь который стоит вырезать
     * @return string относительный путь
     */
    public function relativePath($path, $owner = null){
        if(is_null($owner)){
            $owner = MODX_BASE_PATH;
        }
        if(!(empty($path) || !is_scalar($path)) && !preg_match("/^http(s)?:\/\/\w+/",$path)){
            $path = trim(preg_replace("#^".$owner."#", '', $path), '/');
        }else{
            $path = '';
        }
        return $path;
    }

    /**
     * Перевод строки/числа из восьмеричной/десятичной системы счисления в 8-ричную систему счисления
     * 0755 => 0755
     * '0755' => 0755
     * 755 => 0755
     * '755' => 0755
     *
     * @param  mixed $chmod строка или число в восьмеричной/десятичной системе счисления
     * @return int        число в восьмеричной системе счисления
     */
    public function toOct($chmod){
        $tmp = (string)$chmod;
        if(intval($tmp, 8) === octdec($chmod)){
            $tmp = (string)octdec($tmp);
        }
        return intval($tmp, 8);
    }

    public function rmDir($dirPath) {
        $path = MODX_BASE_PATH . $this->relativePath($dirPath);
        if ($this->checkDir($path)) {
            $dirIterator = new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS);
            $dirRecursiveIterator = new \RecursiveIteratorIterator($dirIterator, \RecursiveIteratorIterator::CHILD_FIRST);
            foreach($dirRecursiveIterator as $path) {
                $path->isDir() ? rmdir($path->getPathname()) : unlink($path->getPathname());
            }
            rmdir($dirPath);
        }
    }

    public function getInexistantFilename($file, $full = false) {
        $i = 1;
        $file = MODX_BASE_PATH.$this->relativePath($file);
        $out = $file;
        while ($this->checkFile($file)) {
            $i++;
            $out = $full ? $this->takeDir($file).'/';
            $out .= $this->takeFileName($file)."({$i}).".$this->takeFileExt($file);
        }
        return $out;
    }
}