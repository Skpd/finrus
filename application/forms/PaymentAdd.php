<?php
class Form_PaymentAdd extends Zend_Form
{

    public function init()
    {
        $this->addElement('hidden', 'client_id', array(
            'decorators' => array(
                array('HtmlTag', array('id' => 'client_id'))
            )
        ));
        /*
        $select = $this->createElement('select', 'client_id', array(
            'label' => 'Клиент: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $clients = new Model_DbTable_Clients();

        $res = $clients->fetchAll()->toArray();

        foreach ($res as $client) {
            $select->addMultiOption(
                $client['id'],
                implode(' ', array(
                    $client['last_name'], $client['first_name'], $client['middle_name']
                ))
            );
        }

        $this->addElement($select);
        */

        $this->addElement('text', 'amount', array(
            'label' => 'Сумма: ',
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
            'FormErrors',
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'default-form')),
            'Form'
        ));

        #$this->setElementDecorators(array('Label', 'ViewHelper', 'DtDdWrapper'), null);
    }

}
