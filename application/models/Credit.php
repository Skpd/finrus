<?php

/**
 * @property int id
 * @property int client_id
 * @property int amount
 * @property int origin_amount
 * @property int remain
 * @property datetime last_payment_date
 * @property datetime next_payment_date
 * @property datetime opening_date
 * @property datetime closing_date
 * @property string status
 * @property int affiliate_id
 * @property int user_id
 */
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
            $remain = $this->amount;

            $payments = $this->findDependentRowset('Model_DbTable_Payments');

            foreach ($payments as $payment) {
                $remain -= $payment['amount'];
            }

            $penalty_days = floor((time() - strtotime($this->_data['closing_date'])) / 3600 / 24);

            if ($penalty_days < 30) {
                $this->remain = $remain + $remain * 0.02 * $penalty_days / 2;
            } else {
                $this->remain = $remain + $remain * 0.02 * $penalty_days;
            }

            $this->status = self::STATUS_FAILED;

            $this->save();
        }
    }

    public function getPaymentsDaysAgo($days)
    {
        return $this->findDependentRowset('Model_DbTable_Payments', null, $this->select()->where('date >= DATE(NOW() - INTERVAL ? DAY)', $days));
    }

    protected function _insert()
    {
        if (empty($this->user_id)) {
            $this->user_id = Zend_Auth::getInstance()->getStorage()->read()->id;
        }
    }
}