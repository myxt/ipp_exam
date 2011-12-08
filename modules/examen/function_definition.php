<?php
/**
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */

$FunctionList = array();
$FunctionList['examen'] = array(	'name' => 'examen',
							'call_method' => array( 'include_file' => 'extension/examen/classes/examen.php',
												'class' => 'exam',
												'method' => 'fetchExamById' ),
							'operation_types'	=> array( 'read' ),
							'parameter_type' => 'standard',
							'parameters' => array (	array (	'name' => 'id',
														'type' => 'integer',
														'required' => true ),
												array (	'name' => 'istplfetch',
														'type' => 'boolean',
														'default' => true,
														'required' => false )
											)
						);
$FunctionList['all'] = array(	'name' => 'all',
							'call_method' => array( 'include_file' => 'extension/examen/classes/examen.php',
												'class' => 'exam',
												'method' => 'fetchExams' ),
							'operation_types'	=> array( 'read' ),
							'parameter_type' => 'standard',
							'parameters' => array (array () ) );

$FunctionList['structure'] = array(	'name' => 'structure',
							'call_method' => array( 'include_file' => 'extension/examen/classes/examen.php',
												'class' => 'exam',
												'method' => 'getStructure' ),
							'operation_types'	=> array( 'read' ),
							'parameter_type' => 'standard',
							'parameters' => array (	array (	'name' => 'id',
														'type' => 'integer',
														'required' => true ),
												array (	'name' => 'version',
														'type' => 'integer',
														'required' => true ),
												array (	'name' => 'language_code',
														'type' => 'string',
														'required' => true ),
												array (	'name' => 'istplfetch',
														'type' => 'boolean',
														'default' => true,
														'required' => false )
											)
							);
$FunctionList['elements'] = array(	'name' => 'elements',
							'call_method' => array( 'include_file' => 'extension/examen/classes/examen.php',
												'class' => 'exam',
												'method' => 'getElements' ),
							'operation_types'	=> array( 'read' ),
							'parameter_type' => 'standard',
							'parameters' => array (	array (	'name' => 'id',
														'type' => 'integer',
														'required' => true ),
												array (	'name' => 'version',
														'type' => 'integer',
														'required' => true ),
												array (	'name' => 'language_code',
														'type' => 'string',
														'required' => true ),
												array (	'name' => 'istplfetch',
														'type' => 'boolean',
														'default' => true,
														'required' => false )
											)
							);
$FunctionList['element'] = array(	'name' => 'element',
							'call_method' => array( 'include_file' => 'extension/examen/classes/examelement.php',
												'class' => 'examElement',
												'method' => 'fetch' ),
							'operation_types'	=> array( 'read' ),
							'parameter_type' => 'standard',
							'parameters' => array (	array (	'name' => 'id',
														'type' => 'integer',
														'required' => true ),
												array (	'name' => 'asObject',
														'type' => 'boolean',
														'default' => true,
														'required' => false ),
												array (	'name' => 'istplfetch',
														'type' => 'boolean',
														'default' => true,
														'required' => false )
											)
							);
?>