<?php

class EconomyController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $credits = new Model_DbTable_Credits();

        $this->view->total_credits = $credits->select(true)->columns(array('credits_count' => 'COUNT(*)'))->query()
            ->fetch();

        $this->view->total_returns = $credits->select(true)->columns(array('returns_count' => 'COUNT(*)'))
            ->where('status = ?', 'successfull')->query()->fetch();

        $this->view->monthly_credits = $credits->select(true)->columns(array('credits_count' => 'COUNT(*)'))
            ->where('opening_date >= NOW() - INTERVAL 1 MONTH')->query()->fetch();

        $this->view->monthly_returns = $credits->select(true)->columns(array('returns_count' => 'COUNT(*)'))
            ->where('opening_date >= NOW() - INTERVAL 1 MONTH')->where('status = ?', 'successfull')->query()->fetch();

        $this->view->total_debt = $credits->select(true)->columns(array('debt' => 'SUM(amount)'))
            ->where('status = ?', 'failed')->orWhere('status = ?', 'active')->query()->fetch();

        $this->view->total_active_debt = $credits->select(true)->columns(array('debt' => 'SUM(amount)'))
            ->where('status = ?', 'active')->query()->fetch();

        $this->view->monthly_debts = $credits->select(true)->
            columns(
            array(
                'debt'  => 'SUM(amount)',
                'month' => 'MONTHNAME(opening_date)'
            )
        )->
            where('status = ?', 'failed')->
            group('MONTHNAME(opening_date)')->
            order('opening_date DESC')->
            query()->fetchAll();
    }

    public function targetHistoryAction()
    {
        //TODO: remove hardcoded affiliate id
        $affiliate_id = 1;

        switch ($this->_getParam('group')) {
            case 'year':
                $groupFunction = 'YEAR';
                $group = 'year';
                break;

            case 'month':
                $groupFunction = 'MONTH';
                $group = 'month';
                break;

            default:
            case 'day':
                $groupFunction = '';
                $group = 'day';
                break;
        }

        $affiliateHistory = new Model_DbTable_AffiliateHistory();

        $select = $affiliateHistory->select(true)
            ->columns(
            array(
                'sum'  => 'SUM(target)',
                'group_date' => $groupFunction . '(date)',
                'date'
            )
        )->where('affiliate_id = ?', $affiliate_id)
        ->group($groupFunction . '(date)')
        ->order('date DESC')
        ;

        $limit = 30;

        $page = $this->_getParam('page', 1);

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbTableSelect($select));

        $paginator->setItemCountPerPage(intval($limit));
        $paginator->setCurrentPageNumber(intval($page));

        $this->view->group = $group;
        $this->view->data = $paginator;
    }
}