<?php

class Model_Credit extends Zend_Db_Table_Row_Abstract
{
    public function getAmount()
    {
        $amount = $this->_data['amount'];

        if ($this->_data['closing_date'] < time()) {
            $penalty_days = (time() - strtotime($this->_data['closing_date'])) / 3600;

            if ($penalty_days < 30) {
                $amount += $this->_data['origin_amount'] * 0.02 * $penalty_days / 2;
            } else {
                $amount += $this->_data['origin_amount'] * 0.02 * $penalty_days / 2;
            }
        }

        return $amount;
    }
}