<?php

class Usuario extends Acesso
{
    public $user_level;
    public $user_login;
    public $user_nome;
    public $user_email;
    public $param = null;

    public function __construct()
    {
        parent:: __construct();
        $sid = new Session;
        $sid->start();
        if ( !$sid->check( "$this->baseUri" ) )
        {
            $this->redirect( "$this->baseUri/login/" );
        }
        $this->user_login = $sid->getNode( 'user_login' );
        $this->user_access = $sid->getNode( 'user_access' );
        if ( in_array( 'success', $this->uri ) )
        {
            $this->assign( 'onMsg', 'notify("Procedimento realizado com sucesso!")' );
        }
        $this->assign( 'hoje', date( 'd/m/Y' ) );
    }

    public function welcome()
    {
        $this->tpl( 'public/user.html' );
        $this->select()
                ->from( 'user' )
                ->where('nivel = 3')
                ->orderby( 'tx_nome asc' )
                //->orderby( 'login asc' )
                ->run();
        //$this->preg( array( '/1/', '/2/' ), array( 'Administrador', 'Usuário' ), 'user_level' );
        $this->fetch( 'rs' );
        $this->render();
    }

    public function novo()
    {
        $this->tpl( 'public/user_novo.html' );
        $this->render();
    }

    public function incluir()
    {
        if ( $this->postIsValid( array( 'login' => 'string', 'senha' => 'string' ) ) )
        {      
            $this->postIndexAdd('nivel', '3');
            $this->postValueChange( 'senha', base64_encode( $this->postGetValue( 'senha' ) ) );
            $this->insert( 'user' )->fields()->values()->run();
            $this->redirect( "$this->baseUri/usuario/success/" );
        }
        else
        {
            $e = new pageError;
            $e->message = "Todos os campos são de preenchimento obrigatório!";
            $e->show();
        }
    }

    public function remove()
    {
        $this->user_id = $this->uri[2];
        $this->delete()->from( 'user' )->where( "id = $this->user_id" )->run();
        $this->redirect( "$this->baseUri/usuario/success/" );
    }

    public function editar()
    {
        $this->user_id = $this->uri[2];
        $this->tpl( 'public/user_editar.html' );
        $this->select()
                ->from( 'user' )
                ->where( "id = $this->user_id" )
                ->run();
        $this->map();
        $this->assignAll();
        $this->render();
    }

    public function gravar()
    {
        $this->user_id = $this->uri[2];
        if ( $this->postIsValid( array( 'login' => 'string' ) ) )
        {
            if ( strlen( $this->postGetValue( 'senha' ) >= 2 ) )
            {
                $this->postValueChange( 'senha', base64_encode( $this->postGetValue( 'senha' ) ) );
            }
            else
            {
                $this->postIndexDrop( 'senha' );
            }
            $this->update( 'user' )->set()->where( "id = $this->user_id" )->run();
            $this->redirect( "$this->baseUri/usuario/success/" );
        }
    }
}
/*end file*/