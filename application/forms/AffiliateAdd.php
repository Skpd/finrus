<?php
class Form_AffiliateAdd extends Zend_Form
{

    public function init()
    {
        $this->addElement('text', 'name', array(
            'label' => 'Имя: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $select = $this->createElement('select', 'city_id', array(
            'label' => 'Город: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $cities = new Model_DbTable_Cities();

        $res = $cities->fetchAll();

        foreach ($res as $city) {
            $select->addMultiOption($city['id'], $city['name']);
        }

        $this->addElement($select);

        $this->addDecorators(array(
            'FormErrors',
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'default-form')),
            'Form'
        ));
    }

}
