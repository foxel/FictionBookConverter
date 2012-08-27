<?php

define('OUT_EOL', "\r\n");
define('OUT_ENCODING', 'cp1251');

class FictionBookConverter
{
    /** @var DOMDocument */
    protected $_xml;
    /** @var DOMXPath */
    protected $_xpath;
    /** @var resource */
    protected $_file;

    /**
     * @param string $file
     * @return bool
     */
    function convertFB2($file)
    {
        $this->_xml = new DOMDocument();
        if ($this->_xml->load($file) && $this->_file = fopen($file.'.txt', 'w+')) {
            $this->_xpath  = new DOMXPath($this->_xml);
            $rootNamespace = $this->_xml->lookupNamespaceUri($this->_xml->namespaceURI);
            $this->_xpath->registerNamespace('b', $rootNamespace);

            foreach ($this->_xpath->query('b:body') as $b) {
                foreach ($this->_xpath->query('b:title', $b) as $t) {
                    $this->_write($t->textContent);
                }
                $this->_write();
                foreach ($this->_xpath->query('b:section', $b) as $s) {
                    $this->_writeSection($s);
                }
                $this->_write();
            }
            fclose($this->_file);

            return true;
        }

        return false;
    }

    /**
     * @param string $text
     * @return string
     */
    protected function _convertEncoding($text)
    {
        $text = (string) $text;
        $text = strtr(mb_convert_encoding($text, OUT_ENCODING, 'UTF-8'), array(
            chr(0xA0) => ' ',
            PHP_EOL => OUT_EOL,
        ));
        return $text;
    }

    /**
     * @param string $line
     */
    protected function _write($line = '')
    {
        //print $line.OUT_EOL;

        if ($line) {
            $line = $this->_convertEncoding($line);
        }
        fwrite($this->_file, $line.OUT_EOL);
    }

    /**
     * @param DOMNode $s
     */
    protected function _writeSection(DOMNode $s)
    {
        $this->_xml = $s->ownerDocument;
        foreach ($this->_xpath->query('b:title', $s) as $t) {
            $this->_write($t->textContent);
        }
        foreach ($this->_xpath->query('*[not(self::b:title)]', $s) as $p) {
            if ($p->localName == 'section') {
                $this->_writeSection($p);
            } else {
                $this->_write($p->textContent);
            }
        }
        $this->_write();
    }

}

$file = $argv[1];
//print_r($argv);die;
print 'Converting '.$file.'...'.PHP_EOL;
$converter = new FictionBookConverter();
if (file_exists($file) && is_file($file)) {
    $converter->convertFB2($file);
}
