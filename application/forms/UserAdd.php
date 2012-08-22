<?php
class Form_UserAdd extends Zend_Form
{

    public function init()
    {

        $this->addElement('text', 'login', array(
            'label' => 'Логин: ',
            'validators' => array(
                array('Db_NoRecordExists', false, array('table' => 'users', 'field' => 'login'))
            ),
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $this->addElement('text', 'password', array(
            'label' => 'Пароль: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $this->addElement('select', 'type', array(
            'label' => 'Роль: ',
            'required' => true,
            'multiOptions' => array(
                'user'  => 'Оператор',
                'admin' => 'Администратор'
            ),
            'class' => 'ui-state-default ui-corner-all'
        ));

        $select = $this->createElement('select', 'affiliate_id', array(
            'label' => 'Филиал: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $affiliates = new Model_DbTable_Affiliates();

        $res = $affiliates->fetchAll();

        foreach ($res as $affiliate) {
            $select->addMultiOption($affiliate['id'], $affiliate['name']);
        }

        $this->addElement($select);


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
