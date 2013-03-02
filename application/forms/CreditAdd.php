<?php
class Form_CreditAdd extends Zend_Form
{

    public function init()
    {

        $this->addElement(
            'hidden',
            'client_id',
            array(
                'decorators' => array(
                    array('HtmlTag', array('id' => 'client_id'))
                )
            )
        );

        $this->addElement(
            'text',
            'amount',
            array(
                'label'    => 'Сумма: ',
                'required' => true,
                'class'    => 'ui-state-default ui-corner-all'
            )
        );

        /*
        $this->addElement('text', 'next_payment_date', array(
            'label' => 'Next Payment: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));
        */

        $this->addElement(
            'select',
            'duration',
            array(
                'label'        => 'Длительность: ',
                'multiOptions' => array(
                    '7'  => '7 дней',
                    '14' => '14 дней',
                    '21' => '21 дней',
                    '30' => '30 дней',
                    '60' => '2 месяца',
                    '90' => '3 месяца',
                    '120' => '4 месяца',
                    '150' => '5 месяцев',
                    '180' => '6 месяцев',
                ),
                'required'     => true,
                'class'        => 'control-element ui-state-disabled',
                'disabled'     => true
            )
        );

        $this->addElement(
            'select',
            'type',
            array(
                'label'        => 'Тип: ',
                'multiOptions' => array(
                    'default'  => 'Стандарт',
                    'weekly'   => 'Недельная выплата',
                    'skipWeek' => 'Выплата через неделю',
                ),
                'required'     => true,
                'class'        => 'ui-state-default ui-corner-all'
            )
        );

        $this->addElement(
            'submit',
            'create',
            array(
                'label'      => 'Создать',
                'class'      => 'ui-state-default ui-corner-all default-button',
                'decorators' => array('ViewHelper')
            )
        );

        $this->addElement(
            'submit',
            'cancel',
            array(
                'label'      => 'Отмена',
                'class'      => 'ui-state-default ui-corner-all default-button',
                'decorators' => array('ViewHelper')
            )
        );

        $this->addDecorators(
            array(
                'FormElements',
                array('HtmlTag', array('tag' => 'dl', 'class' => 'default-form')),
                'Form'
            )
        );

        #$this->setElementDecorators(array('Label', 'ViewHelper', 'DtDdWrapper'), null);
    }

}
