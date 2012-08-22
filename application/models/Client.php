<?php

class Model_Client extends Zend_Db_Table_Row_Abstract
{
    public function isBlacklisted()
    {
        $blacklist = new Model_DbTable_BlackList();
        return $blacklist->fetchAll(array('client_id = ?' => $this->_data['id']))->count() > 0;
    }
}