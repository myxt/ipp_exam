<?php

$Module = array( 'name' => 'examen', 'variable_params' => true );

$ViewList = array();

$ViewList['action'] = array('functions' => array( 'read' ), 'script' => 'action.php' );

$ViewList['result'] = array('functions' => array( 'read' ), 'script' => 'result.php', 'params' => array( 'exam_id', 'hash' ) );

$ViewList['download'] = array('functions' => array( 'read' ), 'script' => 'download.php', 'params' => array( 'hash' ) );

$ViewList['exam'] = array('functions' => array( 'read' ), 'script' => 'exam.php' ,'params' => array( 'exam_id' ) );
$ViewList['statistics'] = array('functions' => array( 'read' ), 'script' => 'statistics.php','params' => array( 'exam_id' )  );

?>
