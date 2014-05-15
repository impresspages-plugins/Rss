<?php
namespace Plugin\Rss;

class Filter {

    /**
     * @param \Ip\Form $form
     * @return mixed
     */
    public static function ipPagePropertiesForm($form, $info)
    {
        $values = array();
        $current = ipPageStorage($info['pageId'])->get('rssFeed');

        $fieldset = new \Ip\Form\Fieldset(__('RSS', 'rssFeed', false));
        $form->addFieldset($fieldset);

        $form->addField(new \Ip\Form\Field\Checkbox(
            array(
                'name' => 'rssFeed',
                'label' => 'Add to RSS feed',
                'value' => $current
            )
        ));

        return $form;
    }
}
