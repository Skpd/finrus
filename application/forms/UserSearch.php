<?php
class Form_UserSearch extends Zend_Form
{
    public function init()
    {
        $this->setAction(Zend_Controller_Front::getInstance()->getBaseUrl() . '/default/user/list')->setMethod('GET');

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

        $this->addDecorators(array(
            'FormErrors',
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'default-form')),
            'Form'
        ));
    }
}
