<?php

class Model_DbTable_Clients extends Zend_Db_Table_Abstract
{
    protected $_name = 'clients';

    protected $_rowClass = 'Model_Client';
}
