<?php

class CreditController extends Zend_Controller_Action
{

    public $ajaxable = array(
        'get-active' => array('json'),
    );

    public function init()
    {
        $this->_helper->ajaxContext()
            ->initContext('json');
    }

    public function indexAction()
    {
        $this->_forward('list');
    }

    public function listAction()
    {
        $searchForm = new Form_CreditSearch();
        $select     = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());

        $select->from('credits');

        $values = $searchForm->getValidValues($this->_request->getParams());

        if (!empty($values['field']) && !empty($values['value'])) {
            if ($values['field'] == 'client') {
                $select->where('clients.last_name = ?', $values['value']);
            }
        }

        if (!empty($values['sort']) && !empty($values['direction'])) {
            if ($values['sort'] == 'last_name') {
                $select->order('clients.' . $values['sort'] . ' ' . $values['direction']);
            } else {
                $select->order('credits.' . $values['sort'] . ' ' . $values['direction']);
            }
        } else {
            $values['sort']      = 'id';
            $values['direction'] = 'desc';

            $select->order('credits.' . $values['sort'] . ' ' . $values['direction']);
        }

        if (!isset($values['limit'])) {
            $values['limit'] = 30;
        }

        $values['page'] = empty($values['page']) ? 1 : intval($values['page']);

        $select->join(
            'clients',
            "credits.client_id = clients.id",
            array(
                'first_name', 'last_name', 'middle_name', 'client_id' => 'id'
            )
        );

        $select->joinLeft(
            'users',
            "credits.user_id = users.id",
            array(
                'login'
            )
        );

        $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));

        $paginator->setItemCountPerPage(intval($values['limit']));
        $paginator->setCurrentPageNumber(intval($values['page']));

        $this->view->data       = $paginator;
        $this->view->searchForm = $searchForm;
    }

    public function getActiveAction()
    {
        $this->view->clearVars();

        $clientId = $this->_request->getParam('id', 0);

        $credits = new Model_DbTable_Credits();
        try {
            $credit  = $credits->getActive($clientId);
            $this->view->credit = $credit->toArray();
        } catch (Exception $e) {
            $this->view->credit = null;
        }
    }

    public function addAction()
    {
        $form = new Form_CreditAdd();

        if ($this->_request->isPost()) {
            if ($this->_request->getPost('cancel')) {
                $this->_helper->redirector()
                    ->setPrependBase(false)
                    ->gotoSimple($this->_helper->url('index', 'credit', 'default'));

                return;
            }

            if ($form->isValid($this->_request->getPost())) {
                $table = new Model_DbTable_Credits();

                try {
                    $result                = $table->create($form->getValues());
                    $this->view->credit_id = $result['id'];
                    $this->view->redirect  = true;
                } catch (Zend_Exception $e) {
                    $form->addError($e->getMessage());
                }
            }
        }

        $this->view->form = $form;
    }

    public function closeAction()
    {
        $credit_id = $this->_request->getParam('credit', 0);

        $credits = new Model_DbTable_Credits();

        $credit = $credits->find($credit_id)->current();

        if (!empty($credit)) {
            $credit->status = 'failed';
            $credit->save();
        }

        $this->_forward('index', 'index');

        return;
    }

    public function printAction()
    {
        $id = $this->_request->getParam('id', 0);

        $table = new Model_DbTable_Credits();

        $credit = $table->find($id)->current();

        if (!empty($credit)) {
            $client = $credit->findParentRow('Model_DbTable_Clients');

            $this->view->client = $client;
            $this->view->credit = $credit;

            $this->getHelper('layout')->disableLayout();
        } else {
            $this->_forward('list');

            return;
        }
    }

    public function agreementAction()
    {
        $id = $this->_request->getParam('id', 0);

        $table = new Model_DbTable_Credits();

        $credit = $table->find($id)->current();
        /* @var $credit = Model_Credit */

        if (!empty($credit)) {
            $client = $credit->findParentRow('Model_DbTable_Clients');

            $fname = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'agr_new.pdf';

            $pdf  = new Zend_Pdf(file_get_contents($fname));
            $font = Zend_Pdf_Font::fontWithPath(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'templates/times.ttf');
            $page = $pdf->pages[0];
            $page->setFont($font, 14);

            $ts        = strtotime($credit['opening_date']);
            $tsClosing = strtotime($credit['closing_date']);

            $page->drawText($credit['id'], 360, 665);

            $page->setFont($font, 10);

            $page->drawText(date('d', $ts), 432, 624);
            $page->drawText(strftime('%B', $ts), 470, 624);
            $page->drawText(substr(date('y', $ts), 1), 536, 623);

            $page->drawText($client['last_name'] . ' ' . $client['first_name'] . ' ' . $client['middle_name'], 366, 555, 'UTF-8');

            $page->drawText(Finrus_Utils::num2str($credit['origin_amount']), 60, 445, 'UTF-8');
            $page->drawText($credit['origin_amount'], 360, 445);

            $page->drawText(date('d', $tsClosing), 224, 430);
            $page->drawText(strftime('%B', $tsClosing), 260, 430);
            $page->drawText(substr(date('y', $tsClosing), 1), 334, 430);

            //$page->drawText('xxxx xxxxx xxxx xxxx xxxx', 60, 375);

            $page = $pdf->pages[1];
            $page->setFont($font, 12);

            $page->drawText($credit['amount'], 390, 472);

            $page = $pdf->pages[2];
            $page->setFont($font, 12);

            $page->drawText($client['passport'], 120, 482);

            $page->drawText($client['address'], 170, 462, 'UTF-8');
            $page->drawText($client['address'], 170, 442, 'UTF-8');

            $page = $pdf->pages[3];
            $page->setFont($font, 12);

            $page->drawText($credit['id'], 360, 782);
            $page->drawText($client['last_name'] . ' ' . $client['first_name'] . ' ' . $client['middle_name'] . ' и ООО "Фин Русь"', 230, 755, 'UTF-8');

            $page->drawText(date('d', $ts), 364, 713);
            $page->drawText(strftime('%B', $ts), 395, 713);
            $page->drawText(substr(date('y', $ts), 1), 466, 713);

            $time  = strtotime($credit['opening_date']);
            $close = strtotime($credit['closing_date']);
            $dates = array();
            $pos = 633;

            while ($time < $close) {
                $time = strtotime($credit->getNextPaymentDate($time));
                $dates[] = date('Y-m-d', $time);
            }

            $payment = round($credit['amount'] / sizeof($dates));
            $remain  = $credit['amount'];

            foreach ($dates as $k => $date) {
                $remain -= $payment;

                if ($k + 1 == sizeof($dates)) {
                    $payment += $remain;
                    $remain = 0;
                }

                $page->drawText($date, 100, $pos);
                $page->drawText($payment, 300, $pos);
                $page->drawText($remain, 470, $pos);

                $pos -= 14.3;
            }

            $page->drawText($credit['id'], 295, 92);

            $page = $pdf->pages[4];
            $page->setFont($font, 12);

            $page->drawText($client['passport'], 120, 593);

            $page->drawText($client['address'], 170, 573, 'UTF-8');
            $page->drawText($client['address'], 170, 553, 'UTF-8');

            $this->_response->setHeader('content-type', 'application/pdf');

            $this->_response->setBody($pdf->render());

            $this->getHelper('layout')->disableLayout();
            $this->getHelper('viewRenderer')->setNoRender();

        } else {
            $this->_forward('list');

            return;
        }
    }

    public function deleteAction()
    {

    }

}
