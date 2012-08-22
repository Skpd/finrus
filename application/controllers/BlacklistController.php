<?php

class BlacklistController extends Zend_Controller_Action
{

    public $ajaxable = array(
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
        $searchForm = new Form_ClientSearch(array('action' => Zend_Controller_Front::getInstance()->getBaseUrl() . '/default/blacklist/list'));
        $table      = new Model_DbTable_BlackList();
        $select     = $table->select(true)->setIntegrityCheck(false)->join('clients', 'clients.id = black_list.client_id');

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

        $this->view->data       = $paginator;
        $this->view->searchForm = $searchForm;
    }

    public function changeAction()
    {
        $id     = $this->_getParam('id');
        $status = $this->_getParam('status');

        $table = new Model_DbTable_BlackList();

        $table->delete(array('client_id = ?' => intval($id)));

        if ($status == 'true') {
            $table->insert(array(
                'client_id' => intval($id)
            ));
        }
    }

}
