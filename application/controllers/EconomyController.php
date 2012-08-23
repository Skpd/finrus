<?php

class EconomyController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $credits = new Model_DbTable_Credits();

        $this->view->total_credits = $credits->select(true)->columns(array('credits_count' => 'COUNT(*)'))->query()->fetch();
        $this->view->total_returns = $credits->select(true)->columns(array('returns_count' => 'COUNT(*)'))->where('status = ?', 'successfull')->query()->fetch();

        $this->view->monthly_credits = $credits->select(true)->columns(array('credits_count' => 'COUNT(*)'))->where('opening_date >= NOW() - INTERVAL 1 MONTH')->query()->fetch();
        $this->view->monthly_returns = $credits->select(true)->columns(array('returns_count' => 'COUNT(*)'))->where('opening_date >= NOW() - INTERVAL 1 MONTH')->where('status = ?', 'successfull')->query()->fetch();

        $this->view->total_debt    = $credits->select(true)->columns(array('debt' => 'SUM(amount)'))->where('status = ?', 'failed')->orWhere('status = ?', 'active')->query()->fetch();
        $this->view->monthly_debts = $credits->select(true)->columns(array('debt' => 'SUM(amount)', 'month' => 'MONTHNAME(opening_date)'))->where('status = ?', 'failed')->orWhere('status = ?', 'active')->group('MONTHNAME(opening_date)')->query()->fetchAll();
    }
}