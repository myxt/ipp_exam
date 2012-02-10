<?php
/**
 * File containing the examResult class.
 *
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */

class examResult extends eZPersistentObject
{
    function examResult( $row = array() )
    {
        $this->eZPersistentObject( $row );
    }

	static function definition()
	{
		$definition = array(
					'fields' => array(
						'id' => array(	'name' => 'ID',
									'datatype' => 'integer',
									'default' => 0,
									'required' => true ),
						'contentobject_id' => array( 'name' => 'contentObjectID',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'hash' => array( 'name' => 'hash',
										'datatype' => 'string',
										'default' => '',
										'required' => true ),
						'question_id' => array( 'name' => 'questionID',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'answer' => array( 'name' => 'answer',
										'datatype' => 'string',
										'default' => '',
										'required' => true ),
						'correct' => array( 'name' => 'correct',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'followup' => array( 'name' => 'followup',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'conditional' => array( 'name' => 'conditional',
										'datatype' => 'integer',
										'default' => null,
										'required' => false )
					),
					'keys' => array( 'id' ),
					'function_attributes' => array( 'content' => 'content', 'template_name' => 'templateName' ),
					'increment_key' => 'id',
					'class_name' => 'examResult',
					'sort' => array( 'id' => 'asc' ),
					'name' => 'exam_results' );
		return $definition;
	}
	static function fetch( $id , $asObject = true )
	{ //This isn't actually used anywhere is it.

		$examResult = eZPersistentObject::fetchObject( examResult::definition(),
														null,
														array( 'question_id' => $id ),
														$asObject );
		return $examResult;
	}
	static function fetchByHash( $hash, $exam_id, $asObject = true )
	{
		$examResult = eZPersistentObject::fetchObjectList( examResult::definition(),
														null,
														array( 'hash' => $hash , 'contentobject_id' => $exam_id ),
														array( 'question_id' => 'asc', 'followup' => 'desc' ),
														$asObject
											);
		return $examResult;
	}
	static function fetchSurvey( $exam_id, $asObject = true )
	{
		$examResult = eZPersistentObject::fetchObjectList( examResult::definition(),
														null,
														array( 'contentobject_id' => $exam_id ),
														array( 'question_id' => 'asc', 'answer' => 'asc' ),
														$asObject
											);
		return $examResult;
	}
	static function fetchSurveyQuestionCount( $exam_id, $question_id, $asObject = true )
	{
		$examResult = eZPersistentObject::fetchObjectList( examResult::definition(),
														null,
														array( 'contentobject_id' => $exam_id, 'question_id' => $question_id, 'answer' => array( "!=", "0" ) )
											);
		return count($examResult);
	}
	static function fetchSurveyAnswerCount( $exam_id, $answer_id, $asObject = true )
	{
		$examResult = eZPersistentObject::fetchObjectList( examResult::definition(),
														null,
														array( 'question_id' => $exam_id, 'answer' => $answer_id )
											);
		return count($examResult);
	}
	function &templateName()
	{
		$element = examElement::fetch( $this->questionID );
		return $element->type;
	}
}

?>
