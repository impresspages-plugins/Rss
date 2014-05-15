<?php
namespace Plugin\Rss;

class Service{

    public static function arrayToXml($data) {

        $xml = "<?xml version='1.0' encoding='UTF-8'?>";
        $xml.= "<rss version='2.0'>";

        $xml.= "<channel>";

        $xml.= '<title>'.$data['title'].'</title>';
        $xml.= '<link>'.$data['url'].'</link>';
        $xml.= '<description>'.$data['description'].'</description>';
        $xml.= '<language>'.$data['language'].'</language>';

        if (!empty($data['items'])){
            foreach ($data['items'] as $item){
                $xml.= '<item>';
                $xml.= '<title>'.$item['title'].'</title>';
                $xml.= '<link>'.$item['url'].'</link>';
                $xml.= '<description>'.$item['description'].'</description>';
                $xml.= '</item>';

            }
        }

        $xml.= '</channel>';
        $xml.= '</rss>';

        return $xml;
    }

}