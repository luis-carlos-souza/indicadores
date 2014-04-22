<?php

class Acesso extends PHPFrodo
{
    public function __construct()
    {
        parent:: __construct();
        $sid = new Session;
        $sid->start();
        $this->user_access = $sid->getNode( 'user_access' );
    }
}
/*end file*/