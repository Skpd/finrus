<?php
class Form_PaymentAdd extends Zend_Form
{

    public function init()
    {
        $this->addElements(
            array(
                'client_id' => array(
                    'type' => 'hidden',
                    'options' => array(
                        'validators' => array(
                            array('Db_RecordExists', false, array('table' => 'clients', 'field' => 'id'))
                        )
                    )
                ),
                'amount'    => array(
                    'type'    => 'text',
                    'options' => array(
                        'validators' => array(
                            'Alnum'
                        )
                    )
                ),
                'type'      => array(
                    'type'    => 'select',
                    'options' => array(
                        'multiOptions' => array(
                            'payment' => 'Оплата',
                            'reopen'  => 'Продление',
                            'other'   => 'Особый',
                        )
                    )
                ),
                'create'    => array(
                    'type' => 'submit'
                ),
                'cancel'    => array(
                    'type' => 'submit'
                ),
            )
        );
    }

}
