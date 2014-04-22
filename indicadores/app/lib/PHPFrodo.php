<?php

/**
 * PHPFrodo class
 *
 * @author Rafael Clares <rafadinix@gmail.com>
 * @version 1.0 <10/2012>
 * @version 1.1 <02/2013>
 * web: www.clares.com.br
 *       ,
 *      ((_,-.
 *        '-.\_)'-,
 *          )  _ )'-   PHPFrodo
 * ,.;.,;,,(/(/ \));,;.,.,
 *
 * Simplifica o acesso ao banco de dados utilizando o PDO
 *
 */
error_reporting( E_ALL );

class PHPFrodo
{
    public $objBanco = null;
    public $dbname = null;
    public $dbase = 'default';
    public $driver = null;
    public $adapter = null;
    public $sgbd = null;
    public $data = null;
    public $tabela = null;
    public $campo = "*";
    public $query = null;
    public $valor = null;
    public $post_fields = array( );
    public $post_values = array( );
    public $strupdate = null;
    public $strorderby = null;
    public $view = null;
    public $stmt = null;
    public $numrows = null;
    public $pagelinks = null;
    public $method = null;
    public $lastID = null;
    public $limit = "";
    public $offset = "";
    public $limitOffset = null;
    public $paginateNum = null;
    public $pagebase = "";
    public $response = null;
    public $uri = array( );
    public $baseUri = "";
    public $referer = "";
    public $buffer = "";
    public $jsonData = "";
    public $paginateStyle = null;
    public $mailSubject = "";
    public $mailMsg = "";
    public $mailAddress = "";

    public function __construct()
    {
        @setlocale( LC_ALL, 'pt_BR', 'ptb' );
        $this->database();
        $this->loadUri();
        $this->assign( 'baseUri', "$this->baseUri" );
        $this->assign( 'microtime', microtime( true ) );
        return $this;
    }

    public function __destruct()
    {//               
    }

    public function __clone()
    {//
    }

