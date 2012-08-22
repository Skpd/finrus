<?php
class Form_CreditSearch extends Zend_Form
{
    public function init()
    {
        $this->setAction(Zend_Controller_Front::getInstance()->getBaseUrl() . '/default/credit/list')->setMethod('GET');

        $this->addElement('select', 'field', array(
            'multioptions' => array(
                'client' => 'Фамилия',
            ),
            'decorators' => array('ViewHelper'),
            'class' => 'ui-state-default ui-corner-all'
        ));
        $this->addElement('text', 'value', array(
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
