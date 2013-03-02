<?php

class PaymentController extends Zend_Controller_Action
{

    public function indexAction()
    {
        $this->_forward('list');
    }

    public function listAction()
    {

        $searchForm = new Form_PaymentSearch();
        $select     = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());

        $select->from('payments');

        $values = $searchForm->getValidValues($this->_request->getParams());

        if (!empty($values['field']) && !empty($values['value'])) {
            $select->where('payments.' . $values['field'] . ' = ?', $values['value']);
        }

        if (!empty($values['sort']) && !empty($values['direction'])) {
            $values['direction'] = $values['direction'] == 'asc' ? 'asc' : 'desc';
            $select->order('payments.' . $values['sort'] . ' ' . $values['direction']);
        } else {
            $values['sort']      = 'id';
            $values['direction'] = 'desc';

            $select->order('payments.' . $values['sort'] . ' ' . $values['direction']);
        }

        if (!isset($values['limit'])) {
            $values['limit'] = 30;
        }

        $values['page'] = empty($values['page']) ? 1 : intval($values['page']);

        $select->join('users', "payments.user_id = users.id", array('login'));

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));

        $paginator->setItemCountPerPage(intval($values['limit']));
        $paginator->setCurrentPageNumber(intval($values['page']));

        $this->view->data       = $paginator;
        $this->view->searchForm = $searchForm;
    }

    public function addAction()
    {
        $form = new Form_PaymentAdd();

        if ($this->_request->isPost()) {
            if ($this->_request->getPost('cancel')) {
                $this->_helper->redirector()
                    ->setPrependBase(false)
                    ->gotoSimple($this->_helper->url('index', 'payment', 'default'));

                return;
            }

            if ($form->isValid($this->_request->getPost())) {
                $values   = $form->getValues();
                $result   = false;
                $payments = new Model_DbTable_Payments();

                switch ($values['type']) {
                    case 'payment':
                        $result = $payments->create($values);
                        break;

                    case 'reopen':
                        $result = $payments->reopen($values);
                        break;

                    case 'other':
                        $result = $payments->create($values, true);
                        break;

                    default:
                        $form->addError('Не указан тип платежа.');
                        break;
                }

                if ($result !== false) {
                    $this->view->payment_id = $result['id'];
                    $this->view->redirect   = true;
                } else {
                    $form->addError('Ошибка при совершении платежа.');
                }
            }
        }

        $this->view->form = $form;
    }

    public function deleteAction()
    {

    }

    public function printAction()
    {
        $id = $this->_request->getParam('id', 0);

        $table = new Model_DbTable_Payments();

        $payment = $table->find($id)->current();

        if (!empty($payment)) {
            $credit = $payment->findParentRow('Model_DbTable_Credits');
            $client = $credit->findParentRow('Model_DbTable_Clients');


            $this->view->client  = $client;
            $this->view->credit  = $credit;
            $this->view->payment = $payment;

            $this->getHelper('layout')->disableLayout();

        } else {
            $this->_forward('list');

            return;
        }
    }

}
