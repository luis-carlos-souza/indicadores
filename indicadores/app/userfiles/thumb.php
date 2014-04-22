<?php
	require_once( '../helpers/helper_canvas.php' );
	$pic = $_GET['img'];
	$t = new Canvas;
	$t->carrega( $pic );
	$t->redimensiona( 60, 60, 'crop' );
	$t->grava();
/*end file*/