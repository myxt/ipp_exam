<?php

$Module = array( 'name' => 'examen', 'variable_params' => true );

$ViewList = array();

$ViewList['result'] = array('functions' => array( 'read' ), 'script' => 'result.php', 'params' => array( 'hash' ) );

$ViewList['download'] = array('functions' => array( 'read' ), 'script' => 'download.php', 'params' => array( 'hash' ) );

$ViewList['exam'] = array('functions' => array( 'read' ), 'script' => 'exam.php' );
$ViewList['statistics'] = array('functions' => array( 'read' ), 'script' => 'statistics.php' );

?>
