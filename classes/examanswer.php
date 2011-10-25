<?php
//
//

/*! \file examanswer.php
*/

class examAnswer extends eZPersistentObject
{
    function examAnswer( $row = array() )
    {
		$this->eZPersistentObject( $row );
		$this->ClassIdentifier = false;
		if ( isset( $row['contentclass_identifier'] ) )
			$this->ClassIdentifier = $row['contentclass_identifier'];
		$this->ClassName = false;
		if ( isset( $row['contentclass_name'] ) )
			$this->ClassName = $row['contentclass_name'];
		if ( isset( $row['serialized_name_list'] ) )
			$this->ClassName = eZContentClass::nameFromSerializedString( $row['serialized_name_list'] );

		$this->CurrentLanguage = false;
		if ( isset( $row['content_translation'] ) )
		{
			$this->CurrentLanguage = $row['content_translation'];
		}
		else if ( isset( $row['real_translation'] ) )
		{
			$this->CurrentLanguage = $row['real_translation'];
		}
		else if ( isset( $row['language_mask'] ) )
		{
			$topPriorityLanguage = eZContentLanguage::topPriorityLanguageByMask( $row['language_mask'] );
			if ( $topPriorityLanguage )
			{
			$this->CurrentLanguage = $topPriorityLanguage->attribute( 'locale' );
			}
		}
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
	function content( $languageCode = "eng-GB" )
	{
		return $this->getContent( $languageCode );
	}
	function getContent($languageCode = "eng-GB" )
	{
		$rows = eZPersistentObject::fetchObjectList( examAnswer::definition(),
											null,
											array( 'id' => $this->ID),
											array( 'priority' => 'asc' ),
											null,
											true );
		return $rows;
	}

	function getConditions($id = 0, $version = 1, $languageCode = 'eng-GB')
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
//eZFire::debug($contentobject_id,"OBJECT ID IN ANSWER ADD");
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
//eZFire::debug($newAnswer->ContentObjectID,"WTF");
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
