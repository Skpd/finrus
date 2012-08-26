<?php

class SettingsController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $affiliates  = new Model_DbTable_Affiliates();
        $citiesTable = new Model_DbTable_Cities();
        $cities      = array();

        $res = $citiesTable->fetchAll()->toArray();

        foreach ($res as $v) {
            $cities[$v['id']] = $v;
        }

        $this->view->affiliates = $affiliates->fetchAll()->toArray();
        $this->view->cities     = $cities;

        $this->view->cityForm      = new Form_CityAdd();
        $this->view->affiliateForm = new Form_AffiliateAdd();
    }

    public function saveAction()
    {
        $type = $this->_request->getParam('type');
        $id   = $this->_request->getParam('id');

        if ($type == 'city') {
            $form  = new Form_CityAdd();
            $table = new Model_DbTable_Cities();
        } else {
            $form  = new Form_AffiliateAdd();
            $table = new Model_DbTable_Affiliates();
        }

        $row = $table->find($id)->current();

        if (!empty($row) && $form->isValid($this->_request->getPost())) {
            $row->setFromArray($form->getValues());
            $row->save();
        }

        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
    }

    public function createAction()
    {
        $type = $this->_request->getParam('type');

        if ($type == 'city') {
            $form  = new Form_CityAdd();
            $table = new Model_DbTable_Cities();
        } else {
            $form  = new Form_AffiliateAdd();
            $table = new Model_DbTable_Affiliates();
        }

        if ($form->isValid($this->_request->getPost())) {
            $row = $table->createRow($form->getValues());
            $row->save();
        }

        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender();
    }

    public function recalculateCreditsAction()
    {
        $affiliate_id = $this->_getParam('affiliate', 0);

        $affiliates = new Model_DbTable_Affiliates();

        $affiliate = $affiliates->find($affiliate_id)->current();

        if (!empty($affiliate)) {

            $activeCredits = $affiliate->findDependentRowset('Model_DbTable_Credits');
            foreach ($activeCredits as $credit) {
                $credit->recalculate();
            }

            $recalculateDate = new Zend_Date(strtotime($affiliate['recalculate_date']));
            $currentDate     = new Zend_Date(date('Y-m-d'));

            while ($recalculateDate->compare($currentDate) == -1) {
                $select = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());

                $select->from(array('c' => 'credits'), array('diff' => 'SUM(p.amount) - c.origin_amount'))
                    ->join(array('p' => 'payments'), 'c.id = p.credit_id', null)
                    ->where('c.remain <= 0')
                    ->where('p.date >= ?', $recalculateDate->toString('yyyy-MM-dd') . ' 00:00:00')
                    ->where('p.date <= ?', $recalculateDate->toString('yyyy-MM-dd') . ' 23:59:59')
                    ->where('c.affiliate_id = ?', $affiliate_id)
                    ->group('c.id');

                $diffs = $select->query()->fetchAll();

                foreach ($diffs as $row) {
                    $affiliate->current_target += $row['diff'];
                }

                $affiliate->current_target -= $affiliate->target;

                $recalculateDate->add(1, Zend_Date::DAY);
            }

            $affiliate->recalculate_date = date('Y-m-d');
            $affiliate->save();
        }

        $this->_redirect(
            $this->view->url(array('controller' => 'settings', 'action' => 'index'), 'default', true),
            array(
                'prependBase' => false
            )
        );
    }
}