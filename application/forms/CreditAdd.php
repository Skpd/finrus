<?php
class Form_CreditAdd extends Zend_Form
{

    public function init()
    {

        $this->addElement('hidden', 'client_id', array(
            'decorators' => array(
                array('HtmlTag', array('id' => 'client_id'))
            )
        ));

        $this->addElement('text', 'amount', array(
            'label' => 'Сумма: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        /*
        $this->addElement('text', 'next_payment_date', array(
            'label' => 'Next Payment: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));
        */

        $this->addElement('select', 'duration', array(
            'label' => 'Длительность: ',
            'multiOptions' => array(
                '7'  => '7 дней',
                '14' => '14 дней',
                '21' => '21 дней',
                '30' => '30 дней'
            ),
            'required' => true,
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
