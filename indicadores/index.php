<?php
error_reporting( E_ALL );
@header( "Cache-Control: no-cache, must-revalidate" );
@header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
@header( "Content-Type: text/html; charset=iso-8859-1" );
/*
$server_name = @preg_replace( '/www\./', '', $_SERVER['SERVER_NAME'] );
$server_addr = @current( explode( ".", $_SERVER['REMOTE_ADDR'] ) );
$allow = array( '127', '192', '10' );
if ( !in_array( $server_addr, $allow ) ):
    $server = array( 'server' => $server_name );
    $ch = @curl_init();
    @curl_setopt( $ch, CURLOPT_URL, "http://clareslab.com.br/ws/index/license/" );
    @curl_setopt( $ch, CURLOPT_VERBOSE, 0 );
    @curl_setopt( $ch, CURLOPT_HEADER, 0 );
    @curl_setopt( $ch, CURLOPT_POST, 1 );
    @curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    @curl_setopt( $ch, CURLOPT_POSTFIELDS, $server );
    $datac = @curl_exec( $ch );
    if ( !curl_errno( $ch ) ):
        if ( $datac != 1 ):
            echo $datac;
            exit;
        endif;
    endif;
    @curl_close( $ch );
endif;
*/
define( 'APP', 'app/' );
define( 'BASEURL', APP );
define( "VIEWSDIR", APP . "views/" );
define( "HELPERDIR", APP . "helpers/" );
define( "LIBDIR", APP . "lib/" );
define( "DATABASEDIR", APP . "database/" );
define( "REALPATH", dirname( __FILE__ ) );
define( "REALPATH_APP", dirname( __FILE__ ) . "/" . APP );
if ( !file_exists( LIBDIR . 'PHPFrodo.php' ) )
{
    echo LIBDIR . "/PHPFrodo.php não encontrado!";
    exit;
}
require_once LIBDIR . 'PHPFrodo.php';
if ( isset( $_GET['route'] ) )
{
    $routes = explode( "/", $_GET['route'] );
    $is_dir = false;
    foreach ( $routes as $r )
    {
        $dir = REALPATH_APP . "$r/";
        if ( $r != "" && is_dir( $dir ) )
        {
            define( 'CTRL', "$r" );
            $is_dir = true;
        }
    }
    $class = $routes[0];
    if ( $is_dir == true )
    {
        if ( !isset( $routes[1] ) || empty( $routes[1] ) )
        {
            $routes[1] = 'index';
        }
        $class = $routes[1];
    }
    else
    {
        define( 'CTRL', APP );
    }
    if ( !isset( $routes[1] ) || empty( $routes[1] ) )
    {
        $routes[1] = 'welcome';
    }
    $action = $routes[1];
    if ( $is_dir == true && isset( $routes[2] ) && !empty( $routes[2] ) )
    {
        $action = $routes[2];
    }
    $class = strtolower($class);
    $obj = new $class;
    ( method_exists( $obj, $action ) ) ? $obj->$action() : $obj->welcome();
}

function __autoload( $class )
{
    if ( $class != 'finfo' )
    {
        if ( CTRL != APP )
        {
            $classFile = APP . CTRL . '/' . $class . '.php';
        }
        else
        {
            $classFile = APP . $class . '.php';
        }
        $classFile = strtolower($classFile);
        if ( file_exists( $classFile ) )
        {
            if ( file_exists( LIBDIR . $class . '.php' ) )
            {
                echo "A classe $class tem o nome reservado e já existe em " . LIBDIR;
                exit;
            }
            require_once $classFile;
        }
        elseif ( file_exists( LIBDIR . $class . '.php' ) )
        {
            $classFile = LIBDIR . $class . '.php';
            require_once $classFile;
        }
        else
        {
            $U404 = explode( "index.php", $_SERVER['PHP_SELF'] );
            $U404 = $U404[0] . "404.php";
            @header( "Location: $U404" );
        }
    }
}
?>