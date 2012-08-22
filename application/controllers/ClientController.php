<?php

class ClientController extends Zend_Controller_Action
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
        $searchForm = new Form_ClientSearch(array('action' => Zend_Controller_Front::getInstance()->getBaseUrl() . '/default/client/list'));
        $table      = new Model_DbTable_Clients();
        $cities     = new Model_DbTable_Cities();
        $select     = $table->select();

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

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbTableSelect($select));

        $paginator->setItemCountPerPage(intval($values['limit']));
        $paginator->setCurrentPageNumber(intval($values['page']));

        $res = $cities->fetchAll()->toArray();

        $allCities = array();
        foreach ($res as $city) {
            $allCities[$city['id']] = $city['name'];
        }

        $this->view->data = $paginator;
        $this->view->cities = $allCities;
        $this->view->searchForm = $searchForm;
    }

    public function addAction()
    {
        $form = new Form_ClientAdd();

        if ($this->_request->isPost()) {
            if ($this->_request->getPost('cancel')) {
                $this->_helper->redirector()
                        ->setPrependBase(false)
                        ->gotoSimple($this->_helper->url('index', 'client', 'default'));
                return;
            }

            if ($form->isValid($this->_request->getPost())) {
                $table = new Model_DbTable_Clients();

                $values = $form->getValues();

                if ($form->passport_img->isUploaded()) {
                    $info      = $form->passport_img->getFileInfo();
                    $extension = end(explode('/', $info['passport_img']['type']));
                    $filename  = uniqid('passport_') . '.' . $extension;

                    if (copy($form->passport_img->getFileName(), APPLICATION_PATH . '/../public/img/' . $filename)) {
                        $values['passport_img'] = $filename;
                    } else {
                        unset($values['passport_img']);
                    }
                }

                $row = $table->createRow($values);

                $row->save();

                $this->_helper->redirector()
                        ->setPrependBase(false)
                        ->gotoSimple($this->_helper->url('index', 'client', 'default'));
                return;
            }
        }

        $this->view->form = $form;
    }

    public function infoAction()
    {
        $id = $this->_getParam('id');

        $clients = new Model_DbTable_Clients();
        $credits = new Model_DbTable_Credits();

        $client = $clients->find($id)->current();

        if (!empty($client)) {
            $this->view->title   = $client['last_name'] . ' ' . $client['first_name'];
            $this->view->content = $this->view->partial(
                'client/info.phtml',
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
