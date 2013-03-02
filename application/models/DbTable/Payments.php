<?php

class Model_DbTable_Payments extends Zend_Db_Table_Abstract
{
    protected $_name = 'payments';

    protected $_dependentTables = array();

    protected $_referenceMap = array(
        'Credit' => array(
            'columns'       => 'credit_id',
            'refTableClass' => 'Model_DbTable_Credits',
            'refColumns'    => 'id'
        ),
        'User'   => array(
            'columns'       => 'user_id',
            'refTableClass' => 'Model_DbTable_Users',
            'refColumns'    => 'id'
        ),
    );

    public function reopen(array $values)
    {
        if (!isset($values['user_id'])) {
            $values['user_id'] = Zend_Auth::getInstance()->getStorage()->read()->id;
        }

        if (!isset($values['date'])) {
            $values['date'] = date('Y-m-d H:i:s', time());
        }

        $credits = new Model_DbTable_Credits();

        $this->getAdapter()->beginTransaction();

        try {
            $credit = $credits->getActive($values['client_id']);

            $values['credit_id'] = $credit->id;

            if (empty($values['amount'])) {
                $values['amount'] = $credit->amount - $credit->origin_amount;
            }

            $row = $this->createRow($values);
            $row->save();

            $newCredit = $credit->toArray();

            unset($newCredit['id']);
            $newCredit['closing_date'] = date('Y-m-d', time() + strtotime($newCredit['closing_date']) - strtotime($newCredit['opening_date']));
            $newCredit['opening_date'] = date('Y-m-d', time());

            if ($newCredit['type'] == 'default') {
                $values['next_payment_date'] = $values['closing_date'];
            } else {
                $values['next_payment_date'] = date('Y-m-d', strtotime('+1 week'));
            }

            $newCredit['remain'] = $newCredit['amount'];

            $credits->insert($newCredit);

            $credit->remain = 0;
            $credit->status = Model_Credit::STATUS_SUCCESSFUL;
            $credit->save();

            $this->getAdapter()->commit();

            return $row;
        } catch (Exception $e) {
            $this->getAdapter()->rollBack();

            return false;
        }
    }

    public function create(array $values, $forceClose = false)
    {
        if (!isset($values['user_id'])) {
            $values['user_id'] = Zend_Auth::getInstance()->getStorage()->read()->id;
        }

        if (!isset($values['date'])) {
            $values['date'] = date('Y-m-d H:i:s', time());
        }

        $credits = new Model_DbTable_Credits();

        $this->getAdapter()->beginTransaction();

        try {
            $credit = $credits->getActive($values['client_id']);

            $values['credit_id'] = $credit->id;

            if (empty($values['amount'])) {
                $values['amount'] = $credit->amount;
            }

            $row = $this->createRow($values);
            $row->save();

            $credit->remain -= $values['amount'];

            if ($credit->remain <= 0 || $forceClose) {
                $credit->remain = 0;
                $credit->status = Model_Credit::STATUS_SUCCESSFUL;
            } else {
                $credit->next_payment_date = $credit->getNextPaymentDate(time());
            }

            $credit->save();

            $this->getAdapter()->commit();

            return $row;
        } catch (Exception $e) {
            $this->getAdapter()->rollBack();

            return false;
        }
    }
}
