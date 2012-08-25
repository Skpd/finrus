<?php

class CommentsController extends Zend_Controller_Action
{
    /**
     * @var Model_DbTable_Comments
     */
    private $_comments;

    public $ajaxable = array(
//        'add' => array('json')
    );

    public function init()
    {
        $this->_comments = new Model_DbTable_Comments();
        $this->_helper->ajaxContext()->initContext('json');
    }

    public function listAction()
    {
        $conversation = $this->_getParam('conversation', null);

        $this->view->conversation = $conversation;
        $this->view->comments     = $this->_comments->getByConversationId($conversation);

        $this->getHelper('layout')->disableLayout();
    }

    public function addAction()
    {
        $conversation = $this->_getParam('conversation', null);

        $form = new Form_CommentAdd();
        $form->getElement('conversation')->setValue($conversation);

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $row = $this->_comments->createRow($form->getValues());

            $row['user_id'] = Zend_Auth::getInstance()->getIdentity()->id;
            $row['created'] = date('Y-m-d H:i:s');

            $row->save();

            $this->_redirect($_SERVER['HTTP_REFERRER']);
        }

        $this->view->form = $form;

        $this->getHelper('layout')->disableLayout();
    }
}