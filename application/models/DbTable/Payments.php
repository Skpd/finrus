<?php

class Model_DbTable_Payments extends Zend_Db_Table_Abstract
{
    protected $_name = 'payments';

    protected $_dependentTables = array();

    protected $_referenceMap    = array(
        'Credit' => array(
            'columns'           => 'credit_id',
            'refTableClass'     => 'Model_DbTable_Credits',
            'refColumns'        => 'id'
        ),
    );

    public function create(array $values)
    {
        $user = Zend_Auth::getInstance()->getStorage()->read();

        if (!isset($values['user_id'])) {
            $values['user_id'] = $user->id;
        }

        $values['date'] = date('Y-m-d H:i:s', time());

        $credits = new Model_DbTable_Credits();

        $values['credit_id'] = (int) $credits->select()
            ->where('client_id = ?', $values['client_id'])
            ->where("status='active'")
            ->query()
            ->fetchColumn()
        ;

        if (empty($values['credit_id'])) {
            return false;
        }

        $row = $this->createRow($values);

        $creditRow = $row->findParentRow('Model_DbTable_Credits');

        if (empty($row['amount'])) {
            $row['amount'] = $creditRow['amount'];
        }

        $creditRow['remain'] -= $row['amount'];

        if ($creditRow['remain'] <= 0) {
            $creditRow['status'] = 'successfull';
        }

        $creditRow->save();
        $row->save();

        return $row;
    }
}
