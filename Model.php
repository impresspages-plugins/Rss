<?php
namespace Plugin\Rss;

use Ip\Internal\Content\Page;

class Model
{

    public static function updateRssCheckbox($pageId, $isEnabled)
    {
        if ($isEnabled == "on" || $isEnabled == 1) {
            $value = 1;
        } else {
            $value = 0;
        }

        ipPageStorage($pageId)->set('rssFeed', $value);

    }

    public static function getRssItems($languageCode)
    {

        $sql = "SELECT p.id FROM " . ipTable('page') . " AS p
            LEFT JOIN " . ipTable('page_storage') . " AS s ON p.id=s.pageId
            WHERE s.`key`='rssFeed' AND s.`value`=1 AND p.`languageCode`='" . esc($languageCode) . "'";

        $rssPageIds = ipDb()->fetchColumn($sql);

        $items = array();
        foreach ($rssPageIds as $pageId) {

            $pageContent = self::getPageContent($pageId);

            if (isset($pageContent['text']) && ($pageContent['text'])){
                $item['url'] = ipHomeUrl().ipPage($pageId)->getUrlPath();
                $item['description'] = $pageContent['text'];

                if (isset($pageContent['heading']) && $pageContent['heading']){
                    $item['title'] = $pageContent['heading'];
                }else{
                    $item['title'] = ipPage($pageId)->getTitle();
                }
                $items[] = $item;
            }

        }

        return $items;
    }

    public static function getRssLanguage($languageCode)
    {
        return $languageCode; //TODO Get RSS language
    }

    public static function getPageContent($pageId)
    {

        $revisionId = self::getRevisionId($pageId);
        if ($revisionId) {
            $pageContent = self::getWidgetsForRss($revisionId);
            return $pageContent;
        } else {
            return false;
        }

    }

    private static function getWidgetsForRss($revisionId)
    {

        $allWidgets = self::getWidgets($revisionId);

        $widgets = array();

        if (self::hasLeadBreakWidget($allWidgets)){

            $widgets = self::getContentBeforeLeadBreak($allWidgets);

        }else{

            foreach ($allWidgets as $widget){

                $widgetText = self::getWidgetHeading($widget);
                if ($widgetText){
                    $widgets['heading'] = $widgetText;
                    break;
                }
            }

            foreach ($allWidgets as $widget){

                $widgetText = self::getWidgetText($widget);
                if ($widgetText){
                    $widgets['text'] = $widgetText;
                    break;
                }
            }
        }

        return $widgets;
    }

    /**
     * Gets all text till first lead break
     * @param $allWidgets
     * @return mixed|string
     */
    private static function getContentBeforeLeadBreak($allWidgets){

        $text = '';
        $heading = false;

        $cnt =0;

        foreach ($allWidgets as $widget){

            if (!$heading){
                $heading = self::getWidgetHeading($widget);
            }

            $text .= self::getWidgetText($widget);
            if ($widget['type']=='LeadBreak'){
                break;
            }
            $cnt++;
        }

        $text =  self::html2text($text);

        $content['heading'] = $heading;
        $content['text'] = $text;

        return $content;
    }

    private static function hasLeadBreakWidget($allWidgets){

        $hasLeadBreak = false;

        foreach ($allWidgets as $widget){
            if ($widget['type']=='LeadBreak'){
                $hasLeadBreak = true;
                break;
            }
        }

        return $hasLeadBreak;

    }

    private static function html2text($html){

        $html2text = new \Ip\Internal\Text\Html2Text($html, false);
        $text = esc($html2text->get_text());
        $text = str_replace("\n", '<br/>', $text);

        return $text;
    }

    private static function getWidgetHeading($widget){

        if (($widget['type']=='Heading') && isset($widget['data']['title'])){
            $title = $widget['data']['title'];
        }else{
            $title = false;
        }
        $title =  self::html2text($title);

        return $title;
    }


    private static function getWidgetText($widget){
        if (($widget['type']=='Text') && isset($widget['data']['text'])){
            $text = $widget['data']['text'];
        }else{
            $text = false;
        }

        $text =  self::html2text($text);

        return $text;
    }

    /**
     * Returns widget elements
     * @param $pageId
     */
    private static function getWidgets($publishedRevisionId)
    {

        /** @var \Ip\Page $revisionId */
        $widgetRecords = ipDb()->selectAll(
            'widget', '*',
            array(
                'revisionId' => $publishedRevisionId,
                'isVisible' => 1,
                'isDeleted' => 0,
                'blockName' => 'main'
            ),
            'ORDER BY position ASC'
        );

        $widgetData = array();
        if (!empty($widgetRecords)) {

            foreach ($widgetRecords as $widgetRecord) {

                if ($widgetRecord['name'] != 'Columns') {

                    $widgetFiltered = self::getWidget($widgetRecord);

                    if ($widgetFiltered) {
                        $widgetData[] = $widgetFiltered;
                    }

                }
            }
        }

        return $widgetData;
    }

    public static function getWidget($widgetRecord)
    {

        if (isset($widgetRecord['name'])) {

            $widget['type'] = $widgetRecord['name'];

            if (isset($widgetRecord['skin'])) {
                $widget['layout'] = $widgetRecord['skin'];
            }

            if (isset($widgetRecord['blockName'])) {
                $widget['blockName'] = $widgetRecord['blockName'];
            }

            if (isset($widgetRecord['data'])) {
                $widget['data'] = json_decode($widgetRecord['data'], true);
                switch ($widget['type']) {
                    case 'Image':
//                        self::copyWidgetFile($widget['data']['imageOriginal']);
                        break;
                    case 'Gallery':
//                        self::copyWidgetGalleryFiles($widget['data']);
                        break;
                }
            } else {
                $widget = false;
            }

        } else {
            $widget = false;
        }

        return $widget;
    }


    private static function getRevisionId($pageId)
    {

        $revisionTable = ipTable('revision');
        $sql = "
            SELECT * FROM $revisionTable
            WHERE
                `pageId` = ? AND
                `isPublished` = 1
            ORDER BY `createdAt` DESC, `revisionId` DESC
        ";
        $revision = ipDb()->fetchRow($sql, array($pageId));
        if ($revision) {
            return $revision['revisionId'];
        } else {
            return false;
        }


    }


}