<?php

class Cep{
    public function getend() {
        $options = array( 
            'location' => 'http://clareslab.com.br/cep2013/server.php',
            'uri' => 'http://clareslab.com.br/cep2013/',
            'encoding' => 'iso-8859-1' );
        $key = 'ZjUyODc2NGQ2MjRkYjEyOWIzMmMyMWZiY2EwY2I4ZDY=';
        $client = new SoapClient( null, $options );
        $endereco = trim( $_POST['endereco'] );
        echo $client->getEndereco( $endereco, 'json' , $key);
    }
    public function getcep() {
        $options = array( 
            'location' => 'http://clareslab.com.br/cep2013/server.php',
            'uri' => 'http://clareslab.com.br/cep2013/',
            'encoding' => 'iso-8859-1' );
        $key = 'ZjUyODc2NGQ2MjRkYjEyOWIzMmMyMWZiY2EwY2I4ZDY=';
        $client = new SoapClient( null, $options );
        $cep = trim( $_POST['cep'] );
        echo $client->getCep( $cep, 'json' , $key);
    }
    public function getendb() {
        $options = array( 
            'location' => 'http://clareslab.com.br/cep2013/server.php',
            'uri' => 'http://clareslab.com.br/cep2013/',
            'encoding' => 'iso-8859-1' );
        $key = 'ZjUyODc2NGQ2MjRkYjEyOWIzMmMyMWZiY2EwY2I4ZDY=';
        $client = new SoapClient( null, $options );
        $endereco = trim( $_POST['endereco'] );
        echo $client->getBairroCityUf( $endereco, 'json' , $key);
    }

}