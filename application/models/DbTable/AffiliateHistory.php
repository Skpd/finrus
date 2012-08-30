<?php

class Model_DbTable_AffiliateHistory extends Zend_Db_Table_Abstract
{
    protected $_name = 'affiliate_history';

    protected $_referenceMap    = array(
        'Affiliate' => array(
            'columns'           => 'affiliate_id',
            'refTableClass'     => 'Model_DbTable_Affiliates',
            'refColumns'        => 'id'
        ),
    );
}