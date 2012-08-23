<?php

class UserController extends Zend_Controller_Action
{

    public $ajaxable = array(
        'info' => array('json')
    );

    public function init()
    {
        $this->_helper->ajaxContext()->initContext('json');
    }

    public function indexAction()
    {
        $this->_forward('list');
    }

    public function listAction()
    {
        $searchForm = new Form_UserSearch();
        $select     = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());

        $select->from('users');

        $values = $searchForm->getValidValues($this->_request->getParams());

        if (!empty($values['field']) && !empty($values['value'])) {
            $select->where($values['field'] . ' = ?', $values['value']);
        }

        if (!empty($values['sort']) && !empty($values['direction'])) {
            $values['direction'] = $values['direction'] == 'asc' ? 'asc' : 'desc';
            $select->order($values['sort'] . ' ' . $values['direction']);
        }

        if (!isset($values['limit'])) {
            $values['limit'] = 30;
        }

        $values['page'] = empty($values['page']) ? 1 : intval($values['page']);

        $select->join('affiliates', "users.affiliate_id = affiliates.id", array('name'));

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));

        $paginator->setItemCountPerPage(intval($values['limit']));
        $paginator->setCurrentPageNumber(intval($values['page']));

        $this->view->data       = $paginator;
        $this->view->searchForm = $searchForm;
    }

    public function addAction()
    {
        $form = new Form_UserAdd();

        if ($this->_request->isPost()) {
            if ($this->_request->getPost('cancel')) {
                $this->_helper->redirector()
                    ->setPrependBase(false)
                    ->gotoSimple($this->_helper->url('index', 'user', 'default'));
                return;
            }

            if ($form->isValid($this->_request->getPost())) {
                $table = new Model_DbTable_Users();
                $row   = $table->createRow($form->getValues());

                $row['password'] = md5($row['password']);

                $row->save();
                $this->_helper->redirector()
                    ->setPrependBase(false)
                    ->gotoSimple($this->_helper->url('index', 'user', 'default'));
                return;
            }
        }

        $this->view->form = $form;
    }

    public function statusAction()
    {
        $disable = $this->_request->getParam('disable');
        $id      = $this->_request->getParam('id');
        $table   = new Model_DbTable_Users();

        $user = $table->find($id)->current();

        if (!empty($user)) {
            if (!empty($disable)) {
                $user['active'] = 0;
            } else {
                $user['active'] = 1;
            }

            $user->save();
        }

        $this->_helper->redirector()
            ->setPrependBase(false)
            ->gotoSimple($this->_helper->url('index', 'user', 'default'));
    }

    public function infoAction()
    {
        $id = $this->_getParam('id');

        $users    = new Model_DbTable_Users();
        $payments = new Model_DbTable_Payments();

        $user = $users->find($id)->current();

        if (!empty($user)) {

            $select = $payments->select(true)->setIntegrityCheck(false)
                ->columns(array('payment_amount' => 'amount'))
                ->where('user_id = ?', $user['id'])
                ->join('credits', 'credits.id = payments.credit_id', array('status', 'credit_amount' => 'amount'))
                ->group('payments.id');

            $total = $select->query()->fetchAll();

            $total['credit_count'] = count($total);

            foreach ($total as $k => $row) {
                $total['credit_sum'] += $row['credit_amount'];

                if ($row['status'] == 'successfull') {
                    $total['returned_count']++;
                }
            }

            $total['returned_percent'] = $total['returned_count'] / $total['credit_count'] * 100;


            $select->where('payments.date >= NOW() - INTERVAL 1 MONTH');


            $monthly = $select->query()->fetchAll();

            $monthly['credit_count'] = count($monthly);

            foreach ($monthly as $k => $row) {
                $monthly['credit_sum'] += $row['credit_amount'];

                if ($row['status'] == 'successfull') {
                    $monthly['returned_count']++;
                }
            }

            $monthly['returned_percent'] = $monthly['returned_count'] / $monthly['credit_count'] * 100;

            $this->view->title   = $user['login'];
            $this->view->content = $this->view->partial(
                'user/info.phtml',
                array(
                    'user'    => $user->toArray(),
                    'total'   => $total,
                    'monthly' => $monthly
                )
            );
        } else {
            $this->view->title   = 'Ошибка.';
            $this->view->content = 'Оператор не найден.';
        }
    }

    public function deleteAction()
    {

    }

}
