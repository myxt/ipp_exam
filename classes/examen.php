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

//eZFire::debug(__FUNCTION__,"WE ARE HERE");
//eZFire::debug($this->contentObject,"CONTENT OBJECT");
		if (is_object($this->contentObject))
		{
			$structure = $this->getStructure($this->contentObject->attribute( 'id' ),$this->contentObject->attribute( 'current_version' ),$this->contentObject->CurrentLanguage);
			if ( $this->contentObject.data_map.random.data_int ) {
				shuffle($structure);
			}
			return $structure;
		} else {
			return false;
		}
	}
	function getStructure( $id = 0, $version = 1, $languageCode = 'eng-GB', $istplfetch = false )
	{ //Only top level items.
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
//eZFire::debug($this->contentObject,"CONTENT OBJECT");
//eZFire::debug($id,"id");
//eZFire::debug($version,"structure version");
//eZFire::debug($languageCode,"struture language");

		$rows = eZPersistentObject::fetchObjectList( examElement::definition(),
								null,
								array( 'contentobject_id' => $id,
										'parent' => 0,
										'version' => $version ,
										'language_code' => $languageCode ),
								array( 'priority' => 'asc' ),
								null,
								true );
//eZFire::debug(count($rows),"ROW COUNT");
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
//eZFire::debug($this->contentObjectID,"WE BE HEEAH");
		return eZContentObject::fetch( $this->contentObjectID );
	}
	public function questions()
	{
		//$contentObjectID = $this->contentObjectID;
		return $this->getQuestions();
	}
	function getQuestions( $languageCode = 'eng-GB' )
	{
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

	public function __clone()
	{ //used by copy

	}

	function validateEditActions( $validation, $params )
	{ //called by validateObjectAttributeHTTPInput
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
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
		$examElements = $this->getElements($id, $version, $language_code);

		foreach($examElements as $elementObject) {
			$elementObject->removeElement();
		}
	}
	function examArray($examID,$examVersion,$examLanguage)
	{ //Need this for both the examen module and for validation.
		$examElements = $this->getStructure($examID,$examVersion,$examLanguage );
//eZFire::debug($examElements,"EXAM STRUCTURE");
		//but we don't want to shuffle if there are pagebreaks, except if the pagebreak is the last element.
		//Doesn't make much sense to shuffle text blocks either.  I can only really see textblocks as being useful as a condition or for 
		//a non-random exam..
		$random=true;
		$conditionObjectArray = $this->getConditions($examID,$examVersion,$examLanguage);
		/* Conditions
			if [not] picked	Remove			text, group, question 1 5
			if [not] picked	Add				text, group, question 2 6
			if [not] picked	Follow With		text, group, question 3 7
			if [not] picked	Display in Resuts	text				  4 8 

			Conditions that override Random UNLESS the <conditional element> is in the same group and the group is NOT random and the priorty of the question is less than the <conditional element>.  Since a group cannot be a member of a group it will always override random
				if [not] picked	Remove
				if [not] picked	Follow With
			1 5 3 7
			Conditions that imply that the element must be removed from the initial list
				if [not] picked	Add
				if [not] picked	Display in Resuts
			2 6 4 8
		*/
		foreach($conditionObjectArray as $condition) {
//eZFire::debug($condition->option_id,"CONDITION OPTION ID");
//eZFire::debug($condition->option_value,"CONDITION VALUE ID");
			switch ($condition->option_id) { //This could be a mod at this point but I have a funny feeling this will be extended
				case 1:
				case 5:
					$random=false;
					break;
				case 3:
				case 7:
					//Have to put these in the check array so that we can check on them
					$answerConditionArray[$condition->option_value] = array( 'answer_id' => $condition->ID, 'option_id' =>  $condition->option_id, 'option_value' => $condition->option_value );
					$random=false;
					break;
				case 2:
				case 4:
				case 6:
				case 8:
//eZFire::debug($condition->option_value,"IN THE CASE?");
					$conditionRemoveArray[] = $condition->option_value;
					break;
			}
			/*Gotta match on the question id to be able to do the NOT*/
//eZFire::debug($condition,"CONDITION");
			$answerConditionArray[$condition->questionID] = array( 'answer_id' => $condition->ID, 'option_id' =>  $condition->option_id, 'option_value' => $condition->option_value );
		}
//eZFire::debug($answerConditionArray,"ANSWER CONDITON ARRAY");
		$elementCount = count($examElements);

		/*Check if anything overrides random*/
		if ( $dataMap["random"]->DataInt == 1 AND $random == true ) {
			
			foreach($examElements as $ElementIndex => $element) {
				if ($element.type == "pagebreak") {//If there is any top-level pagebreak that is NOT the last element... random has to be turned off.
						if ( $ElementIndex != $elementCount ) {
							$random = false;
						}
						break; //don't need more than one
				}
				if ($element.type == "question") {//parse conditiosn
						if ( $ElementIndex != $elementCount ) {
							$random = false;
						}
						break; //don't need more than one
				}
				if ($element.type == "group") {//Do it all again for the children, sigh.
						if ( $ElementIndex != $elementCount ) {
							$random = false;
						}
						break; //don't need more than one
				}
			}
			if ( $random ) {
//eZFire::debug("WE HAVE RANDOM");
				shuffle($examElements);
			}
		}

		foreach($examElements as $element) {
			if( in_array( $element->ID, $conditionRemoveArray ) ) {
//eZFire::debug($element->ID,"THIS SHOULD BE REMOVED BECAUSE ITS A CONDITION");
				continue;
			}
			switch($element->type) {
				case "pagebreak":
					//if (!$random) { //if it's random we can toss because we can't use it anyway
						$examArray[]=array($element->ID , "" );
					//}
					break;
				case "text":
					$examArray[]=array($element->ID , "" );	
					break;
				case "question":
					$examArray[]=array($element->ID , "" );	
					$questionCount++;
					break;
				case "group": //Now we have to recursively do the whole thing again, doh
					if ( $element->option->random == 1 ) {
						$childRandom = true;
						$children = $element->children;
						$childrenCount = count($children);
						foreach($children as $childIndex => $child) {
							if ($child->type == "pagebreak") {//If there is a pagebreak that is NOT the last member of the group... random has to be turned off.
								if ( $childIndex != $childrenCount ) {
									$childRandom = false;
								}
								break; //don't need more than one
							}
						}
						if ( $childRandom == true )
							shuffle($children);
					}
					foreach($children as $child) {
						switch($child->type) {
							case "pagebreak":
								if (!$random) { //if it's random we can toss because we can't use it anyway
									$groupArray[] = array($child->ID , "" );
								}
								break;
							case "text":
									$groupArray[] = array($child->ID , "" );
							case "question":
									$groupArray[] = array($child->ID , "" );
									$questionCount++;
								break;
							case "group": 
								break;
						}
					}
					$examArray = array_merge($examArray, $groupArray);
				break;
			} //end switch
		} //end foreach
		return array($examArray,$answerConditionArray);
	}
}

?>
