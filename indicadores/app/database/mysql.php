<?php

/* Adapter mysql */

Class mysql
{

    public $query;
    public $fetchAll;
    public $result;
    public $response;
    public $begin_status = false;
    public $config;
    public $host;
    public $port;
    public $user;
    public $pass;
    public $dbname;
    public $con = null;

    public function __construct( $config )
    {
        try
        {
            #array com dados do banco
            $this->config = $config;
            # Recupera os dados de conexao do config
            $this->dbname = $this->config['dbname'];
            $this->host = $this->config['host'];
            $this->port = $this->config['port'];
            $this->user = $this->config['user'];
            $this->pass = $this->config['password'];
            # instancia e retorna objeto
            $this->con = mysql_connect( "$this->host", "$this->user", "$this->pass" );
            mysql_select_db( "$this->dbname" );
            if ( !$this->con )
            {
                throw new Exception( "Falha na conexão MySql com o banco [$this->dbname] em " . DATABASEDIR . "database.conf.php" );
            }
            else
            {
                return $this->con;
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    public function __destruct()
    {   
        if ( $this->con == true )
        {
            @mysql_close();
        }
    }

    public function query( $query = '' )
    {
        try
        {
            if ( $query == '' )
            {
                throw new Exception( 'mysql query: A query deve ser informada como parâmetro do método.' );
            }
            else
            {
                $this->query = $query;
                $this->result = mysql_query( $this->query );
                if ( !$this->result )
                {
                    $this->response = "Erro " . mysql_errno() . " => " . mysql_error();
                }
                else
                {
                    $this->response = "success";
                }
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    public function fetchAll()
    {
        $this->fetchAll = "";
        while ( $row = @mysql_fetch_array( $this->result, MYSQL_ASSOC ) )
        {
            $this->fetchAll[] = $row;
        }
        return $this->fetchAll;
    }

    public function rowCount()
    {
        return @mysql_affected_rows();
    }

    public function begin()
    {
        @mysql_query( "START TRANSACTION" );
        @mysql_query( "BEGIN" );
    }

    public function commit()
    {
        @mysql_query( "COMMIT" );
    }

    public function rollback()
    {
        @mysql_query( "ROLLBACK" );
    }

    public function limit( $limit, $offset )
    {
        return "LIMIT " . ( int ) $limit . "," . ( int ) $offset;
    }

}

/* end file */