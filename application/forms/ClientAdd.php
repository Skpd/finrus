<?php
class Form_ClientAdd extends Zend_Form
{

    public function init()
    {

        $this->setAttrib('enctype', 'multipart/form-data');

        $this->addElement('text', 'first_name', array(
            'label' => 'Имя: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $this->addElement('text', 'last_name', array(
            'label' => 'Фамилия: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $this->addElement('text', 'middle_name', array(
            'label' => 'Отчество: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $this->addElement('text', 'age', array(
            'label' => 'Возраст: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $this->addElement('text', 'passport', array(
            'label' => '№ Паспорта: ',
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

        $this->addElement('text', 'phone', array(
            'label' => 'Телефон: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $this->addElement('text', 'address', array(
            'label' => 'Адрес: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $this->addElement('file', 'passport_img', array(
            'label' => 'Скан паспорта: ',
            'class' => 'ui-state-default ui-corner-all'
        ));

        $this->addElement('submit', 'create', array(
            'label' => 'Создать',
            'class' => 'ui-state-default ui-corner-all default-button',
            'decorators' => array('ViewHelper')
        ));

        $this->addElement('submit', 'cancel', array(
            'label' => 'Отмена',
            'class' => 'ui-state-default ui-corner-all default-button',
            'decorators' => array('ViewHelper')
        ));

        $this->addDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'default-form')),
            'Form'
        ));

        #$this->setElementDecorators(array('Label', 'ViewHelper', 'DtDdWrapper'), null);
    }

}
