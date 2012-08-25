<?php

class Model_DbTable_Credits extends Zend_Db_Table_Abstract
{
    protected $_name = 'credits';

    protected $_rowClass = 'Model_Credit';

    protected $_referenceMap = array(
        'Clients' => array(
            'columns'           => 'client_id',
            'refTableClass'     => 'Model_DbTable_Clients',
            'refColumns'        => 'id'
        ),
        'Affiliate' => array(
            'columns'           => 'affiliate_id',
            'refTableClass'     => 'Model_DbTable_Affiliates',
            'refColumns'        => 'id'
        ),
    );

    public function getReport($period = 7)
    {
        $merged = array();

        $opened = $this->fetchAll(
            $this->select(false)->from(
                $this->_name,
                array(
                    'opened_sum'    => 'SUM(`origin_amount`)',
                    'credit_opened' => 'COUNT(`client_id`)',
                    'date'          => 'DATE(`opening_date`)'
                ))
                ->where('`opening_date` <= DATE(NOW())')
                ->where('`opening_date` >= DATE(NOW() - INTERVAL ? DAY)', $period)
                ->group('opening_date')
                ->order('opening_date asc')
        )->toArray();

        $payments = new Model_DbTable_Payments();

        $closed = $payments->fetchAll(
            $payments->select(true)->setIntegrityCheck(false)
                ->columns(
                array(
                    'returned_sum'    => 'SUM(`payments`.`amount`)',
                    'credit_returned' => 'COUNT(`credits`.`client_id`)',
                    'date'            => 'DATE(`payments`.`date`)'
                ))
                ->where('`payments`.`date` >= DATE(NOW() - INTERVAL ? DAY)', $period)
                ->joinLeft('credits', 'payments.credit_id = credits.id', array('client_id'))
                ->group('DATE(date)')
                ->order('date asc')
        )->toArray();

        foreach ($opened as $row) {
            $merged[$row['date']]['opened_sum']    = $row['opened_sum'];
            $merged[$row['date']]['credit_opened'] = $row['credit_opened'];
        }

        foreach ($closed as $row) {
            $merged[$row['date']]['returned_sum']    = $row['returned_sum'];
            $merged[$row['date']]['credit_returned'] = $row['credit_returned'];
        }

        $result = array();

        ksort($merged);

        foreach ($merged as $date => $values) {
            $result[] = array_merge($values, array('date' => $date));
        }


        return $result;
    }

    public function create(array $values)
    {
        $clients = new Model_DbTable_Clients();

        $client = $clients->find($values['client_id'])->current();

        if (empty($client)) {
            throw new Zend_Exception('Клиент не найден.');
        }

        $active = $this->select()
            ->where('client_id = ?', $values['client_id'])
            ->where("status='active' OR status='failed'")
            ->query()
            ->fetchColumn();

        if (!empty($active)) {
            throw new Zend_Exception('У данного клиента уже есть активный кредит.');
        }

        if (isset($values['opening_date'])) {
            $values['opening_date'] = date('Y-m-d', strtotime($values['opening_date']));
        } else {
            $values['opening_date'] = date('Y-m-d', time());
        }

        $isFirstCredit   = true;
        $isTrustedClient = false;

        if (null !== $this->fetchRow(array('client_id = ?' => $values['client_id'], "status = 'successfull'"))) {
            $isFirstCredit = false;
        }

        $select = $this->select(true)
            ->columns(
            array(
                'credits_count' => 'SUM( id )',
                'more_5k'       => 'SUM( origin_amount >= 5000 )'
            )
        )
            ->where('client_id = ?', $values['client_id'])
            ->where('status = ?', 'successfull')
            ->group('client_id')
            ->having('credits_count >= ?', 5)
            ->having('more_5k >= ?', 2);

        if ($select->query()->columnCount() > 0) {
            $isTrustedClient = true;
        }

        $values['origin_amount'] = $values['amount'];

        $values['amount'] = 0;

        if ($isFirstCredit) {
            if ($values['origin_amount'] <= 5000) {
                switch ($values['duration']) {
                    case 7:
                        $values['amount'] = $values['origin_amount'] + $values['origin_amount'] * 0.09;
                        break;
                    case 14:
                        $values['amount'] = $values['origin_amount'] + $values['origin_amount'] * 0.18;
                        break;
                    case 21:
                        $values['amount'] = $values['origin_amount'] + $values['origin_amount'] * 0.27;
                        break;
                    case 30:
                        $values['amount'] = $values['origin_amount'] + $values['origin_amount'] * 0.32;
                        break;

                    default:
                        break;
                }
            }
        } else {
            if ($values['origin_amount'] <= 5000) {
                switch ($values['duration']) {
                    case 7:
                        $values['amount'] = $values['origin_amount'] + $values['origin_amount'] * 0.08;
                        break;
                    case 14:
                        $values['amount'] = $values['origin_amount'] + $values['origin_amount'] * 0.16;
                        break;
                    case 21:
                        $values['amount'] = $values['origin_amount'] + $values['origin_amount'] * 0.22;
                        break;
                    case 30:
                        $values['amount'] = $values['origin_amount'] + $values['origin_amount'] * 0.30;
                        break;

                    default:
                        break;
                }
            } else if ($values['origin_amount'] <= 10000) {
                switch ($values['duration']) {
                    case 7:
                        $values['amount'] = $values['origin_amount'] + $values['origin_amount'] * 0.07;
                        break;
                    case 14:
                        $values['amount'] = $values['origin_amount'] + $values['origin_amount'] * 0.15;
                        break;
                    case 21:
                        $values['amount'] = $values['origin_amount'] + $values['origin_amount'] * 0.20;
                        break;
                    case 30:
                        $values['amount'] = $values['origin_amount'] + $values['origin_amount'] * 0.27;
                        break;

                    default:
                        break;
                }
            } else if ($values['origin_amount'] <= 20000 && $isTrustedClient) {
                switch ($values['duration']) {
                    case 30:
                        $values['amount'] = $values['origin_amount'] + $values['origin_amount'] * 0.15;
                        break;
                    default:
                        break;
                }
            }
        }

        if (empty($values['amount'])) {
            throw new Zend_Exception('Сумма указана неверно.');
        }

        $values['closing_date'] = time() + (60 * 60 * 24 * $values['duration']);

        if (isset($values['closing_date'])) {
            if (!is_numeric($values['closing_date'])) {
                $values['closing_date'] = strtotime($values['closing_date']);
            }

            $values['closing_date'] = date('Y-m-d', $values['closing_date']);
        }

        if (!isset($values['remain'])) {
            $values['remain'] = $values['amount'];
        }

        $users = new Model_DbTable_Users();
        $values['affiliate_id'] = $users->find(Zend_Auth::getInstance()->getStorage()->read()->id)
            ->current()
            ->findParentRow('Model_DbTable_Affiliates')
            ->id
        ;

        $row = $this->createRow($values);

        $row->save();

        return $row;
    }
}
