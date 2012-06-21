<?php
/**
 * File containing the exam class.
 *
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */

class exam extends eZPersistentObject
{
	function exam( $row = array())
	{
		$this->eZPersistentObject( $row );
		$this->contentObject = $this->getObject(); //Where do we use this?
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
										'required' => true ),
						'score_tally' => array( 'name' => 'score_tally',
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
		if (is_object($this->contentObject))
		{
			$structure = $this->getStructure($this->contentObject->attribute( 'id' ),$this->contentObject->attribute( 'current_version' ),$this->contentObject->CurrentLanguage);
			//$contentObject = $this->getObject();
			$dataMap = $this->contentObject->DataMap();
			$random = $dataMap['random']->DataInt;
			if ( $random ) {
				shuffle($structure);
			}
			return $structure;
		} else {
			return false;
		}
	}
	static function getStructure( $id = 0, $version = 1, $languageCode = 'eng-GB', $istplfetch = false )
	{ //Only top level items.
		$rows = eZPersistentObject::fetchObjectList( examElement::definition(),
								null,
								array( 'contentobject_id' => $id,
										'parent' => 0,
										'version' => $version ,
										'language_code' => $languageCode ),
								array( 'priority' => 'asc' ),
								null,
								true );
		if ($istplfetch) return array( 'result' => $rows );
		else return $rows;
	}
	static function elements()
	{ //all elements - this isn't ever used
		return $this->getElements( $this->ContentObjectID, $this->ContentObject->version, $this->ContentObject->languageCode );
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
	static function getIDs( $id = 0, $version = 1, $languageCode = 'eng-GB' )
	{
		$idArray = array();
		$rows = eZPersistentObject::fetchObjectList( examElement::definition(),
											array('id'),
											array( 'contentobject_id' => $id,
													'version' => $version,
													'language_code' => $languageCode ),
											array( 'priority' => 'asc' ),
											null,
											false );
		foreach($rows as $row){
			$idArray[] = $row['id'];
		}
		return $idArray;
	}
	function getObject()
	{
		if ($this->contentObjectID) {
		$object = eZContentObject::fetch( $this->contentObjectID );
			if (is_object( $object )) {
				return $object;
			}
		} // else it's a draft
		return false;
	}

	public function questions()
	{
		return $this->getQuestions($this->contentObject->attribute( 'id' ),$this->contentObject->attribute( 'current_version' ),$this->contentObject->CurrentLanguage);
	}
	function getQuestions( $id = 0, $version = 1, $languageCode = 'eng-GB' )
	{
		$rows = eZPersistentObject::fetchObjectList( examElement::definition(),
								null,
								array( 'contentobject_id' => $id,
										'type' => "question",
										'version' => $version,
										'language_code' => $languageCode ),
								array( 'priority' => 'asc' ),
								null,
								true );
		return $rows;
	}

	function increment($attribute) {
		$old = $this->attribute( $attribute ) ? $this->attribute( $attribute ) : 0;
		$new = $old + 1;
		$this->setAttribute( $attribute, $new );
		$this->store();
		return $new;
	}
	function highScore( $score = 0 )
	{
		$old = $this->attribute( 'high_score' );
		if ( $score > $old ) {
			$this->setAttribute( 'high_score', $score );
			$this->store();
			return $score;
		}
		return $old;
	}
	function average()
	{//Return the average.
		return round( $this->score_tally / $this->count );
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
	static function removeSession( $http, $examID )
	{
		$http->removeSessionVariable( 'condition_array['.$examID.']' );
		$http->removeSessionVariable( 'exam_array['.$examID.']' );
		$http->removeSessionVariable( 'index['.$examID.']' );
		$http->removeSessionVariable( 'name['.$examID.']' );
		$http->removeSessionVariable( 'passed['.$examID.']' );
		$http->removeSessionVariable( 'result_array['.$examID.']' );
		$http->removeSessionVariable( 'score['.$examID.']' );
		$http->removeSessionVariable( 'status['.$examID.']' );
		$http->removeSessionVariable( 'mode['.$examID.']' );  //simple or complex
		$http->removeSessionVariable( 'timestamp['.$examID.']' );
		$http->removeSessionVariable( 'hash['.$examID.']' );
		$http->removeSessionVariable( 'count['.$examID.']' );
	}
	function removeVersion($id,$version,$language_code )
	{
		$examElements = $this->getElements($id, $version, $language_code);

		foreach($examElements as $elementObject) {
			$elementObject->removeElement();
		}
	}
}

?>
