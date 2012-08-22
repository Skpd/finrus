<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    public function _initAutoloader() {
        $loader = new Zend_Application_Module_Autoloader(array(
            'namespace' => '',
            'basePath' => dirname(__FILE__)
        ));

        return $loader;
    }

    public function _initAcl() {
        $acl = new Finrus_Acl();

        Zend_Controller_Front::getInstance()->registerPlugin(
            new Finrus_Plugin_Acl($acl)
        );

        return $acl;
    }

    public function _initNavigation() {
        $pages = array(
            array(
                'module'     => 'default',
                'controller' => 'index',
                'action'     => 'index',
                'label'      => 'Главная',
                'resource'   => 'index',
                'privilege'  => 'view'
            ),
            array(
                'module'     => 'default',
                'controller' => 'credit',
                'action'     => 'index',
                'label'      => 'Кредиты',
                'resource'   => 'credit',
                'privilege'  => 'view'
            ),
            array(
                'module'     => 'default',
                'controller' => 'payment',
                'action'     => 'index',
                'label'      => 'Платежи',
                'resource'   => 'payment',
                'privilege'  => 'view'
            ),
            array(
                'module'     => 'default',
                'controller' => 'client',
                'action'     => 'index',
                'label'      => 'Клиенты',
                'resource'   => 'client',
                'privilege'  => 'view'
            ),
            array(
                'module'     => 'default',
                'controller' => 'user',
                'action'     => 'index',
                'label'      => 'Операторы',
                'resource'   => 'user',
                'privilege'  => 'view'
            ),
            array(
                'module'     => 'default',
                'controller' => 'blacklist',
                'action'     => 'index',
                'label'      => 'Черный Список',
                'resource'   => 'blacklist',
                'privilege'  => 'view'
            ),
            array(
                'module'     => 'default',
                'controller' => 'settings',
                'action'     => 'index',
                'label'      => 'Настройки',
                'resource'   => 'settings',
                'privilege'  => 'view'
            ),
            array(
                'module'     => 'default',
                'controller' => 'index',
                'action'     => 'login',
                'label'      => 'Войти',
                'resource'   => 'index',
                'privilege'  => 'login'
            ),
            array(
                'module'     => 'default',
                'controller' => 'index',
                'action'     => 'logout',
                'label'      => 'Выйти',
                'resource'   => 'index',
                'privilege'  => 'logout'
            ),
        );

        $container = new Zend_Navigation($pages);

        return $container;
    }

    public function _initExtendedView() {
        if (!$this->hasResource('View')) {
            $this->bootstrap('view');
        }

        $view = $this->getResource('View');

        $view->navigation()
                ->setAcl($this->getResource('Acl'))
                ->setRole(Zend_Controller_Front::getInstance()->getPlugin('Finrus_Plugin_Acl')->getRole());

        $view->headTitle('Finrus');

        if (!$this->hasResource('navigation')) {
            $this->bootstrap('navigation');
        }

        $view->menu = $this->getResource('navigation');

        Zend_Controller_Front::getInstance()->setResponse(new Zend_Controller_Response_Http());
        Zend_Controller_Front::getInstance()->getResponse()->setHeader('Content-Type', 'text/html; charset=utf-8');

        return $view;
    }

    public function _initRouters() {
        $router = Zend_Controller_Front::getInstance()->getRouter();

        $router->addRoute('search', new Zend_Controller_Router_Route(
            'search/:field',
            array(
                'module'     => 'default',
                'controller' => 'index',
                'action'     => 'search',
                'field'      => null,
            )
        ));
    }

}

