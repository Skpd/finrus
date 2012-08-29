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

        $affiliates       = new Model_DbTable_Affiliates();
        $affiliateHistory = new Model_DbTable_AffiliateHistory();

        $affiliate = $affiliates->find($affiliate_id)->current();

        if (!empty($affiliate)) {

            $activeCredits = $affiliate->findDependentRowset('Model_DbTable_Credits');
            foreach ($activeCredits as $credit) {
                $credit->recalculate();
            }

            if (empty($affiliate['recalculate_date'])) {
                $firstPayment = $affiliate->findManyToManyRowset(
                    'Model_DbTable_Payments', 'Model_DbTable_Users', null, null,
                    $affiliates->select()->order('date ASC')->limit(1)
                )->current();

                $affiliate['recalculate_date'] = date('Y-m-d', strtotime($firstPayment['date']));
            }

            $recalculateDate = new Zend_Date(strtotime($affiliate['recalculate_date']));
            $currentDate     = new Zend_Date(date('Y-m-d'));

            while ($recalculateDate->compare($currentDate) == -1) {
                $dayHistory = $affiliate->findDependentRowset(
                    'Model_DbTable_AffiliateHistory',
                    null,
                    $affiliateHistory->select()->where('date = ?', $recalculateDate->toString('yyyy-MM-dd'))
                )->current();

                if (empty($dayHistory)) {
                    $dayHistory = $affiliateHistory->createRow(
                        array(
                            'affiliate_id' => $affiliate['id'],
                            'date'         => $recalculateDate->toString('yyyy-MM-dd')
                        )
                    );
                }

                $select = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());

                $select->from(array('c' => 'credits'), array('diff' => 'SUM(p.amount) - c.origin_amount'))
                    ->join(array('p' => 'payments'), 'c.id = p.credit_id', null)
                    ->where('c.remain <= 0')
                    ->where('p.date >= ?', $recalculateDate->toString('yyyy-MM-dd') . ' 00:00:00')
                    ->where('p.date <= ?', $recalculateDate->toString('yyyy-MM-dd') . ' 23:59:59')
                    ->where('c.affiliate_id = ?', $affiliate_id)
                    ->group('c.id');

                $diffs = $select->query()->fetchAll();

                $dayHistory['target'] = 0;

                foreach ($diffs as $row) {
                    $dayHistory['target'] += $row['diff'];
                }

                $dayHistory['target'] -= $affiliate['target'];

                $dayHistory->save();

                $recalculateDate->add(1, Zend_Date::DAY);
            }

            $select = $affiliateHistory->select(false)->setIntegrityCheck(false)
                ->from(
                    array('ah' => $affiliateHistory->info(Zend_Db_Table::NAME)),
                    array('sum' => 'SUM(ah.target)')
                )
                ->join(array('a' => $affiliates->info(Zend_Db_Table::NAME)), 'ah.affiliate_id = a.id', null)
                ->where('affiliate_id = ?', $affiliate['id']);

            $affiliate['current_target'] = $select->query()->fetchColumn();

            $affiliate['recalculate_date'] = date('Y-m-d');
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