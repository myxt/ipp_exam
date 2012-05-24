<?php
/**
 * File containing the examAnswer class.
 *
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */

class examAnswer extends eZPersistentObject
{
    function examAnswer( $row = array() )
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
										'default' => 0,
										'required' => true ),
						'question_id' => array( 'name' => 'questionID',
										'datatype' => 'integer',
										'default' => 0,
										'required' => true ),
						'priority' => array( 'name' => 'priority',
										'datatype' => 'integer',
										'default' => 0,
										'required' => true ),
						'option_id' => array( 'name' => 'option_id',
										'datatype' => 'integer',
										'default' => '',
										'required' => false ),
						'option_value' => array( 'name' => 'option_value',
										'datatype' => 'integer',
										'default' => '',
										'required' => false ),
						'correct' => array( 'name' => 'correct',
										'datatype' => 'integer',
										'default' => 0,
										'required' => false ),
						'content' => array( 'name' => 'content',
										'datatype' => 'string',
										'default' => '',
										'required' => false ),
						'version' => array( 'name' => 'version',
										'datatype' => 'integer',
										'default' => 0,
										'required' => true ),
						'language_code' => array( 'name' => 'languageCode',
										'datatype' => 'string',
										'default' => '',
										'required' => false )
					),
					'keys' => array( 'id' ),
					'function_attributes' => array('template_name' => 'templateName' ),
					'increment_key' => 'id',
					'class_name' => 'examAnswer',
					'sort' => array( 'id' => 'asc' ),
					'name' => 'exam_answer' );
		return $definition;
	}

    static function fetch( $id , $asObject = true )
    {
        $examAnswer = eZPersistentObject::fetchObject( examAnswer::definition(),
                                                                 null,
                                                                 array( 'id' => $id ),
                                                                 $asObject );
        return $examAnswer;
    }

	static function getConditions($id = 0, $version = 1, $languageCode = 'eng-GB')
	{
		$rows = eZPersistentObject::fetchObjectList( examAnswer::definition(),
											array('question_id', 'id', 'option_id','option_value'),
											array( 'contentobject_id' => $id,
													'version' => $version,
													'language_code' => $languageCode,
													'option_id' => array( "!=", "0" ),
													'option_value' => array( "!=", "" )
												),
											array( 'priority' => 'asc' ),
											null,
											true );
		return $rows;
	}

	function add( $contentobject_id, $question_id, $priority = 0, $option_id, $option_value, $correct, $content, $version, $language_code = "eng-GB" )
	{
		$newAnswer = new examAnswer();
		$newAnswer->setAttribute( 'contentobject_id', $contentobject_id );
		$newAnswer->setAttribute( 'question_id', $question_id );
		$newAnswer->setAttribute( 'priority', $priority );
		$newAnswer->setAttribute( 'option_id', $option_id );
		$newAnswer->setAttribute( 'option_value', $option_value );
		$newAnswer->setAttribute( 'correct', $correct );
		$newAnswer->setAttribute( 'content', $content );
		$newAnswer->setAttribute( 'version', $version );
		$newAnswer->setAttribute( 'language_code', $language_code );
		$newAnswer->store();
		return $newAnswer;
	}
	function removeAnswer()
	{
		$db = eZDB::instance();
		$db->begin();
		$query = "DELETE FROM `exam_answer` WHERE `id` = ".$this->ID;
		$db->query( $query );
		$db->commit();
	}
	public function removeAnswerByID( $id )
	{
		$answer = examAnswer::fetch( $id );
		$answer->removeAnswer();
	}
	function &templateName()
	{
		$element = examElement::fetch( $this->questionID );
		return $element->type;
	}
}

?>
