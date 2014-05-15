<?php
namespace Plugin\Rss;

/**
 *
 * Event dispatcher class
 *
 */
class XmlResponse extends \Ip\Response {

    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function render()
    {
        $xml = Service::arrayToXml($this->content);

        return $xml;

    }

    public function send()
    {
        $this->addHeader('Content-type: text/xml;');
        parent::send();
    }

    /**
     *
     *  Returns $dat encoded to UTF8
     * @param mixed $dat array or string
     */
    private function utf8Encode($dat)
    {
        if (is_string($dat)) {
            if (mb_check_encoding($dat, 'UTF-8')) {
                return $dat;
            } else {
                return utf8_encode($dat);
            }
        }
        if (is_array($dat)) {
            $answer = array();
            foreach($dat as $i=>$d) {
                $answer[$i] = $this->utf8Encode($d);
            }
            return $answer;
        }
        return $dat;
    }
}