<?php

class UserController extends Zend_Controller_Action
{

    public $ajaxable = array(
        'client-info' => array('json')
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

//    public function opInfoAction()
//    {
//        $id = $this->_getParam('id');
//
//        $users   = new Model_DbTable_Users();
//        $credits = new Model_DbTable_Credits();
//
//        $user = $users->find($id)->current();
//
//        if (!empty($user) && $user['type'] == Finrus_Acl::ROLE_USER) {
//
//        }
//    }

    public function clientInfoAction()
    {
        $id = $this->_getParam('id');

        $clients = new Model_DbTable_Clients();
        $credits = new Model_DbTable_Credits();

        $client = $clients->find($id)->current();

        if (!empty($client)) {
            $this->view->title   = $client['last_name'] . ' ' . $client['first_name'];
            $this->view->content = $this->view->partial(
                'user/client-info.phtml',
                array(
                    'client'        => $client->toArray(),
                    'isBlacklisted' => $client->isBlacklisted(),
                    'credits'       => $credits->fetchAll(array('client_id = ?' => $id), 'id desc')->toArray()
                )
            );
        } else {
            $this->view->title   = 'Ошибка.';
            $this->view->content = 'Клиент не найден.';
        }
    }

    public function deleteAction()
    {

    }

}
