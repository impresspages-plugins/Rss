<?php
/**
 * Created by PhpStorm.
 * User: Marijus
 * Date: 5/13/14
 * Time: 3:44 PM
 */
namespace Plugin\Rss;

$routes[ipGetOption('Rss.rssUrl').'{/languageCode}'] = array(
    'name' => 'Rss',
    'action' =>
        function($languageCode = null) {
            if ($languageCode == null) {
                $languageCode = ipContent()->getCurrentLanguage()->getCode();
            }
            $data = array(
                'title' => ipGetOption('Rss.channelTitle'),
                'url' => ipRouteUrl('Rss'),
                'description' => ipGetOption('Rss.channelDescription'),
            );

            $data['items'] = Model::getRssItems($languageCode);
            $data['language'] = Model::getRssLanguage($languageCode);
            $xmlResponse = new XmlResponse($data);

            return $xmlResponse;
        }
);
