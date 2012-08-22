<?php

class Model_DbTable_Users extends Zend_Db_Table_Abstract
{
    protected $_name = 'users';

    protected $_referenceMap    = array(
        'Affiliate' => array(
            'columns'           => 'affiliate_id',
            'refTableClass'     => 'Model_DbTable_Affiliates',
            'refColumns'        => 'id'
        ),
    );
}