    /**
     *  O método deve ser chamado quando não desejar utilizar o PDO
     *  @param String $adapter nome do adapter
     *  @example $obj->adapter('mysql');
     *  @example $obj->adapter('pgsql');
     */
    public function adapter( $adapter = null )
    {
        try
        {
            if ( $adapter == null )
            {
                throw new Exception( 'adapter: O adapter deve ser informado como parâmetro do método.' );
            }
            else
            {
                $this->adapter = $adapter;
                $this->driver = DATABASEDIR . $this->adapter . ".php";
                if ( !file_exists( $this->driver ) )
                {
                    $this->adapter = null;
                    throw new Exception( "adapter: O arquivo $this->driver não existe." );
                }
                else
                {
                    require_once $this->driver;
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

    /**
     *  Método utilizado para conexão com o banco
     *  @name database
     *  @param String $sgbd  - índice do array {database/database.conf.php} que contém as informações da conexão.
     *  @example $obj->database('blog');
     */
    public function database( $dbase = null )
    {
        try
        {
            if ( $dbase != null )
            {
                $this->dbase = $dbase;
            }
            if ( file_exists( DATABASEDIR . 'database.conf.php' ) )
            {
                include DATABASEDIR . 'database.conf.php';
            }
            else
            {
                throw new Exception( "database: Arquivo de configuração do banco inexistente!" );
            }
            if ( !isset( $databases["$this->dbase"] ) )
            {
                throw new Exception( "database: banco [$this->dbase] não configurado em " . DATABASEDIR . "database.conf.php" );
            }
            //conexao adapters
            $this->adapter( $databases[$this->dbase]['driver'] );
            $this->objBanco = new $this->adapter( $databases[$this->dbase] );
            $this->dbname = $databases[$this->dbase]['dbname'];
            $this->sgbd = $databases[$this->dbase]['driver'];
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     *  Método utilizado para realizar a selação
     *  Se o argumento for omitido o campo assumido será "*"
     *  @name database
     *  @param String
     *  @example $obj->select();
     *  @example $obj->select('*');
     *  @example $obj->select('user_id','user_name');
     *  @example $obj->select('user_id as id','user_name as name');
     */
    public function select( $campo = null )
    {
        if ( $campo != null )
        {
            $this->campo = $campo;
        }
        $this->data = null;
        $this->query = "SELECT $this->campo FROM ";
        return $this;
    }

    /**
     * Utilizado após o método select para realizar join's
     *
     * @name join
     * @param String $table Nome da tabela
     * @param String $condition Condição do JOIN
     * @param String $method  INNER, LEFT...
     * @example $obj->join("t1","t1.id = t2.id","INNER");
     */
    public function join( $table = '', $condition = '', $method = '' )
    {
        try
        {
            if ( $table == '' || $condition == '' )
            {
                throw new Exception( "join: tabela e condição devem ser informados como parâmetros do método." );
            }
            else
            {
                if ( $method != '' )
                {
                    $this->query .= " $method JOIN $table ON ($condition) ";
                }
                else
                {
                    $this->query .= " JOIN $table ON ($condition) ";
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

    /**
     * Utilizado após o método select, aponta a tabela destino
     *
     * @name from
     * @param String $table Nome da tabela
     * @example $obj->from("table");
     */
    public function from( $table = null )
    {
        try
        {
            if ( $table == null )
            {
                throw new Exception( "from - A(s) tabela(s) deve(m) ser informada(s) no método." );
            }
            else
            {
                $this->tabela = $table;
                $this->query .= " $this->tabela ";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para sql insert
     *
     * @name insert
     * @param String $table Nome da tabela
     * @example $obj->insert("produtos");
     */
    public function insert( $table = null )
    {
        try
        {
            if ( $table == null )
            {
                throw new Exception( "insert: Uma tabela deve ser informada como parâmetro do método." );
            }
            else
            {
                $this->tabela = $table;
                $this->campo = null;
                $this->valor = null;
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado em insert para informar os campos de destino
     *
     * @name fields
     * @param Array $field Nome do campo
     * @example $obj->fields(array('campo1','campo2'));
     */
    public function fields( $fields = array( ) )
    {
        try
        {
            if ( isset( $this->post_fields ) && !empty( $this->post_fields ) )
            {
                $fields = $this->post_fields;
            }
            if ( empty( $fields ) )
            {
                throw new Exception( "fields: O(s) campo(s) destino da inserção deve(m) ser informado(s) no método." );
            }
            else
            {
                $this->campo = implode( ",", $fields );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado no insert para informar os valores dos campos
     *
     * @name values
     * @param Array $value Valor do campo
     * @example $obj->values(array(10,'foo'));
     */
    public function values( $values = array( ) )
    {
        try
        {
            if ( isset( $this->post_values ) && !empty( $this->post_values ) )
            {
                $values = $this->post_values;
            }
            if ( empty( $values ) )
            {
                throw new Exception( "values: O(s) valor(es) deve(m) ser informado(s) como parâmetro(s) do método." );
            }
            else
            {
                $this->valor = "'" . implode( "','", $values ) . "'";
                $this->query = "INSERT INTO $this->tabela ($this->campo) VALUES ($this->valor);";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para Update no banco
     *
     * @name update
     * @param String $table Nome da tabela
     * @example $obj->update('users');
     */
    public function update( $table = null )
    {
        try
        {
            if ( $table == null )
            {
                throw new Exception( "update: A tabela destino deve ser informada como parâmetro do método." );
            }
            else
            {
                $this->strupdate = "";
                $this->tabela = $table;
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado após update, define nome e valor do campo
     *
     * @name set
     * @param Array $field Nome do campo
     * @param Array $value Valor do campo
     * @example $obj->set(array("nome","idade"),array("Blair",28));
     */
    public function set( $fields = array( ), $values = array( ) )
    {
        try
        {
            if ( empty( $fields ) || empty( $values ) )
            {
                $fields = $this->post_fields;
                $values = $this->post_values;
            }

            if ( !empty( $fields ) && !empty( $values ) )
            {
                $params = (array_combine( $fields, $values ));
                foreach ( $params as $key => $value )
                {
                    $this->strupdate .= " $key = '$value',";
                }
                $this->query = "UPDATE $this->tabela SET " . substr( $this->strupdate, 0, -1 );
            }
            else
            {
                throw new Exception( "set: Arrays fields ou values vazios." );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Incrementa determinado campo da tabela
     *
     * @name increment
     * @param String $table Nome da tabela
     * @param String $field Nome do campo
     * @param Int $value valor a ser incrementado
     * @example $obj->increment('visitas','count',1,'id = 1');
     */
    public function increment( $table = null, $field = null, $value = null, $cond = null )
    {
        try
        {
            if ( $table == null || $field == null || $value == null )
            {
                throw new Exception( 'increment: O nome da tabela,campo e valor devem ser informados!' );
            }
            else
            {
                $this->query = "UPDATE $table SET $field = $field+$value where $cond";
                $this->run();
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Decrementa determinado campo da tabela
     *
     * @name decrement
     * @param String $table Nome da tabela
     * @param String $field Nome do campo
     * @param Int $value valor a ser incrementado
     * @example $obj->decrement('visitas','count',1);
     */
    public function decrement( $table = null, $field = null, $value = null )
    {
        try
        {
            if ( $table == null || $field == null || $value == null )
            {
                throw new Exception( 'decrement: O nome da tabela,campo e valor devem ser informados!' );
            }
            else
            {
                $this->query = "UPDATE $table SET $field = $field-$value";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para deletar registros de uma tabela
     *
     * @name delete
     * @example $obj->delete();
     */
    public function delete()
    {
        $this->query = "DELETE FROM ";
        return $this;
    }

    /**
     *
     * Utilizado para realizar seleção com a condição
     * @name where
     * @param String $condition
     * @example $obj->where("id = 1");
     * @example $obj->where("username = 'foo' ");
     */
    public function where( $condition = null )
    {
        try
        {
            if ( $condition == null )
            {
                throw new Exception( "where: A condição deve ser informada como parâmetro do método." );
            }
            else
            {
                $this->query .= " WHERE $condition";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     *
     * Utilizado para realizar seleção com a condição OR (ou)
     *
     * @name orwhere
     * @param String $condition
     * @example $obj->orwhere("id > 5");
     */
    public function orwhere( $condition = null )
    {
        try
        {
            if ( $condition == null )
            {
                throw new Exception( "orwhere: A condição deve ser informada como parâmetro do método." );
            }
            else
            {
                $this->query .= " OR  $condition";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     *
     * Utilizado para realizar seleção com a condição AND (ou)
     *
     * @name andwhere
     * @param String $condition
     * @example $obj->andwhere("id > 5");
     */
    public function andwhere( $condition = null )
    {
        try
        {
            if ( $condition == null )
            {
                throw new Exception( "andwhere: A condição deve ser informada como parâmetro do método." );
            }
            else
            {
                $this->query .= " AND  $condition";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     *
     * Utilizado para realizar seleção com a condição Like em strings
     * @name like
     * @param String $field campo da tabela
     * @param String | Int $value valor comparado
     * @example $obj->like("nome","%Foo%");
     *
     */
    public function like( $field = null, $value = null )
    {
        try
        {
            if ( $field == null || $value == null )
            {
                throw new Exception( "like: O campo da tabela e valor comparado devem ser informados como parâmetro do método." );
            }
            else
            {
                $this->query .= " WHERE $field LIKE '$value'";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     *
     * Sql  orlike em strings
     * @name orlike
     * @param String $field campo da tabela
     * @param String | Int $value valor comparado
     * @example $obj->orlike("nome","%Foo%");
     */
    public function orlike( $field = null, $value = null )
    {
        try
        {
            if ( $field == null || $value == null )
            {
                throw new Exception( "orlike: O campo da tabela e valor comparado devem ser informados como parâmetros do método." );
            }
            else
            {
                $this->query .= " OR $field LIKE '$value'";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Metodo Sql orderby
     *
     * @name orderby
     * @param Strin $order campo ordem
     * @example $obj->orderby("nome asc");
     * @example $obj->orderby("nome desc");
     */
    public function orderby( $order = null )
    {
        try
        {
            if ( $order == null )
            {
                throw new Exception( "orderby: O campo e ordem devem ser informadas como parâmetros do método." );
            }
            else
            {
                $this->query .= " order by $order";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Metodo sql groupby
     *
     * @name orderby
     * @param Strin $order campo ordem
     * @example $obj->orderby("nome asc");
     * @example $obj->orderby("nome desc");
     */
    public function groupby( $field = null )
    {
        try
        {
            if ( $field == null )
            {
                throw new Exception( "groupby: O campo deve ser informado como parâmetro do método." );
            }
            else
            {
                $this->query .= " GROUP BY $field";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
      /**
     *
     * Popula o array assigndata para utilização no método display
     *
     * @name assign
     * @param key String indice do array
     * @param value String valor do array
     * @example $obj->assign('hoje', date());
     *
     */
    public function assign( $key = null, $value = null )
    {
        try
        {
            if ( $key == null )
            {
                throw new Exception( "assign: O método deve receber ao menos o primeiro parâmetro." );
            }
            else
            {
                $this->assigndata[$key] = trim( $value );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    public function assignAll()
    {
        try
        {
            if ( isset( $this->view->tpldata ) )
            {
                unset( $this->view->tpldata );
            }
            foreach ( $this->data as $data )
            {
                foreach ( $data as $key => $value )
                {
                    $this->assigndata[$key] = trim( $value );
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
    /* Tpl TemplateFy */

    public function tpl( $tpl = null, $tpldir = null, $baseapp = null )
    {
        try
        {
            if ( $tpl == null )
            {
                throw new Exception( "tpl: O arquivo de template deve ser informado como parâmetro do método." );
            }
            else
            {
                $this->view = new TemplateFy;
                if ( $tpldir != null )
                {
                    $this->view->tpldir = $tpldir;
                }
                else
                {
                    $this->view->tpldir = VIEWSDIR;
                }
                if ( $baseapp != null )
                {
                    $this->view->baseApp = $baseapp;
                }
                else
                {
                    $this->view->baseApp = HTTPURL . APP;
                }
                if(isset( $this->view_prepend_data))
				{
                    $this->view->data($this->view_prepend_data);
                }				
                $this->view->tpl( $tpl );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }
    /* Fecth  TemplateFy */

    public function fetch( $target, $data = null )
    {
        try
        {
            if ( isset( $this->view ) && is_callable( array( $this->view, 'fetch' ) ) )
            {
                if ( $data == null )
                {
                    $data = $this->data;
                }
                $this->view->fetch( $target, $data );
            }
            else
            {
                throw new Exception( "fetch: O objeto de template não foi inicializado." );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
    }
    /* render for TemplateFy */

    public function render( $printable=null )
    {
        try
        {
            if ( isset( $this->view ) && is_callable( array( $this->view, 'fetch' ) ) )
            {
                if ( !empty( $this->assigndata ) )
                {

                    $this->view->data( $this->assigndata );
                }

                if ( $printable == null )
                {
                    $this->view->render();
                }
                else
                {
                    return $this->view->render( 'printable' );
                }
            }
            else
            {
                throw new Exception( "render: O objeto de template não foi inicializado. obj->tpl()" );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
    }

    /**
     * Executa a Query e disponibiliza os dados em $this->data
     *
     */
    public function run()
    {
        try
        {
            if ( $this->paginateNum != null )
            {
                $this->stmt = $this->objBanco->query( "$this->query" );
                if ( $this->stmt )
                {
                    $this->numrows = $this->stmt->rowCount();
                    $this->paginateLinks( $this->paginateNum );
                }
            }
            if ( $this->limitOffset != null )
            {
                $this->query .= " $this->limitOffset";
            }
            if ( $this->paginateNum == null )
            {
                $this->stmt = $this->objBanco->query( "$this->query" );
            }
            if ( $this->stmt )
            {
                $this->numrows = $this->stmt->rowCount();
                if ( $this->adapter == null )
                {
                    $this->data = $this->stmt->fetchAll( PDO :: FETCH_ASSOC );
                }
                else
                {
                    $this->data = $this->stmt->fetchAll();
                }
            }
            if ( $this->objBanco->response != 'success' )
            {
                $this->response = $this->objBanco->response;
                throw new Exception( $this->response );
            }
            if ( preg_match_all( '/insert/i', $this->query, $m ) )
            {
                $this->getLastID();
            }
        }
        catch ( Exception $e )
        {
            echo "<p style=\"color:red;padding:6px;border:1px solid red; width:99%\">" . $e->getMessage() . "</p>";
            exit;
        }
        return $this;
    }

    /**
     *  getLastID - retorna MySQL lastIDInserted
     *  @return bool
     *  @example $obj->getLastID()
     */
    public function getLastID()
    {
        //$this->lastID = $this->objBanco->getLastID();
        $this->lastID = mysql_insert_id();
        return $this->lastID;
    }

    public function begin()
    {
        $this->objBanco->begin();
    }

    public function commit()
    {
        $this->objBanco->commit();
    }

    public function rollback()
    {
        $this->objBanco->rollback();
    }

    /**
     *  result - Verifica se há dados retornados da query
     *  @return bool
     *  @example $obj->result()
     */
    public function result()
    {
        if ( isset( $this->data ) && !empty( $this->data[0] ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Utilizado para criar url amigavel
     * @name urlmod
     * @param String $str
     * @param String $key
     * @param String $reverse
     * @example $obj->urlmod('titulo');
     * @example $obj->urlmod('titulo','link');
     * @example $obj->urlmod('titulo','link','reverse');
     * @return str teste-a-solucao
     */
    public function urlmod( $key, $nkey=null, $reverse=null )
    {
        $group_a = array( 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç',
            'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð',
            'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú',
            'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä',
            'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í',
            'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø',
            'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'A', 'a', 'A',
            'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c',
            'C', 'c', 'D', 'd', 'Ð', 'd', 'E', 'e', 'E',
            'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g',
            'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H',
            'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i',
            'I', 'i', '?', '?', 'J', 'j', 'K', 'k', 'L',
            'l', 'L', 'l', 'L', 'l', '?', '?', 'L', 'l',
            'N', 'n', 'N', 'n', 'N', 'n', '?', 'O', 'o',
            'O', 'o', 'O', 'o', '?', '?', 'R', 'r', 'R',
            'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's',
            '?', '?', 'T', 't', 'T', 't', 'T', 't', 'U',
            'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
            'U', 'u', 'W', 'w', 'Y', 'y', '?', 'Z', 'z',
            'Z', 'z', '?', '?', '?', '?', 'O', 'o', 'U',
            'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u',
            'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', '?',
            '?', '?', '?', '?', '?' );
        $group_b = array( 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C',
            'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D',
            'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U',
            'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a',
            'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i',
            'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A',
            'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c',
            'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E',
            'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g',
            'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H',
            'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i',
            'I', 'i', '', '', 'J', 'j', 'K', 'k', 'L',
            'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l',
            'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o',
            'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R',
            'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's',
            'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U',
            'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
            'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z',
            'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U',
            'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u',
            'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A',
            'a', 'AE', 'ae', 'O', 'o' );

        $pattern = array( '/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/' );
        $replace = array( ' ', '-', '' );
        try
        {
            if ( $reverse != null )
            {
                $replace = array( '-', '-', '' );
            }
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $item )
                {
                    if ( isset( $item[trim( $key )] ) )
                    {
                        $replaced = str_replace( $group_a, $group_b, $this->data[$idx][trim( $key )] );
                        if ( $nkey == null )
                        {
                            $this->data[$idx]["$key"] = strtolower( preg_replace( $pattern, $replace, $replaced ) );
                        }
                        else
                        {
                            $this->data[$idx]["$nkey"] = strtolower( preg_replace( $pattern, $replace, $replaced ) );
                        }
                    }
                }
            }
            else
            {
                $this->response = "urlmode: O array de origem está vazio.";
                //throw  new Exception("urlmod: O array de origem está vazio.");
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
    }

    public function urlmodr( $key, $nkey=null, $reverse=null )
    {
        $group_a = array( 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç',
            'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð',
            'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú',
            'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä',
            'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í',
            'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø',
            'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'A', 'a', 'A',
            'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c',
            'C', 'c', 'D', 'd', 'Ð', 'd', 'E', 'e', 'E',
            'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g',
            'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H',
            'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i',
            'I', 'i', '?', '?', 'J', 'j', 'K', 'k', 'L',
            'l', 'L', 'l', 'L', 'l', '?', '?', 'L', 'l',
            'N', 'n', 'N', 'n', 'N', 'n', '?', 'O', 'o',
            'O', 'o', 'O', 'o', '?', '?', 'R', 'r', 'R',
            'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's',
            '?', '?', 'T', 't', 'T', 't', 'T', 't', 'U',
            'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
            'U', 'u', 'W', 'w', 'Y', 'y', '?', 'Z', 'z',
            'Z', 'z', '?', '?', '?', '?', 'O', 'o', 'U',
            'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u',
            'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', '?',
            '?', '?', '?', '?', '?' );
        $group_b = array( 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C',
            'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D',
            'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U',
            'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a',
            'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i',
            'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o',
            'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A',
            'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c',
            'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E',
            'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g',
            'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H',
            'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i',
            'I', 'i', '', '', 'J', 'j', 'K', 'k', 'L',
            'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l',
            'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o',
            'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R',
            'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's',
            'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U',
            'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u',
            'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z',
            'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U',
            'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u',
            'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A',
            'a', 'AE', 'ae', 'O', 'o' );

        $pattern = array( '/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/' );
        $replace = array( ' ', '-', '' );

        if ( $reverse != null )
        {
            $replace = array( '-', ' ', '' );
        }
        $replaced = str_replace( $group_a, $group_b, $key );
        return strtolower( preg_replace( $pattern, $replace, $replaced ) );
    }

    /**
     * Utilizado para clonar ou concatenar 1 ou mais indices
     * @name clonekey
     * @param String $new
     * @param String $separator
     * @param Array $keys
     * @example $obj->clonekey('new key',array('key a','key b'));
     * @example $obj->clonekey('new key',array('key a','key b'),' - ');
     * @example $obj->clonekey('new key',array('key a'));
     * @example $obj->clonekey('new key',array('New Value'));
     *
     */
    public function clonekey( $new, $keys, $sep = " " )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $item )
                {
                    $t = array( );
                    foreach ( $keys as $key )
                    {
                        if ( isset( $this->data[$idx][$key] ) )
                        {
                            $t[] = $this->data[$idx][$key];
                        }
                        else
                        {
                            $t[] = $key;
                        }
                    }
                    $this->data[$idx][$new] = implode( $sep, $t );
                }
            }
            else
            {
                $this->response = "clonekey: O array de origem está vazio.";
                //throw  new Exception("concat: O array de origem está vazio.");
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para função preg_replace
     * @name preg
     * @param String $key
     * @param String $pattern
     * @param String $replace
     * @example $obj->preg('/./','*','key_name');
     *
     */
    public function preg( $pattern, $replace, $key )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $item )
                {
                    if ( isset( $item[trim( $key )] ) )
                    {
                        $this->data[$idx][trim( $key )] = preg_replace( $pattern, $replace, $this->data[$idx][trim( $key )] );
                    }
                }
            }
            else
            {
                $this->response = "preg: O array de origem está vazio.";
                //throw  new Exception("preg: O array de origem está vazio.");
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para função explod
     * @name explod
     * @param String $sep
     * @param String $key
     * @param String $idx
     * @example $obj->explod('-','key_data_cad',0);
     *
     */
    public function explod( $sep, $key, $index )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $item )
                {
                    if ( isset( $item[trim( $key )] ) )
                    {
                        $this->data[$idx][trim( $key )] = explode( $sep, $this->data[$idx][trim( $key )] );
                        if ( isset( $this->data[$idx][trim( $key )][$index] ) )
                            $this->data[$idx][trim( $key )] = $this->data[$idx][trim( $key )][$index];
                    }
                }
            }
            else
            {
                $this->response = "explod: O array de origem está vazio.";
                //throw  new Exception("explod: O array de origem está vazio.");
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para formatar o valor do campo
     * @name mask
     * @param String $key
     * @param String $mask
     * @example $obj->mask('key','###.###.###-##');
     *
     */
    public function mask( $key, $mask )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $item )
                {
                    $str_final = '';
                    $k = 0;
                    if ( isset( $item[$key] ) && !empty( $item[$key] ) )
                    {
                        $keyval = $this->data[$idx][trim( $key )];
                        $keyval = preg_replace( '/[\/: \/s]/', '', $keyval );
                        for ( $i = 0; $i <= strlen( $mask ) - 1; $i++ )
                        {
                            if ( $mask[$i] == '#' )
                            {
                                if ( isset( $keyval[$k] ) )
                                    $str_final .= $keyval[$k++];
                            }
                            else
                            {
                                if ( isset( $mask[$i] ) )
                                    $str_final .= $mask[$i];
                            }
                        }
                        $this->data[$idx][trim( $key )] = $str_final;
                    }
                }
            }
            else
            {
                $this->response = "mask: O array de origem está vazio.";
                //throw  new Exception("mask: O array de origem está vazio.");
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para  somar valores do campo determinado
     * @name sum
     * @param String $key
     * @example $obj->sum('produto_valor');
     * return $this->sum;
     */
    public function sum( $key )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                $ret = 0;
                foreach ( $this->data as $id => $data )
                {
                    if ( isset( $data[$key] ) )
                    {
                        $dkey = $data[$key];
                        $num = preg_replace( '/\,/', '', $dkey );
                        $num = preg_replace( '/\./', '', $dkey );
                        $ret += $num;
                    }
                    else
                    {
                        $ret = 0;
                    }
                }
                $this->sum = $ret;
            }
            else
            {
                //throw  new Exception("encode: O array de origem está vazio.");
                $this->response = "sum: O array de origem está vazio.";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this->sum;
    }

    /**
     * Utilizado para formatar datas ...
     * @name todata
     * @param String key
     * @param String p1
     * @example $obj->todata('item_data','y/m/d');
     */
    public function todata( $tbkey = null, $p1 = 'd/m/Y' )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $val )
                {
                    if ( isset( $this->data[$idx]["$tbkey"] ) )
                    {
                        if ( $this->data[$idx]["$tbkey"] != "" )
                        {
                            $this->data[$idx]["$tbkey"] = date( $p1, strtotime( $this->data[$idx]["$tbkey"] ) );
                        }
                    }
                }
            }
            else
            {
                //throw  new Exception("todata: O array de origem está vazio.");
                $this->response = "todata: O array de origem está vazio.";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para formatar moeda em real ...
     * @name money
     * @param String key
     * @param String decimals
     * @param String sep1
     * @param String sep2
     * @example $obj->money('price');
     * @example $obj->money('price',2,'.','');
     */
    public function money( $tbkey = null, $decimals = 2, $sep1 = ',', $sep2 = '.' )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $val )
                {
                    if ( isset( $this->data[$idx]["$tbkey"] ) )
                    {
                        $this->data[$idx]["$tbkey"] = @number_format( $this->data[$idx]["$tbkey"], $decimals, $sep1, $sep2 );
                    }
                }
            }
            else
            {
                //throw  new Exception("money: O array de origem está vazio.");
                $this->response = "money: O array de origem está vazio.";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para mudar o encoding utf8_decode, utf8_encode, htmlentities ...
     * @name encode
     * @param String $encoding
     * @example $obj->encode('key','utf8_decode');
     * @example $obj->encode();
     * defauls all keys to utf8_decode
     */
    public function encode( $tbkey = null, $encoding = 'utf8_decode' )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $val )
                {
                    if ( $tbkey != null )
                    {
                        if ( isset( $this->data[$idx]["$tbkey"] ) )
                        {
                            $this->data[$idx]["$tbkey"] = $encoding( $this->data[$idx]["$tbkey"] );
                        }
                    }
                    else
                    {
                        foreach ( $val as $key => $v )
                        {
                            if ( isset( $this->data[$idx]["$key"] ) )
                            {
                                $this->data[$idx]["$key"] = $encoding( $this->data[$idx]["$key"] );
                            }
                        }
                    }
                }
            }
            else
            {
                //throw  new Exception("encode: O array de origem está vazio.");
                $this->response = "encode: O array de origem está vazio.";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para cortar uma string do array ...
     * @name cut
     * @param String $key
     * @param int $chars
     * @example $obj->cut('name',40);
     *
     */
    public function cut( $key, $chars, $info )
    {
        try
        {
            if ( !empty( $this->data ) )
            {
                foreach ( $this->data as $idx => $item )
                {
                    if ( isset( $item[trim( $key )] ) )
                    {
                        $str = $item[trim( $key )];
                        if ( strlen( $str ) >= $chars )
                        {
                            $str = preg_replace( '/\s\s+/', ' ', $str );
                            $str = strip_tags( $str );
                            $str = preg_replace( '/\s\s+/', ' ', $str );
                            $str = substr( $str, 0, $chars );
                            $str = preg_replace( '/\s\s+/', ' ', $str );
                            $arr = explode( ' ', $str );
                            array_pop( $arr );
                            //$arr = preg_replace('/\&nbsp;/i',' ',$arr);
                            $final = implode( ' ', $arr ) . $info;
                        }
                        else
                        {
                            $final = $str;
                        }
                        $this->data[$idx][trim( $key )] = strip_tags( $final );
                    }
                }
            }
            else
            {
                //throw  new Exception("cut: O array de origem está vazio.");
                $this->response = "cut: O array de origem está vazio.";
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Utilizado para realizar limit na paginacao
     *
     * @param Int $limit
     * @param Int $offset
     * @example $obj->limit(10,20);
     */
    protected function limit( $limit = null, $offset = null )
    {
        try
        {
            if ( $limit == null || $offset == null )
            {
                $this->response = "limit: Os parâmetros limit e offset devem ser informados.";
                //throw  new Exception('limit: Os parâmetros limit e offset devem ser informados.');
            }
            $this->limit = $limit;
            $this->offset = $offset;
            if ( $this->sgbd == 'pdo' )
            {
                $this->limitOffset = "LIMIT " . ( int ) $this->offset . " OFFSET " . ( int ) $this->limit;
                // PDO inverse sequence limit x offset value
            }
            else
            {
                $this->limitOffset = $this->objBanco->limit( $this->limit, $this->offset );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     *
     * Utilizado para realizar paginacao, utiliza para isso o limit()
     *
     * @name paginate
     * @param Int $rows Número de registros por página
     * @example $obj->paginate(10);
     */
    public function paginate( $rows = null )
    {
        $this->paginateNum = $rows;
        return $this;
    }

    /**
     * utilizado internamente para criar os links html para paginação
     *
     * @name paginateLinks
     * @param Int $rows
     *
     */
    public function paginateLinks( $rows = null )
    {
        try
        {
            if ( $rows == null )
            {
                throw new Exception( "paginate: O número de registros por página deve ser informado como parâmetro." );
            }
            else
            {
                if ( $this->pagebase == "" && isset( $this->uri ) )
                {
                    if ( isset( $this->uri[1] ) && $this->uri[1] != 'page' )
                    {
                        $this->pagebase = HTTPURL . $this->uri[0] . "/" . $this->uri[1];
                    }
                    elseif ( isset( $this->uri[0] ) )
                    {
                        $this->pagebase = HTTPURL . $this->uri[0];
                    }
                }
                //if( !empty( $this->data ) ){
                $page = '0';
                $this->pagelinks = "";
                $ant = 0;
                $prox = 2;
                $total = ceil( $this->numrows / $rows );
                $ult = $total;

                if ( in_array( 'page', $this->uri ) )
                {
                    $page = array_search( 'page', $this->uri );
                    if ( isset( $this->uri[$page + 1] ) )
                    {
                        $page = $this->uri[$page + 1];
                        $prox = ($page + 1);
                        $ant = ($page - 1);
                    }
                }

                $lim = (($page * $rows) - $rows);
                if ( $lim <= 0 )
                {
                    $lim = 0;
                }
                $off = ($rows);
                $this->limit( $lim, $off );

                $maxPages = 9;
                $this->pageArr = "";
                //if( $total >= $rows ){
                for ( $i = 1; $i <= $total; $i++ )
                {
                    if ( $i == 1 && $page <= 1 )
                    {
                        $this->pageArr[] = "<li class=\"active\"><span>$i</span></li>";
                    }
                    elseif ( $i == $page )
                    {
                        $this->pageArr[] = "<li class=\"active\"><span>$i</span></li>";
                    }
                    elseif ( $i == 1 )
                    {
                        $this->pageArr[] = "<li><a href=\"$this->pagebase/page/$i/\">$i</a></li>";
                    }
                    else
                    {
                        $this->pageArr[] = "<li><a href=\"$this->pagebase/page/$i/\">$i</a></li>";
                    }
                }

                $continue = "<li class=\"disabled\"><a>...</a></li>";
                $primeira = "<li><a href=\"$this->pagebase/page/1/\" title=\"primeira\">««</a></li>";
                $ultima = "<li><a href=\"$this->pagebase/page/$ult/\" title=\"última\">»»</a></li>";

                if ( $total != $page )
                {
                    $proxima = "<li><a href=\"$this->pagebase/page/$prox/\" title=\"próxima\">»</a></li>";
                }
                else
                {
                    $proxima = "<li class=\"disabled\"><span>»</span></li>";
                    $ultima = "<li class=\"disabled\"><span>»»</span></li>";
                }
                if ( $ant >= 1 )
                {
                    $anteriror = "<li><a href=\"$this->pagebase/page/$ant/\" title=\"anterior\">«</a></li>";
                }
                if ( $ant == 0 )
                {
                    $primeira = "<li class=\"disabled\"><span>««</span></li>";
                    $anteriror = "<li class=\"disabled\"><span>«</span></li>";
                }

                if ( $page < $maxPages )
                {
                    if ( !empty( $this->pageArr ) )
                        $arr = array_slice( $this->pageArr, 0, $maxPages - 1 );
                }
                else
                {
                    if ( ($page % $maxPages) == 0 )
                    {
                        if ( !empty( $this->pageArr ) )
                            $arr = array_slice( $this->pageArr, $page - 1, $maxPages );
                    }
                    else
                    {
                        if ( !empty( $this->pageArr ) )
                            $arr = array_slice( $this->pageArr, ($page - 1) - ($page % $maxPages), $maxPages );
                    }
                }
                if ( $total >= $maxPages )
                {
                    $arr[] = $continue;
                }

                if ( $total != $page )
                {
                    if ( ($page + $maxPages) < $total && $page >= ($maxPages * 2) )
                    {
                        $offjump = $page - ($page % $maxPages) + ($maxPages * 2) - 1;
                        if ( isset( $this->pageArr[$offjump] ) )
                        {
                            // $arr[] = $this->pageArr[$offjump];
                        }
                    }
                    else
                    {
                        if ( $page <= ($total - $maxPages) - 1 )
                        {
                            // $arr[] = $this->pageArr[($maxPages * 2) - 1];
                        }
                    }
                    if ( $total >= $maxPages )
                    {
                        $arr[] = $this->pageArr[count( $this->pageArr ) - 1];
                    }
                }
                else
                {
                    if ( $total >= $maxPages )
                        $arr = array_slice( $this->pageArr, $total - $maxPages, $maxPages );
                }
                if ( $page >= $maxPages * 2 )
                {
                    array_unshift( $arr, $continue );
                    if ( $page < $total )
                    {
                        array_unshift( $arr, $this->pageArr[($page - ($page % $maxPages) - 1) - ($maxPages)] );
                    }
                    else
                    {
                        array_unshift( $arr, $this->pageArr[$total - ($maxPages * 2)] );
                    }
                }
                if ( !empty( $arr ) )
                {
                    array_unshift( $arr, $anteriror );
                }
                //array_unshift( $arr, $primeira );
                $arr[] = $proxima;
                //$arr[] = $ultima;

                $this->pagelinks = implode( "\n", $arr );
                $this->paginateNum = null;

                if ( $total <= 1 )
                {
                    $this->pagelinks = "";
                }

                //}
            }
            $this->pagelinks = "$this->pagelinks\n";
            $this->assign( 'pages', $this->pagelinks );
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Uitilizado para carregar funções de outros arquivos
     *
     * @name helper
     * @param String $helper nome do arquivo com as funções
     * @example $obj->helper('sendmail');
     *
     */
    public function helper( $helper )
    {
        try
        {
            if ( file_exists( HELPERDIR . "helper_" . $helper . ".php" ) )
            {
                require_once HELPERDIR . "helper_" . $helper . ".php";
            }
            else
            {
                throw new Exception( "helper: Arquivo não encontrado no diretório " . HELPERDIR );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    /**
     * Converte Json em PHP Array
     * jextract
     * @param Array Json
     * @example print_r($obj->jextract($_POST['dataform']));
     */
    public function jextract( $param = null )
    {
        try
        {
            $json2array = '';
            if ( $param == null )
            {
                throw new Exception( 'jextract: str vazia' );
            }
            else
            {
                parse_str( $param, $json2array );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $json2array;
    }

    /**
     * Converte Array em Json
     * toJson
     * @param Array param
     * @example print_r($obj->jextract($_POST['dataform']));
     */
    public function toJson( $param = null, $nokey = null )
    {
        try
        {
            $jarray = array( );
            if ( $param == null && empty( $this->data[0] ) )
            {
                //throw new Exception( 'toJson: array vazio' );
                $this->jsonData = '-1';
            }
            else
            {
                if ( $param == null )
                {
                    $param = $this->data;
                }
                $json = "";
                if ( $nokey == null )
                {
                    $json .= "{ \"rs\" : [";
                }
                else
                {
                    $json .= "[";
                }
                foreach ( $param as $p )
                {
                    if ( $nokey == null )
                    {
                        $json .= "{";
                    }
                    foreach ( $p as $k => $v )
                    {
                        //$v = utf8_encode( $v );
                        if ( $nokey != null )
                        {
                            $json .= "\"$v\",";
                        }
                        else
                        {
                            $json .= "\"$k\":\"$v\",";
                        }
                    }
                    if ( $nokey == null )
                    {
                        $json .= "},";
                    }
                }
                $json = substr_replace( $json, '', -1, 1 );
                $json = preg_replace( '/,}/', '}', $json );
                if ( $nokey == null )
                {
                    $json .= "]}";
                }
                else
                {
                    $json .= "]";
                }
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        $this->jsonData = $json;
        return $this->jsonData;
    }

    /**
     * Retorna um XML do resultado da consulta
     * toXML
     * @param String $rootnode (opcional)
     * @param String $node (opcional)
     */
    public function toXML( $rootnode = 'root', $node = 'node' )
    {
        $Xml = new XmlData( $rootnode, $node );
        $Xml->fromArray( $this->data );
        $Xml->output();
        return $this;
    }

    /**
     * Redireciona URL
     * redirect
     * @param String $url
     */
    public function redirect( $url = null )
    {
        @header( "Location: $url" );
    }

    /**
     * Extrai as variaveis do get e armazena no atrivuto uri URL
     * loadUri
     */
    public function loadUri()
    {
        try
        {
            if ( !isset( $_GET ) || empty( $_GET ) )
            {
                throw new Exception( 'loadUri: Null' );
            }
            else
            {
                $routes = explode( "/", $_GET['route'] );
                foreach ( $routes as $uri )
                {
                    if ( $uri != "" )
                    {
                        $this->uri[] = $uri;
                    }
                    (isset( $_SERVER['HTTP_REFERER'] )) ? $this->referer = $_SERVER['HTTP_REFERER'] : $this->referer = '';
                }
                //GET BASE URI
                $TMP_URL = $this->get_current_url();
                if ( !preg_match( '/www/', $TMP_URL ) )
                {
                    $protocol = "http://";
                }
                else
                {
                    $protocol = 'http://www.';
                }
                $TMP_URL = explode( "index.php", $TMP_URL );
                $HTTPURL = $TMP_URL[0];
                @define( 'HTTPURL', $HTTPURL );
                $this->baseUri = substr( $HTTPURL, 0, -1 );
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }
    /* Retornar a URL Absoluta BASEURI */

    public static function get_current_url()
    {
        $protocol = 'http';
        if ( $_SERVER['SERVER_PORT'] == 443 || (!empty( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') )
        {
            $protocol .= 's';
            $protocol_port = $_SERVER['SERVER_PORT'];
        }
        else
        {
            $protocol_port = 80;
        }
        $host = $_SERVER['HTTP_HOST'];
        $port = $_SERVER['SERVER_PORT'];
        $request = $_SERVER['PHP_SELF'];
        if ( isset( $_SERVER['argv'][0] ) )
            $query = substr( $_SERVER['argv'][0], strpos( $_SERVER['argv'][0], ';' ) + 1 );
        $toret = $protocol . '://' . $host . ($port == $protocol_port ? '' : ':' . $port) . $request . (empty( $query ) ? '' : '?' . $query);
        return $toret;
    }
    /*
     * Retorna oarray pre formatado 
     * printr
     * @param Array $array
     * @example $obj->printr($data);
     */

    public function printr( $data = null )
    {
        if ( $data == null )
        {
            $data = $this->data;
        }
        echo "<pre>";
        print_r( $data );
        echo "</pre>";
    }

    /**
     * Popula os arrays fields e values para montar a query
     * post2Query
     * retorna os dados em $this->post_fields e $this->post_values
     * @param Array $arr2query
     * @example $obj->post2Query($_POST);
     * @example $obj->post2Query($_GET);
     */
    public function post2Query( $arr2query )
    {
        try
        {
            if ( !is_array( $arr2query ) || empty( $arr2query ) )
            {
                throw new Exception( 'post2query: O paramêtro não é um array ou está vazio!' );
            }
            else
            {
                foreach ( $arr2query as $key => $value )
                {
                    $value = @addslashes($value );
                    $this->post_fields[] = trim( "$key" );
                    $this->post_values[] = trim( "$value" );
                    //$this->post_values[] = preg_replace('/\s+/', ' ', $value);
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
    /* Checa se houve post post 
     * doPost
     * @param String $key
     * @example $obj->doPost();
     * @example $obj->doPost('email')
     */

    public function doPost( $key = null )
    {
        if ( isset( $_POST ) && !empty( $_POST ) )
        {
            if ( $key != null )
            {
                if ( isset( $_POST["$key"] ) && strlen( $_POST["$key"] ) >= 1 )
                {
                    return true;
                }
                else
                {
                    return false;
                }
            }
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Valida campos submetidos pelo post, 
     * Se TRUE, retorna os dados em $this->post_fields e $this->post_values
     * postIsValid
     * @param Array $post
     * @example $obj->->postIsValid( array( 'item_desc' => 'string', 'item_cat' => 'numeric' );
     * @example $obj->->postIsValid( array('item_cat' => 'numeric' );
     */
    public function postIsValid( $post = array( ) )
    {
        $this->response = "";
        $is_valid = true;
        if ( !is_array( $post ) || empty( $post ) )
        {
            $this->response = 'O paramêtro não é um array ou está vazio!';
            $is_valid = false;
        }
        else
        {
            foreach ( $post as $key => $value )
            {
                if ( isset( $_POST[$key] ) && preg_replace( '/\s+/', '', $_POST[$key] ) == "" || !isset( $_POST[$key] ) )
                {
                    $this->response .= '<p>O campo [' . $key . '] deve ser preenchido! </p>';
                    $is_valid = false;
                }
                else
                {
                    if ( isset( $_POST[$key] ) )
                    {
                        if ( $value == 'numeric' && !is_numeric( $_POST[$key] ) )
                        {
                            $this->response .= '<p>O campo [' . $key . '] deve ser númerico! </p>';
                            $is_valid = false;
                        }
                        elseif ( $value == 'mail' && !preg_match( "/^[a-z0-9_\.\-]+@[a-z0-9_\.\-]*[a-z0-9_\-]+\.[a-z]{2,4}$/", $_POST[$key] ) )
                        {
                            $this->response .= '<p>O campo [' . $key . '] deve conter um e-mail válido! </p>';
                            $is_valid = false;
                        }
                        elseif ( $value == 'cpf' )
                        {
                            $this->helper( 'str' );
                            if ( !validaCpf( $_POST[$key] ) )
                            {
                                $this->response .= '<p>O campo [' . $key . '] deve ser conter um CPF válido! </p>';
                                $is_valid = false;
                            }
                        }
                        elseif ( $value == 'money' )
                        {
                            if ( !preg_match( "/^[-+]?\d{1,3}(\.\d{3})*,\d{2}$/", $_POST[$key], $s ) )
                            {
                                $this->response .= 'O campo [' . $key . '] deve ser conter valor monetário! <br />';
                                $is_valid = false;
                            }
                        }
                    }
                }
            }
            if ( $is_valid == false )
            {
                return false;
            }
            else
            {
                $this->post2Query( $_POST );
                $this->response = "";
                return true;
            }
        }
    }

    //add index do post_fields e post_values
    public function postValueChange( $index, $value )
    {
        $change = array_search( $index, $this->post_fields );
        if ( isset( $this->post_fields["$change"] ) )
        {
            $this->post_values[$change] = "$value";
        }
    }

    //add index do post_fields e post_values
    public function postIndexDate( $index )
    {
        $change = array_search( $index, $this->post_fields );
        if ( isset( $this->post_fields["$change"] ) )
        {
            if ( $this->post_values[$change] != "" )
            {
                $todate = preg_replace( '/\//', '-', $this->post_values[$change] );
                $this->post_values[$change] = date( 'Y-m-d', strtotime( $todate ) );
            }
        }
    }

    //add index do post_fields e post_values
    public function postIndexAdd( $index, $value )
    {
        $value = mysql_real_escape_string( $value );
        $this->post_fields[] = "$index";
        $this->post_values[] = "$value";
    }

    //get value do post_fields
    public function postGetValue( $index )
    {
        $value = array_search( $index, $this->post_fields );
        if ( $value || isset( $this->post_fields["$value"] ) )
        {
            return $this->trimmer( $this->post_values[$value] );
        }
        else
        {
            if ( isset( $_POST["$index"] ) )
            {
                return $this->trimmer( $_POST["$index"] );
            }
            else
            {
                return false;
            }
        }
    }

    //trim
    public function trimmer( $str )
    {
        return preg_replace( '/\s+/', ' ', $str );
    }

    //formata para campo double 10,2
    public function postIndexFormat( $index, $format = 'money' )
    {
        if ( $format == 'money' )
        {
            $change = array_search( $index, $this->post_fields );
            $this->post_values[$change] = preg_replace( array( '/\./', '/\,00/' ), array( '', '' ), $this->post_values[$change] );
        }
    }

    //remove index do post_fields e post_values
    public function postIndexDrop( $index )
    {
        $remove = array_search( $index, $this->post_fields );
        if ( $remove )
        {
            unset( $this->post_fields[$remove] );
            unset( $this->post_values[$remove] );
        }
        else
        {
            $this->response .= 'O index informado não existe no array';
        }
    }

    //remove blank post_fields e post_values
    public function postBlankDrop()
    {
        foreach ( $this->post_fields as $index )
        {
            $idx = @array_search( $index, $this->post_fields );
            if ( $this->trimmer( $this->post_values[$idx] ) == "" || empty( $this->post_values[$idx] ) )
            {
                unset( $this->post_fields[$idx] );
                unset( $this->post_values[$idx] );
            }
        }
        //sort($this->post_fields);
        //sort($this->post_values);
    }

    //exibe os post_fields e values
    public function showPostData()
    {
        $this->printr( $this->post_fields );
        $this->printr( $this->post_values );
        exit;
    }

    public function scaffold( $conf = array( 'url' => '' ) )
    {
        echo "<!DOCTYPE html>\n";
        echo "<html>\n";
        echo "<head>\n";
        try
        {
            echo "<link href=\"$this->baseUri/app/assets/js/jquery/bootstrap/css/bootstrap.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
            echo "<link href=\"$this->baseUri/app/assets/css/default/scaff.css\" rel=\"stylesheet\" type=\"text/css\" />\n";
            echo "</head>\n";
            echo "<body>\n";
            echo "<div id=\"scaff\">\n";
            $table = null;
            if ( isset( $this->uri[1] ) )
            {
                $table = $this->uri[1];
            }
            $url = $conf['url'];
            $fields = array( );
            if ( $table == null )
            {
                //throw new Exception( 'Informe o nome da tabela' );
                $this->query = "select * FROM information_schema.tables WHERE  TABLE_SCHEMA = '$this->dbname'";
                $this->run();
                $data = $this->data;

                foreach ( $data as $item )
                {
                    $tbl = $item['TABLE_NAME'];
                    echo "<p><a href=\"$url/$tbl/\">$tbl</a></p>\n";
                }
            }
            else
            {
                $this->query = "select * FROM information_schema.columns WHERE table_name = '$table' and TABLE_SCHEMA = '$this->dbname'";
                $this->run();
                $data = $this->data;
                echo "<p><a href=\"$url/\" >HOME / TABELAS </a></p>\n";
                $pkey = "id";
                echo "<table class='table table-striped'>\n";
                echo "<thead>\n";
                echo "<tr>\n";
                foreach ( $data as $key => $val )
                {
                    echo "<th>";
                    $key = $val['COLUMN_NAME'];
                    array_push( $fields, $key );
                    if ( $val['COLUMN_KEY'] == 'PRI' )
                    {
                        $pkey = $val['COLUMN_NAME'];
                    }
                    echo ucwords( preg_replace( '/_/', ' ', $key ) );
                    echo "</th>\n";
                }
                echo "<th>Action</th>\n";
                echo "</tr>\n";
                echo "</thead>\n";

                //add and update
                if ( isset( $_POST ) && !empty( $_POST ) )
                {
                    $this->post2Query( $_POST );

                    if ( !isset( $_GET['update'] ) && !empty( $_POST["$pkey"] ) )
                    {
                        $this->insert( "$table" )
                                ->fields( $this->post_fields )
                                ->values( $this->post_values )
                                ->run();
                    }
                    elseif ( isset( $_GET['update'] ) )
                    {
                        $id = $_GET['update'];
                        $this->update( "$table" )
                                ->set( $this->post_fields, $this->post_values )
                                ->where( "$pkey = '$id'" )
                                ->run();
                        $this->redirect( "$url/$table/" );
                    }
                }
                //delete
                if ( isset( $_GET['delete'] ) && !empty( $_GET['delete'] ) )
                {
                    $id = $_GET['delete'];
                    $this->delete()->from( "$table" )->where( "$pkey = '$id'" )->run();
                    $this->redirect( "$url/$table/" );
                }

                $this->select()->from( "$table" )->run();
                if ( $this->result() )
                {
                    echo "<tbody>\n";
                    foreach ( $this->data as $item )
                    {
                        echo "<tr>\n";
                        $k = 0;
                        foreach ( $item as $v )
                        {
                            echo "<td>\n";
                            $pkeyv = $item["$pkey"];
                            $f = array_values( $item );
                            echo $f[$k] . "\n";
                            echo "</td>\n";

                            $k++;
                        }
                        echo "<td><a href=\"?delete=$pkeyv\" class=\"btn\">delete</a> ";
                        echo "<a href=\"?edit=$pkeyv\" class=\"btn\">edit</a></td>";
                        echo "</tr>\n";
                    }
                    echo "</tbody>\n";
                }
                echo "</table>\n";

                //edit
                if ( isset( $_GET['edit'] ) && !empty( $_GET['edit'] ) )
                {

                    $id = $_GET['edit'];
                    $this->select()->from( "$table" )->where( "$pkey = '$id'" )->run();
                    if ( $this->result() )
                    {
                        $data = $this->data[0];
                        $scaff = '<form name="f" action="?update=' . $id . '" method="post" class="form">' . "\n";
                        foreach ( $fields as $key )
                        {
                            $label = ucwords( preg_replace( '/_/', ' ', $key ) );
                            $scaff .= '<label for="' . $key . '">' . $label . '</label>' . "\n";
                            $scaff .= '<input type="text" name="' . $key . '" id="' . $key . '" value="' . $data[$key] . '"  />' . "\n\n";
                        }
                        $scaff .= '<br /><br /><button class="btn">Atualizar Registro</button>' . "\n";
                        $scaff .= '</form>' . "\n";
                        echo $scaff;
                        exit;
                    }
                }

                $scaff = '<form name="f"  method="post" class="form">' . "\n";
                foreach ( $data as $key => $val )
                {
                    $key = $val['COLUMN_NAME'];
                    $type = $val['COLUMN_TYPE'];
                    $label = ucwords( preg_replace( '/_/', ' ', $key ) );
                    $scaff .= '<label for="' . $key . '">' . $label . '</label>' . "\n";
                    $scaff .= '<input type="text" name="' . $key . '" id="' . $key . '" placeholder="' . $type . '" />' . "\n\n";
                }
                $scaff .= '<br /><br /><button class="btn">Incluir Registro</button>' . "\n";
                $scaff .= '</form>' . "\n";
                echo $scaff;
            }
            echo "</div>\n";
            echo "</body>\n";
            echo "</html>";
            if ( $this->response != null )
            {
                echo $this->response;
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    //abre arquivo ou url com curl ou fgets
    public function openUrl( $param = array( ) )
    {
        try
        {
            if ( empty( $param ) )
            {
                throw new Exception( 'openUrl: Array de parâmetros vazio!' );
            }
            else
            {
                if ( isset( $param['method'] ) )
                {
                    $method = strtoupper( $param['method'] );
                }
                else
                {
                    throw new Exception( 'openUrl: Parâmetro method deve ser informado no array de parâmetros!' );
                }
                if ( $method == 'C' )
                {
                    $url = $param['url'];
                    $buffer = "";
                    $ch = curl_init();
                    curl_setopt( $ch, CURLOPT_URL, $url );
                    curl_setopt( $ch, CURLOPT_HEADER, 0 );
                    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
                    $buffer = trim( curl_exec( $ch ) );
                    if ( curl_errno( $ch ) )
                    {
                        throw new Exception( 'Curl error: ' . curl_error( $ch ) );
                    }
                    else
                    {
                        $this->buffer = $buffer;
                        return $buffer;
                    }
                    curl_close( $ch );
                }
                elseif ( $method == 'F' )
                {
                    $url = $param['url'];
                    $line = "";
                    $buffer = "";
                    $handle = @fopen( "$url", "r" );
                    if ( $handle )
                    {
                        while ( !feof( $handle ) )
                        {
                            $line = trim( @fgets( $handle, 4096 ) );
                            if ( isset( $param['return'] ) && $param['return'] == 'array' )
                            {
                                $buffer[] = explode( ",", $line );
                            }
                            else
                            {
                                $buffer .= $line . "\n";
                            }
                        }
                        fclose( $handle );
                        $this->buffer = $buffer;
                        return $buffer;
                    }
                }
                elseif ( $method == 'FC' )
                {
                    $url = $param['url'];
                    $buffer = trim( @file_get_contents( $url, 0, null ) );
                    $this->buffer = $buffer;
                    return $buffer;
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

    public function map( $arr = array( ) )
    {
        try
        {
            if ( empty( $arr ) )
            {
                if ( !isset( $this->data[0] ) )
                {
                    throw new Exception( '' );
                }
                else
                {
                    $arr = $this->data[0];
                }
            }
            foreach ( $arr as $k => $v )
            {
                if ( !isset( $this->$k ) )
                {
                    $this->$k = "";
                }
                $this->$k = $v;
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    public function find( $c = array( ) )
    {
        try
        {
            if ( empty( $c ) )
            {
                throw new Exception( '' );
            }
            $this->query = "SELECT * FROM " . $c[0];
            if ( isset( $c[1] ) )
            {
                $this->query .= " WHERE " . $c[1];
            }
            $this->run();
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }

    public function name( $param = null )
    {
        try
        {
            if ( $param == null )
            {
                throw new Exception( '' );
            }
            else
            {
                
            }
        }
        catch ( Exception $e )
        {
            echo $e->getMessage();
            exit;
        }
        return $this;
    }
}
?>
