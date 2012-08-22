<?php
class Form_ClientSearch extends Zend_Form
{
    public function init()
    {
        $this->setMethod('GET');

        $this->addElement('select', 'field', array(
            'label' => 'Поле: ',
            'multioptions' => array(
                'first_name' => 'Имя',
                'last_name' => 'Фамилия',
                'middle_name' => 'Отчество',
                'passport' => '№ Паспорта'
            ),
            'decorators' => array('ViewHelper'),
            'class' => 'ui-state-default ui-corner-all'
        ));
        $this->addElement('text', 'value', array(
            'label' => 'Значение: ',
            'decorators' => array('ViewHelper'),
            'class' => 'ui-state-default ui-corner-all'
        ));

        $this->addElement('hidden', 'page', array(
            'decorators' => array('ViewHelper')
        ));
        $this->addElement('hidden', 'limit', array(
            'value' => 30,
            'decorators' => array('ViewHelper')
        ));

        $this->addElement('hidden', 'sort', array(
            'decorators' => array('ViewHelper')
        ));

        $this->addElement('hidden', 'direction', array(
            'decorators' => array('ViewHelper')
        ));

        $this->addElement('submit', 'search', array(
            'label' => 'Найти',
            'class' => 'ui-state-default ui-corner-all default-button',
            'decorators' => array('ViewHelper')
        ));

        $this->addDecorators(array(
            'FormErrors',
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'default-form')),
            'Form'
        ));
    }
}
