<?php
namespace Plugin\Rss;

class Event {


    public static function ipPageUpdated($data)
    {
        if (!isset($data['rssFeed'])) {
            return;
        }
        $pageId = $data['id'];

        Model::updateRssCheckbox($pageId, $data['rssFeed']);
    }

}
