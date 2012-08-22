<?php
class Form_Auth extends Zend_Form
{

    public function init()
    {
        $this->setAction(Zend_Controller_Front::getInstance()->getBaseUrl() . '/default/index/login');

        $this->addElement('text', 'login', array(
            'label' => 'Логин: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));
        $this->addElement('password', 'password', array(
            'label' => 'Пароль: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $this->addElement('submit', 'enter', array(
            'label' => 'Вход',
            'class' => 'ui-state-default ui-corner-all default-button',
            'decorators' => array(
                'ViewHelper'
            )
        ));


        //$this->loadDefaultDecorators();
        $this->addDecorators(array(
            'FormErrors',
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'default-form')),
            'Form'
        ));
    }

}
