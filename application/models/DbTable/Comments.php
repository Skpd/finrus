<?php

class Model_DbTable_Comments extends Zend_Db_Table_Abstract
{
    protected $_name = 'comments';

    protected $_referenceMap = array(
        'User' => array(
            'columns'           => 'user_id',
            'refTableClass'     => 'Model_DbTable_Users',
            'refColumns'        => 'id'
        ),
    );

    public function getByConversationId($conversation)
    {
        return $this->select(true)->setIntegrityCheck(false)
            ->join('users', 'comments.user_id = users.id', array('author' => 'login'))
            ->where('conversation = ?', $conversation)
            ->order('created ASC')
            ->query()->fetchAll();
    }
}
