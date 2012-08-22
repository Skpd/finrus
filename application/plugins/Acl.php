<?php

class Finrus_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var null|\Panasonic_Acl
     */
    private $_acl = null;
    private $_resource = null;
    private $_role = null;

    public function __construct(Zend_Acl $acl)
    {
        $this->_acl = $acl;
        
        $this->_role = (Zend_Auth::getInstance()->hasIdentity())
            ? Zend_Auth::getInstance()->getStorage()->read()->type
            : Finrus_Acl::ROLE_GUEST;
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_resource = $request->getControllerName();
        
        $action = $request->getActionName();
        
        if (!$this->_acl->has($this->_resource) || !$this->_acl->isAllowed($this->_role, $this->_resource, $action)) {
            if ($this->_role == Finrus_Acl::ROLE_GUEST) {
                $request->setModuleName('default')
                    ->setControllerName('index')
                    ->setActionName('login');
            } else {
                $request->setModuleName('default')
                    ->setControllerName('error')
                    ->setActionName('denied');
            }
        }
    }

    public function isAllowed($action = null, $resource = null, $role = null)
    {
        if ($action === null) {
            $action = $this->_request->getActionName();
        }

        if ($resource === null) {
            $resource = $this->_request->getControllerName();
        }

        
        
        if ($role === null) {
            $role = Zend_Auth::getInstance()->hasIdentity()
                ? Zend_Auth::getInstance()->getStorage()->read()->type
                : Finrus_Acl::ROLE_GUEST;
        }

        if (!$this->_acl->has($resource) || !$this->_acl->isAllowed($role, $resource, $action)) {
            return false;
        } else {
            return true;
        }
    }

    public function getRole()
    {
        return $this->_role;
    }

    public function setRole($role)
    {
        $this->_role = $role;

        return $this;
    }

    public function getResource()
    {
        return $this->_resource;
    }

    public function setResource($resource)
    {
        $this->_resource = $resource;

        return $this;
    }

    /**
     * @return null|\Finrus_Acl
     */
    public function getAcl()
    {
        return $this->_acl;
    }

    /**
     * @param null|Finrus_Acl $acl
     * @return Finrus_Plugin_Acl
     */
    public function setAcl($acl)
    {
        $this->_acl = $acl;

        return $this;
    }
}

?>