<?php
/*Adapter pgsql*/
Class pgsql
{
    public $query;
    public $fetchAll;
    public $result;
    public $response;
    protected $driver;
    protected $config;
    protected $host;
    protected $port;
    protected $user;
    protected $pass;
    protected $dbname;
    protected $con;

    public function __construct( $config )
    {
        try
        {
            #array com dados do banco
            $this->config = $config;
            # Recupera os dados de conexao do config
            $this->dbname = $this->config['dbname'];
            $this->driver = $this->config['driver'];
            $this->host = $this->config['host'];
            $this->port = $this->config['port'];
            $this->user = $this->config['user'];
            $this->pass = $this->config['password'];
            # instancia e retorna objeto
            $this->con =  @pg_connect("host=$this->host dbname=$this->dbname user=$this->user password=$this->pass");
            if(!$this->con)
            {
                throw new Exception( "Falha na conexão PostgreSql com o banco [$this->dbname] em ".DATABASEDIR."database.conf.php" );
            }
            else
            {
                return $this->con;
            }
        }
        catch ( Exception $e )
        {
            echo  $e->getMessage( );
            exit;
        }
        return $this;
    }

    public function query($query = '')
    {
        try
        {
            if($query == '')
            {
                throw new Exception('pgsql query: A query deve ser informada como parâmetro do método.');
            }
            else
            {
                $this->query = $query;
                $this->result = @pg_query($this->query);
                $this->fetchAll = @pg_fetch_all($this->result);
                if(!$this->result)
                {
                    $this->response = utf8_decode( pg_last_error($this->con) );
                }
                else
                {
                    $this->response = "success";
                }                  
            }
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    public function fetchAll()
    {
        return $this->fetchAll;
    }

    public function rowCount()
    {
        //return @pg_affected_rows($this->result);
        return @pg_num_rows($this->result);
    }

    public function limit($offset, $limit)
    {
        return "LIMIT ". (int)$limit . " OFFSET " . (int)$offset;
    }
}
/* end file */