<?php

class Model_DbTable_Affiliates extends Zend_Db_Table_Abstract
{
    protected $_name = 'affiliates';

    protected $_referenceMap    = array(
        'City' => array(
            'columns'           => 'city_id',
            'refTableClass'     => 'Model_DbTable_Cities',
            'refColumns'        => 'id'
        ),
    );
}
