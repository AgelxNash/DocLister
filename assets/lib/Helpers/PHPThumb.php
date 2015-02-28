<?php

namespace Helpers;

include_once(MODX_BASE_PATH.'assets/snippets/phpthumb/phpthumb.class.php');

class PHPThumb{

    private $thumb = null;
    public $debugMessages = '';

    public function __construct()
    {
       $this->thumb = new \phpthumb();
    }

    public function create($inputFile, $outputFile, $options) {
        $this->thumb->sourceFilename = $inputFile;
        $this->setOptions($options);
        if ($this->thumb->GenerateThumbnail() && $this->thumb->RenderToFile($outputFile)) {
            return true;
        } else {
            $this->debugMessages = implode('<br/>', $this->thumb->debugmessages);
            return false;
        }
    }

    private function setOptions($options) {
        $options = strtr($options, Array("," => "&", "_" => "=", '{' => '[', '}' => ']'));
        parse_str($options, $params);
        foreach ($params as $key => $value) {
            $this->thumb->setParameter($key, $value);
        }
    }
}