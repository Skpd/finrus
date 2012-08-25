<?php

class Model_Credit extends Zend_Db_Table_Row_Abstract
{
    const STATUS_SUCCESSFUL = 'successfull';
    const STATUS_ACTIVE     = 'active';
    const STATUS_FAILED     = 'failed';

    public function isOverdue()
    {
        return $this->_data['status'] != self::STATUS_SUCCESSFUL && $this->_data['closing_date'] < date('Y-m-d');
    }

    public function recalculate()
    {
        if ($this->isOverdue()) {
            $penalty_days = floor((time() - strtotime($this->_data['closing_date'])) / 3600 / 24);

            if ($penalty_days < 30) {
                $this->remain += $this->remain * 0.02 * $penalty_days / 2;
            } else {
                $this->remain += $this->remain * 0.02 * $penalty_days;
            }

            $this->status = self::STATUS_FAILED;

            $this->save();
        }
    }

    public function getYesterdayPayments()
    {
        return $this->findDependentRowset('Model_DbTable_Payments', null, $this->select()->where('date >= ?', date('Y-m-d', time())));
    }
}