<?php
/**
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */

$Module = array( 'name' => 'examen', 'variable_params' => true );

$ViewList = array();

$ViewList['result'] = array('functions' => array( 'read' ), 'script' => 'result.php', 'params' => array( 'exam_id', 'hash' ) );
$ViewList['download'] = array('functions' => array( 'read' ), 'script' => 'download.php' );
$ViewList['exam'] = array('functions' => array( 'read' ), 'script' => 'exam.php' ,'params' => array( 'exam_id' ) );
$ViewList['statistics'] = array('functions' => array( 'read' ), 'script' => 'statistics.php','params' => array( 'exam_id' ), 'default_navigation_part' => 'examnavigationpart' );
$ViewList['send'] = array('functions' => array( 'read' ), 'script' => 'send.php', 'params' => array( 'exam_id', 'hash' ) );

$FunctionList = array();
$FunctionList['read'] = array();

?>
