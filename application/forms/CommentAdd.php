<?php

class Form_CommentAdd extends Zend_Form
{
    public function init()
    {
        $this->setAction(Zend_Controller_Front::getInstance()->getBaseUrl() . '/comments/add');

        $this->setAttrib('id', 'comment-form');

        $this->addElement('textarea', 'text', array(
            'placeholder' => 'Комментарий',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $this->addElement('hidden', 'conversation', array(
            'decorators' => array('ViewHelper')
        ));

        $this->addElement('button', 'comment-add', array(
            'label' => 'Добавить',
            'class' => 'ui-state-default ui-corner-all default-button',
            'decorators' => array('ViewHelper')
        ));

        $this->addDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'default-form')),
            'Form'
        ));
    }
}