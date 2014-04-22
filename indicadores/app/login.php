<?php

class Login extends PHPFrodo
{
    public $user_id;
    public $user_level;
    public $user_login;
    public $user_email;
    public $user_nome;
    public $url_retorno;
    public $message_login = "Efetue o login para ter acesso ao painel de gerenciamento";

    public function __construct()
    {
        parent:: __construct();
    }

    public function welcome()
    {
        $this->tpl( 'public/user_login.html' );
        if ( $this->doPost() )
        {
            $this->proccess();
        }
        $this->assign( 'message_login', $this->message_login );
        $this->render();
    }

    public function proccess()
    {
        if ( $this->postIsValid( array( 'user_login' => 'string', 'user_password' => 'string' ) ) )
        {
            $this->user_login = $this->postGetValue( 'user_login' );
            $this->user_password = base64_encode( $this->postGetValue( 'user_password' ) );
            $this->select( '*' )
                    ->from( 'user' )
                    //->where( "user_login = '$this->user_login' and user_password = '$this->user_password'" )
                    ->where( "login = '$this->user_login' and senha = '$this->user_password' AND nivel = 3" )
                    ->run();
            if ( $this->result() )
            {
                $sid = new Session;
                $sid->start();
                $sid->init( 36000, "$this->baseUri" );
                $sid->addNode( 'start', date( 'd/m/Y - h:i' ) );
                $sid->addNode( 'user_id', $this->data[0]['id'] );
                $sid->addNode( 'user_login', $this->data[0]['login'] );
                $sid->addNode( 'user_email', $this->data[0]['tx_email'] );
                $sid->addNode( 'user_name', $this->data[0]['tx_nome'] );
                $sid->addNode( 'user_access', $this->data[0]['nivel'] );
                $this->redirect( "$this->baseUri/" );
            }
            else
            {
                $this->message_login = "E-mail ou Senha incorretos!";
                $this->assign( 'onMsg', '$("#msg").show()' );
            }
        }
        else
        {
            $this->message_login = "E-mail e Senha requeridos!";
            $this->assign( 'onMsg', '$("#msg").show()' );
        }
    }

    public function logout()
    {
        $sid = new Session;
        @$sid->start();
        $sid->destroy();
        $sid->check( "$this->baseUri" );
        $this->redirect( "$this->baseUri/login/" );
    }
}
/*end file*/