<?php

$Module = array( 'name' => 'examen', 'variable_params' => true );

$ViewList = array();

$ViewList['result'] = array('functions' => array( 'read' ), 'script' => 'result.php', 'params' => array( 'exam_id', 'hash' ) );
$ViewList['download'] = array('functions' => array( 'read' ), 'script' => 'download.php' );
$ViewList['exam'] = array('functions' => array( 'read' ), 'script' => 'exam.php' ,'params' => array( 'exam_id' ) );
$ViewList['statistics'] = array('functions' => array( 'read' ), 'script' => 'statistics.php','params' => array( 'exam_id' )  );
$ViewList['send'] = array('functions' => array( 'read' ), 'script' => 'send.php', 'params' => array( 'exam_id', 'hash' ) );


?>
