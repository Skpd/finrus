<?php

class SettingsController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $affiliates = new Model_DbTable_Affiliates();
        $citiesTable = new Model_DbTable_Cities();
        $cities = array();

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
            $form   = new Form_AffiliateAdd();
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
}