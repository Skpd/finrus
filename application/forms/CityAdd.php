<?php
class Form_CityAdd extends Zend_Form
{

    public function init()
    {
        $this->addElement('text', 'name', array(
            'label' => 'Имя: ',
            'required' => true,
            'class' => 'ui-state-default ui-corner-all'
        ));

        $this->addDecorators(array(
            'FormErrors',
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'default-form')),
            'Form'
        ));
    }

}
