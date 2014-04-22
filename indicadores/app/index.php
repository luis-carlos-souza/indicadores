<?php

//echo base64_decode("MTJxd2Fzeng=");exit;
class Index extends Acesso
{

    public function __construct()
    {
        parent:: __construct();
        $sid = new Session;
        $sid->start();
        if ( !$sid->check( "$this->baseUri" ) )
        {
            $this->redirect( "$this->baseUri/login/" );
        }
        $this->user_name = $sid->getNode( 'user_name' );
        $this->user_id = $sid->getNode( 'user_id' );
        $this->user_login = $sid->getNode( 'user_login' );
        $this->assign( 'user_name', strtoupper( $this->user_name ) );
    }

    public function welcome()
    {

        $this->tpl( 'public/home.html' );
        $this->render();
    }

    public function campo()
    {
        $this->tpl( 'public/campo.html' );
        $this->perfis = array( 1 => 'ST', 3 => 'SP', 5 => 'ST', 6 => 'SM', 7 => 'SR' );
        $this->perfil = "";
        $data_s = '2011-01-01';
        $data_e = date( 'Y' ) . '-12-31';

        if ( isset( $_POST['data_e'] ) )
        {
            $data_e = preg_replace( '/\//', '-', $_POST['data_e'] );
            $data_e = date( 'Y-m-d', strtotime( "$data_e" ) );
        }
        if ( isset( $_POST['data_s'] ) )
        {
            $data_s = preg_replace( '/\//', '-', $_POST['data_s'] );
            $data_s = date( 'Y-m-d', strtotime( "$data_s" ) );
        }
        $query = "SELECT MONTH(dt_insercao) AS mes, YEAR(dt_insercao) AS ano, ";
        $query .= "count(dt_insercao) AS total FROM perfil ";
        $query .= "INNER JOIN furo ON (furo.id_furo = perfil.id_furo) ";
        $dataCond = " dt_insercao >= '$data_s' AND dt_insercao <= '$data_e' ";
        $check_load = "'PI','ST','SM','SR','SP'";
        if ( in_array( "campo", $this->uri ) && isset( $this->uri[2] ) )
        {
            $this->furo = $this->uri[2];
            $furos = explode( "-", $this->furo );
            $this->perfil = "[" . implode( ",", $furos ) . "]";
            $query .= " WHERE ";
            foreach ( $furos as $f )
            {
                $query .= "  furo.tipo = '$f' AND $dataCond OR ";
            }
            $query = substr( $query, 0, -3 );
            $check_load = "'" . implode( "','", $furos ) . "'";
        }
        else
        {
            $query .= "WHERE dt_insercao >= '$data_s' AND dt_insercao <= '$data_e' ";
        }
        $query .= "GROUP BY mes, ano ";
        $query .= "ORDER BY ano, mes";
        $_meses = array( '', 'JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ' );
        $this->query = "$query";
        $this->method = "select";
        $this->run();
        if ( $this->result() )
        {
            $arr_ano = array( );
            $arr_mes = array( );
            $arr_total = array( );
            foreach ( $this->data as $k => $v )
            {
                $this->data[$k]['_mes'] = $_meses[$this->data[$k]['mes']];
                $_ano = $this->data[$k]['ano'];
                if ( !in_array( "'$_ano'", $arr_ano ) )
                {
                    $arr_ano[] = "'$_ano'";
                }
                $arr_ano_mes["$_ano"]['total'][] = $this->data[$k]['total'];
            }
            $final = "";
            $final .= "['Mes'," . implode( ",", $arr_ano ) . "],";
            $i = 1;
            $j = 0;
            foreach ( $arr_ano_mes as $m )
            {
                foreach ( $m as $k => $v )
                {
                    $jan[] = (isset( $v[0] )) ? $v[0] : $v[0] = 0;
                    $fev[] = (isset( $v[1] )) ? $v[1] : $v[1] = 0;
                    $mar[] = (isset( $v[2] )) ? $v[2] : $v[2] = 0;
                    $abr[] = (isset( $v[3] )) ? $v[3] : $v[3] = 0;
                    $mai[] = (isset( $v[4] )) ? $v[4] : $v[4] = 0;
                    $jun[] = (isset( $v[5] )) ? $v[5] : $v[5] = 0;
                    $jul[] = (isset( $v[6] )) ? $v[6] : $v[6] = 0;
                    $ago[] = (isset( $v[7] )) ? $v[7] : $v[7] = 0;
                    $set[] = (isset( $v[8] )) ? $v[8] : $v[8] = 0;
                    $out[] = (isset( $v[9] )) ? $v[9] : $v[9] = 0;
                    $nov[] = (isset( $v[10] )) ? $v[10] : $v[10] = 0;
                    $dez[] = (isset( $v[11] )) ? $v[11] : $v[11] = 0;
                    $i++;
                    $j++;
                }
            }
            $final .= "['JAN'," . implode( ",", $jan ) . "],";
            $final .= "['FEV'," . implode( ",", $fev ) . "],";
            $final .= "['MAR'," . implode( ",", $mar ) . "],";
            $final .= "['ABR'," . implode( ",", $abr ) . "],";
            $final .= "['MAI'," . implode( ",", $mai ) . "],";
            $final .= "['JUN'," . implode( ",", $jun ) . "],";
            $final .= "['JUL'," . implode( ",", $jul ) . "],";
            $final .= "['AGO'," . implode( ",", $ago ) . "],";
            $final .= "['SET'," . implode( ",", $set ) . "],";
            $final .= "['OUT'," . implode( ",", $out ) . "],";
            $final .= "['NOV'," . implode( ",", $nov ) . "],";
            $final .= "['DEZ'," . implode( ",", $dez ) . "]";
            $this->assign( 'strJson', $final );
        }
        $this->assign( 'titulo', "Sondagens e Ensaios de Campo" );
        $this->assign( 'check_load', "$check_load" );
        $this->assign( 'data_e', date( 'd/m/Y', strtotime( $data_e ) ) );
        $this->assign( 'data_s', date( 'd/m/Y', strtotime( $data_s ) ) );
        $this->render();
    }

