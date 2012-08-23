<?php

class Finrus_Acl extends Zend_Acl {

    const ROLE_GUEST = 'guest';
    const ROLE_USER  = 'user';
    const ROLE_ADMIN = 'admin';

    public function __construct() {
        //roles
        $this->addRole(new Zend_Acl_Role(self::ROLE_GUEST));
        $this->addRole(new Zend_Acl_Role(self::ROLE_USER), self::ROLE_GUEST);
        $this->addRole(new Zend_Acl_Role(self::ROLE_ADMIN), self::ROLE_USER);

        //default deny all
        $this->deny(null, null);

        //common resources
        $this->addResource('guestPages');
        $this->addResource('userPages');
        $this->addResource('adminPages');

        //resources allowed for guests
        $this->addResource('error', 'guestPages');

        //resources allowed for users
        $this->addResource('index', 'userPages');
        $this->addResource('credit', 'userPages');
        $this->addResource('payment', 'userPages');
        $this->addResource('client', 'userPages');
        $this->addResource('blacklist', 'userPages');

        //resources allowed for admins
        $this->addResource('user', 'adminPages');
        $this->addResource('settings', 'adminPages');
        $this->addResource('economy', 'adminPages');

        //allow all the resources associated with the roles
        $this->allow(self::ROLE_GUEST, 'guestPages');
        $this->allow(self::ROLE_USER, 'userPages');
        $this->allow(self::ROLE_ADMIN, 'adminPages');

        //privileges
        $this->allow(self::ROLE_GUEST, 'index', 'login');

        $this->allow(self::ROLE_ADMIN, 'index', 'report');

        $this->deny(self::ROLE_USER, 'index', 'report');
        $this->deny(self::ROLE_USER, 'index', 'login');
    }
}

?>
