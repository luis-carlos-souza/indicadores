<?php

/**
 * Session class
 *
 * @author Rafael Clares <rafadinix@gmail.com>
 * @version 1.0  <10/2010>
 * @version 1.1  <02/2013>
 * web: www.clares.wordpress.com
 * 
 */
Class Session
{

    /**
     * $session = new Session;
     * $session->start();
     * $session->init(TimeLife,Domain);
     * $session->check(Dominio);
     * $session->status();
     * $session->destroy();
     */
    public function start()
    {
        @session_start();
    }

    public function init( $timeLife = null, $domain = null )
    {
        $_SESSION["ACTIVITY_ID"] = md5( uniqid( time() ) );
        $_SESSION['LAST_ACTIVITY'] = time();
        $_SESSION['SESSION_ID'] = session_id();
        $_SESSION['SS_DOMAIN'] = md5( $domain );
        if ( $timeLife != null )
        {
            $_SESSION['LIFE_TIME'] = $timeLife;
        }
        else
        {
            $_SESSION['LIFE_TIME'] = 1800;
        }
    }

    public function getLeftTime()
    {
        $minutos = floor( ($_SESSION['LIFE_TIME'] - (time() - $_SESSION['LAST_ACTIVITY']) ) / 60 );
        $segundos = (($_SESSION['LIFE_TIME'] - (time() - $_SESSION['LAST_ACTIVITY']) ) % 60 );
        if ( $segundos <= 9 )
        {
            $segundos = "0" . $segundos;
        }
        return "$minutos:$segundos";
    }

    public function addNode( $key, $value )
    {
        $_SESSION['node'][$key] = $value;
        return $this;
    }

    public function getNode( $key )
    {
        if ( isset( $_SESSION['node'][$key] ) )
        {
            return $_SESSION['node'][$key];
        }
    }

    public function getDomain()
    {
        if ( isset( $_SESSION['SS_DOMAIN'] ) )
        {
            return $_SESSION['SS_DOMAIN'];
        }
    }

    public function remNode( $key )
    {
        if ( isset( $_SESSION['node'][$key] ) )
        {
            unset( $_SESSION['node'][$key] );
        }
        return $this;
    }

    public function destroyNodes()
    {
        if ( isset( $_SESSION['node'] ) )
        {
            unset( $_SESSION['node'] );
        }
        return $this;
    }

    public function check( $domain )
    {
        if ( !isset( $_SESSION['LAST_ACTIVITY'] ) || ((time() - $_SESSION['LAST_ACTIVITY']) >= $_SESSION['LIFE_TIME']) )
        {
            return false;
        }
        else
        {
            if ( md5( $domain ) != $this->getDomain() )
            {
                return false;
            }
            else
            {
                return true;
            }
        }
    }

    public function getId()
    {

        if ( isset( $_SESSION['SESSION_ID'] ) )
        {
            return $_SESSION['SESSION_ID'];
        }
        else
        {
            return false;
        }
    }

    public function regenerate()
    {
        @session_regenerate_id();
        $_SESSION['SESSION_ID'] = session_id();
    }

    public function destroy()
    {
        $this->destroyNodes();
        @session_destroy();
        $_SESSION['SS_DOMAIN'] = md5( uniqid( time() ) );
        @session_start();
    }

}

/* end file */