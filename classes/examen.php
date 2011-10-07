<?php
//
// ## BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
// SOFTWARE NAME: examen
// SOFTWARE RELEASE: 1.0.0
// COPYRIGHT NOTICE: Copyright (C) 2011 Leiden Tech, Myxt
//
// ## END COPYRIGHT, LICENSE AND WARRANTY NOTICE ##
//

class exam extends eZPersistentObject
{
	function exam( $row = array())
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
		$this->contentObject = $this->getObject(); //This won't work with drafts - not sure it should be here
		$this->structure = $this->structure();
		//$this->elements = $this->getElements();
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
						'count' => array( 'name' => 'count',
										'datatype' => 'string',
										'default' => '',
										'required' => true ),
						'pass_first' => array( 'name' => 'passFirst',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'pass_second' => array( 'name' => 'passSecond',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'enabled' => array( 'name' => 'enabled',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'high_score' => array( 'name' => 'high_score',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true )
					),
					'keys' => array( 'id' ),
					'function_attributes' => array(  'structure' => 'structure', 'questions' => 'questions', 'elements' => 'getElements', 'contentObject' => 'getObject' ),
					'increment_key' => 'id',
					'class_name' => 'exam',
					'sort' => array( 'id' => 'asc' ),
					'name' => 'exam_statistics' );
		return $definition;
	}

	static function fetch( $id, $asObject = true )
	{ //This is actually fetch by contentobject_id
		$examObject = eZPersistentObject::fetchObject(
										exam::definition(),
										null,
										array( 'contentobject_id' => $id ),
										$asObject );
		return $examObject;
	}

	function fetchExamById( $id )
	{
		$examObject = eZPersistentObject::fetchObject(
										exam::definition(),
										null,
										array( 'id' => $id ),
										$asObject );
		if ($istplfetch) return array( 'result' => $examObject );
		else return $examObject;
	}
	function fetchExams( $id )
	{
		$examObjects = eZPersistentObject::fetchObjectList( exam::definition(),
								null,
								array(),
								null,
								true );
		return $examObjects;
	}
	public function structure() 
	{ //This returns the current version structure.  Can't be used with edit etc.  This maintains the heirarchy.

eZFire::debug(__FUNCTION__,"WE ARE HERE");
eZFire::debug($this->contentObject,"CONTENT OBJECT");
		if (is_object($this->contentObject))
		{
		eZFire::debug($this->contentObject->attribute( 'id' ),"id");
		eZFire::debug($this->contentObject->attribute( 'current_version' ),"structure version");
		eZFire::debug($this->contentObject->CurrentLanguage,"struture language");

		//return false;
				return $this->getStructure($this->contentObject->attribute( 'id' ),$this->contentObject->attribute( 'current_version' ),$this->contentObject->CurrentLanguage);
		} else {
			return false;
		}
	}
	function getStructure( $id = 0, $version = 1, $languageCode = 'eng-GB', $istplfetch = false )
	{ //Only top level items.
eZFire::debug(__FUNCTION__,"WE ARE HERE");
eZFire::debug($this->contentObject,"CONTENT OBJECT");
eZFire::debug($id,"id");
eZFire::debug($version,"structure version");
eZFire::debug($languageCode,"struture language");
		$rows = eZPersistentObject::fetchObjectList( examElement::definition(),
								null,
								array( 'contentobject_id' => $id,
										'parent' => 0,
										'version' => $version ,
										'language_code' => $languageCode ),
								array( 'priority' => 'asc' ),
								null,
								true );
eZFire::debug(count($rows),"ROW COUNT");
		if ($istplfetch) return array( 'result' => $rows );
		else return $rows;
	}
	static function elements()
	{ //all elements
		$this->getElements( $this->ContentObjectID, $this->ContentObject->version, $this->ContentObject->languageCode );
	}
	static function getElements( $id = 0, $version = 1, $languageCode = 'eng-GB', $istplfetch = false )
	{ //all elements
		$rows = eZPersistentObject::fetchObjectList( examElement::definition(),
										null,
										array( 'contentobject_id' => $id,
												'version' => $version,
												'language_code' => $languageCode ),
										array( 'priority' => 'asc' ),
										null,
										true );
		if ($istplfetch) return array( 'result' => $rows );
		else return $rows;
	}
	function getObject()
	{
//eZFire::debug($this->contentObjectID,"WE BE HEEAH");
		return eZContentObject::fetch( $this->contentObjectID );
	}
	public function questions()
	{
		//$contentObjectID = $this->contentObjectID;
		return $this->getQuestions();
	}
	function getQuestions( $languageCode = 'eng-GB' )
	{ //Only top level items.
		$rows = eZPersistentObject::fetchObjectList( examElement::definition(),
								null,
								array( 'contentobject_id' => $this->contentObjectID,
										'type' => "question",
										'language_code' => $languageCode ),
								array( 'priority' => 'asc' ),
								null,
								true );
		return $rows;
	}

	function increment($attribute) {
		$old = $this->attribute( $attribute );
		$new = $old++;
		$this->setAttribute( $attribute, $new );
		$this->store();
		return $new;
	}
	function highScore( $score = 0 )
	{
		$old = $this->attribute( 'high_score' );
		if ( $score > $old ) {
			$this->setAttribute( $attribute, $score );
			$this->store();
			return $score;
		}
		return $old;
	}

	public function __clone()
	{ //used by copy

	}

	function validateEditActions( $validation, $params )
	{ //called by validateObjectAttributeHTTPInput
	}
	function removeExam()
	{ //called by deleteStoredObjectAttribute
		$db = eZDB::instance();
		$db->begin();
		$query = "DELETE FROM `exam_statistics` WHERE `contentobject_id` = ".$this->contentObjectID;
		$db->query( $query );
		$query = "DELETE FROM `exam_structure` WHERE `contentobject_id` = ".$this->contentObjectID;
		$db->query( $query );
		$query = "DELETE FROM `exam_answer` WHERE `contentobject_id` = ".$this->contentObjectID;
		$db->query( $query );
		$query = "DELETE FROM `exam_results` WHERE `contentobject_id` = ".$this->contentObjectID;
		$db->query( $query );
		$db->commit();
	}
	function removeVersion($id,$version,$language_code )
	{
		$examElements = $this->getElements($originalContentObjectAttribute->attribute( 'contentobject_id' ),$originalContentObjectAttribute->attribute( 'version' ),$originalContentObjectAttribute->attribute( 'language_code' ));
		foreach($examElements as $elementObject) {
			$elementObject->removeExam();
			if ($elementObject->type == 'question' ) {
				foreach( $elementObject->getAnswers as $answer ) {
					$answer->removeExam();
				}
			}
		}
	}
}

?>