    public function laboratorio()
    {
        $this->tpl( 'public/lab.html' );
        $this->perfis = array( 1 => 'L', 3 => 'G', 5 => 'C', 6 => 'M');
        $this->perfil = "";
        $data_s = '2011-01-01';
        $data_e = date( 'Y' ) . '-12-31';

        if ( isset( $_POST['data_e'] ) )
        {
            $data_e = preg_replace( '/\//', '-', $_POST['data_e'] );
            $data_e = date( 'Y-m-d', strtotime( "$data_e" ) );
        }
        if ( isset( $_POST['data_s'] ) )
        {
            $data_s = preg_replace( '/\//', '-', $_POST['data_s'] );
            $data_s = date( 'Y-m-d', strtotime( "$data_s" ) );
        }
        $query = "SELECT MONTH(dt_insercao) AS mes, YEAR(dt_insercao) AS ano, ";
        $query .= "count(dt_insercao) AS total FROM resultado_ensaios ";
        $query .= "INNER JOIN ensaios ON (ensaios.id_ensaio = resultado_ensaios.id_ensaio) ";
        $dataCond = " dt_insercao >= '$data_s' AND dt_insercao <= '$data_e' ";
        $check_load = "'L','G','C','M'";
        if ( in_array( "laboratorio", $this->uri ) && isset( $this->uri[2] ) )
        {
            $this->furo = $this->uri[2];
            $furos = explode( "-", $this->furo );
            $this->perfil = "[" . implode( ",", $furos ) . "]";
            $query .= " WHERE ";
            foreach ( $furos as $f )
            {
                $query .= "  ensaios.tipo = '$f' AND $dataCond OR ";
            }
            $query = substr( $query, 0, -3 );
            $check_load = "'" . implode( "','", $furos ) . "'";
        }
        else
        {
            $query .= "WHERE dt_insercao >= '$data_s' AND dt_insercao <= '$data_e' ";
        }
        $query .= "GROUP BY mes, ano ";
        $query .= "ORDER BY ano, mes";
        $_meses = array( '', 'JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ' );
        $this->query = "$query";
        $this->method = "select";
        $this->run();
        if ( $this->result() )
        {
            $arr_ano = array( );
            $arr_mes = array( );
            $arr_total = array( );
            foreach ( $this->data as $k => $v )
            {
                $this->data[$k]['_mes'] = $_meses[$this->data[$k]['mes']];
                $_ano = $this->data[$k]['ano'];
                if ( !in_array( "'$_ano'", $arr_ano ) )
                {
                    $arr_ano[] = "'$_ano'";
                }
                $arr_ano_mes["$_ano"]['total'][] = $this->data[$k]['total'];
            }
            $final = "";
            $final .= "['Mes'," . implode( ",", $arr_ano ) . "],";
            $i = 1;
            $j = 0;
            foreach ( $arr_ano_mes as $m )
            {
                foreach ( $m as $k => $v )
                {
                    $jan[] = (isset( $v[0] )) ? $v[0] : $v[0] = 0;
                    $fev[] = (isset( $v[1] )) ? $v[1] : $v[1] = 0;
                    $mar[] = (isset( $v[2] )) ? $v[2] : $v[2] = 0;
                    $abr[] = (isset( $v[3] )) ? $v[3] : $v[3] = 0;
                    $mai[] = (isset( $v[4] )) ? $v[4] : $v[4] = 0;
                    $jun[] = (isset( $v[5] )) ? $v[5] : $v[5] = 0;
                    $jul[] = (isset( $v[6] )) ? $v[6] : $v[6] = 0;
                    $ago[] = (isset( $v[7] )) ? $v[7] : $v[7] = 0;
                    $set[] = (isset( $v[8] )) ? $v[8] : $v[8] = 0;
                    $out[] = (isset( $v[9] )) ? $v[9] : $v[9] = 0;
                    $nov[] = (isset( $v[10] )) ? $v[10] : $v[10] = 0;
                    $dez[] = (isset( $v[11] )) ? $v[11] : $v[11] = 0;
                    $i++;
                    $j++;
                }
            }
            $final .= "['JAN'," . implode( ",", $jan ) . "],";
            $final .= "['FEV'," . implode( ",", $fev ) . "],";
            $final .= "['MAR'," . implode( ",", $mar ) . "],";
            $final .= "['ABR'," . implode( ",", $abr ) . "],";
            $final .= "['MAI'," . implode( ",", $mai ) . "],";
            $final .= "['JUN'," . implode( ",", $jun ) . "],";
            $final .= "['JUL'," . implode( ",", $jul ) . "],";
            $final .= "['AGO'," . implode( ",", $ago ) . "],";
            $final .= "['SET'," . implode( ",", $set ) . "],";
            $final .= "['OUT'," . implode( ",", $out ) . "],";
            $final .= "['NOV'," . implode( ",", $nov ) . "],";
            $final .= "['DEZ'," . implode( ",", $dez ) . "]";
            $this->assign( 'strJson', $final );
        }
        $this->assign( 'titulo', "Ensaios de Laboratório" );
        $this->assign( 'check_load', "$check_load" );
        $this->assign( 'data_e', date( 'd/m/Y', strtotime( $data_e ) ) );
        $this->assign( 'data_s', date( 'd/m/Y', strtotime( $data_s ) ) );
        $this->render();
    }
}