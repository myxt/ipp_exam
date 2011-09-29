<?php

$FunctionList = array();
$FunctionList['examen'] = array(	'name' => 'examen',
							'call_method' => array( 'include_file' => 'extension/examen/classes/examen.php',
												'class' => 'exam',
												'method' => 'fetchExam' ),
							'operation_types'	=> array( 'read' ),
							'parameter_type' => 'standard',
							'parameters' => array (	array (	'name' => 'id',
														'type' => 'integer',
														'required' => true )
											)
						);
?>