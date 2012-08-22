<?php

class IndexController extends Zend_Controller_Action
{

    public $ajaxable = array(
        'search' => array('json'),
        'report' => array('json'),
    );

    public function init()
    {
        $this->_helper->ajaxContext()
            ->initContext('json');
    }

    public function reportAction()
    {
        $period = $this->_request->getParam('period', 7);

        if (empty($period)) {
            $period = 7;
        }

        $credits = new Model_DbTable_Credits();

        $this->view->data = $credits->getReport($period);
    }
    
    public function indexAction()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $users = new Model_DbTable_Users();
            $city_id = $users->find(Zend_Auth::getInstance()->getStorage()->read()->id)
                ->current()
                ->findParentRow('Model_DbTable_Affiliates')
                ->findParentRow('Model_DbTable_Cities')
                ->id
            ;

            $select = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());

            $res = $select->from('clients', array('first_name', 'last_name', 'middle_name', 'passport', 'phone'))
                ->where('clients.city_id = ?', $city_id)
                ->where('(DATEDIFF( `credits`.`closing_date`, NOW() ) <= 3) = 1')
                ->join(
                    'credits',
                    "credits.client_id = clients.id AND credits.status='active'",
                    array('remain', 'expired' => '(DATEDIFF( `credits`.`closing_date`, NOW() ) <= 0)', 'id', 'closing_date', 'diff' => 'DATEDIFF( `credits`.`closing_date`, NOW() )')
                )
            ;

            $res = $res->query()->fetchAll();

            $expired = array();
            $active  = array();

            foreach ($res as $row) {
                if ($row['expired']) {
                    $expired[] = $row;
                } else {
                    $active[] = $row;
                }
            }

            $acl = new Finrus_Acl();

            $this->view->isAllowedReport = $acl->isAllowed(
                Zend_Controller_Front::getInstance()->getPlugin('Finrus_Plugin_Acl')->getRole(),
                'index',
                'report'
            );

            $this->view->active  = $active;
            $this->view->expired = $expired;
        }
    }

    public function loginAction()
    {
        $authForm = new Form_Auth();

        if ($this->_request->isPost()) {
            if ($authForm->isValid($this->_request->getParams())) {

                $auth = Zend_Auth::getInstance();

                $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());

                $authAdapter->setTableName('users')
                    ->setIdentityColumn('login')
                    ->setCredentialColumn('password');


                $login = $this->_request->getParam('login', '');
                $passw = $this->_request->getParam('password', '');

                $authAdapter->setIdentity($login);
                $authAdapter->setCredential($passw);
                $authAdapter->setCredentialTreatment('MD5(?) AND active = 1');

                if (!empty($login) && !empty($passw)) {
                    $result = $auth->authenticate($authAdapter);

                    if ($result->isValid()) {
                        $auth->getStorage()->write($authAdapter->getResultRowObject(null, 'password'));
                        $this->_redirect("/");
                    } else {
                        $authForm->addError('Authentication failed.');
                    }
                }
            }
        }

        $this->view->form = $authForm;
    }

    public function searchAction()
    {
        $this->view->clearVars();

        $field = $this->_request->getParam('field', null);
        $value = $this->_request->getParam('q', null);

        $result = array();

        if (!empty($field)) {
            switch ($field) {
                case 'client':
                    $table = new Model_DbTable_Clients();
                    $select = $table->select()
                        ->where("first_name LIKE '%" . $value . "%'")
                        ->orWhere("last_name LIKE '%" . $value . "%'")
                        ->orWhere("middle_name LIKE '%" . $value . "%'")
                        ->orWhere("passport LIKE '%" . $value . "%'");
                    $res = $table->fetchAll($select);

                    if (!empty($res)) {
                        foreach ($res as $row) {
                            $result[] = array(
                                'id'   => $row['id'],
                                'name' => $row['last_name'] . ' ' . $row['first_name'] . ' ' . $row['middle_name']
                            );
                        }
                    }
                    break;

                default:
                    break;
            }
        }

        $this->view->results = $result;
    }

    public function logoutAction()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            Zend_Auth::getInstance()->clearIdentity();
        }
        $this->_redirect("/");
    }
}







