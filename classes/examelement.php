<?php
//
//

/*! \file examelement.php
*/

class examElement extends eZPersistentObject
{
    function examElement( $row = array() )
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
		$id = $row['id'];
		$priority = $row['priority'];
//eZFire::debug($priority,"PRIORITY");
		$type = $row['type'];
//eZFire::debug($type,"TYPE");
		$parent = $row['parent'];
		$this->content = $this->getContent();
		$this->children = $this->getChildren();
		$this->answers = $this->getAnswers();
		$this->statistics = $this->getStats();
		$this->options = $this->getOptions();

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
						'priority' => array( 'name' => 'priority',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'type' => array( 'name' => 'type',
										'datatype' => 'string',
										'default' => '',
										'required' => true ),
						'parent' => array( 'name' => 'parent',
										'datatype' => 'integer',
										'default' => '0',
										'required' => false ),
						'xmloptions' => array( 'name' => 'xmlOptions',
										'datatype' => 'string',
										'default' => '',
										'required' => false ),
						'content' => array( 'name' => 'content',
										'datatype' => 'string',
										'default' => '',
										'required' => false ),
						'version' => array( 'name' => 'version',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'language_code' => array( 'name' => 'languageCode',
										'datatype' => 'string',
										'default' => '',
										'required' => false )
					),
					'keys' => array( 'id' ),
					'function_attributes' => array(  'template_name' => 'templateName', 'content' => 'content', 'children' => 'children', 'answers' => 'getAnswers', 'options' => 'getOptions', 'statistics' => 'getStats' ),
					'increment_key' => 'id',
					'class_name' => 'examElement',
					'sort' => array( 'id' => 'asc' ),
					'name' => 'exam_structure' );
		return $definition;
	}

	static function fetch( $id , $asObject = true )
	{

		$examElement = eZPersistentObject::fetchObject( examElement::definition(),
														null,
														array( 'id' => $id ),
														$asObject );
		return $examElement;
	}
	function content( $languageCode = "eng-GB" )
	{
		return $this->getContent( $languageCode );
	}
	function getContent( $languageCode = 'eng-GB' )
	{
		return $this->content;
	}
	function children()
	{
		return $this->getChildren();
	}
	function getChildren()
	{
		if ($this->type != "group" ) return;
		$rows = eZPersistentObject::fetchObjectList( examElement::definition(),
											null,
											array( 'parent' => $this->ID),
											array( 'priority' => 'asc' ),
											null,
											true );
		return $rows;
	}
	function getAnswers()
	{
//eZFire::debug(__FUNCTION__,"WHY AREN'T WE HERE");
		if ($this->type != "question" ) return;
		$rows = eZPersistentObject::fetchObjectList( examAnswer::definition(),
											null,
											array( 'question_id' => $this->ID),
											array( 'priority' => 'asc' ),
											null,
											true );
		return $rows;
	}
	static function getAnswerIDs( $id = 0, $version = 1, $languageCode = 'eng-GB' )
	{
		$rows = eZPersistentObject::fetchObjectList( examAnswer::definition(),
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

	public function statistics()
	{
		return $this->getStats;
	}

	function getStats()
	{
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
//eZFire::debug($this->type,"ELEMENT TYPE");
//eZFire::debug($this->ID,"QUESTION ID");
		if( $this->type != "question" ) return false;
		$db = eZDB::instance();
		$db->begin();
		$query = "SELECT COUNT(*) AS total FROM `exam_results` WHERE `question_id` = ".$this->ID;
		$queryResult = $db->arrayQuery( $query, array( 'limit' => 1, 'column' => 'total' ) );
		$result['total'] = $queryResult[0];
		$queryResult = "SELECT COUNT(*) AS first_pass FROM `exam_results` WHERE `question_id` = ".$this->ID." AND `correct` = 1 AND `followup` = 0";
		$queryResult = $db->arrayQuery( $query,array( 'limit' => 1, 'column' => 'first_pass' ) );
		$result['first_pass'] = $queryResult[0];
		$query = "SELECT COUNT(*) AS second_pass FROM `exam_results` WHERE `question_id` = ".$this->ID." AND `correct` = 1 AND `followup` = 1";
		$queryResult = $db->arrayQuery( $query,array( 'limit' => 1, 'column' => 'second_pass' ) );
		$result['second_pass'] = $queryResult[0];
		$query = "SELECT answer,COUNT(`answer`) as count FROM `exam_results` WHERE `question_id` = ".$this->ID." GROUP BY `answer`;";
		$queryResult = $db->arrayQuery( $query );
		foreach( $queryResult as $answer) {
			$result['answer_count'][$answer['answer']] = $answer['count'];
		}
//eZFire::debug($result,"RETURNING");
		return $result;
	}
	function priorityUp()
	{ //
		$this->getAnswerIDS;
		$oldPriority = $this->priority;
	}
	function priorityDown()
	{
		$oldPriority = $this->priority;
	}
	function &templateName()
	{
		$type = $this->type;
		return $type;
	}
	function add( $contentobject_id, $priority = 0, $type = "group", $parent = 0, $content, $version, $language_code )
	{
		$newElement = new examElement();
		$newElement->setAttribute( 'contentobject_id', $contentobject_id );
		$newElement->setAttribute( 'priority', $priority );
		$newElement->setAttribute( 'type', $type );
		$newElement->setAttribute( 'parent', $parent );
		$newElement->setAttribute( 'content', $content );
		$newElement->setAttribute( 'version', $version );
		$newElement->setAttribute( 'language_code', $language_code );
		$newElement->store();
//eZFire::debug("RETURNING ".$newElement);
		return $newElement;
	}
	function removeElement()
	{
		$db = eZDB::instance();
		$db->begin();
		/*check for children if group, answers if question and permission*/
		switch ($element->type) {
			case "question":
				$children = count( $element->getAnswers() );
				if ($children != 0 ) {
					$query = "DELETE FROM `exam_answer` WHERE `question_id` = ".$this->ID;
					$db->query( $query );
				}
				break;
			case "group":
				$children = count( $element->getChildren() );
				if ($children != 0 ) {
					$query = "DELETE FROM `exam_structure` WHERE `parent` = ".$this->ID;
					$db->query( $query );
				}
				break;
			default: // "text"|"pagebreak"
		}
		$query = "DELETE FROM `exam_structure` WHERE `id` = ".$this->ID;
		$db->query( $query );
		$db->commit();
	}
	function removeElementByID( $id )
	{
		$element = examElement::fetch( $id );
		$element->removeElement();
	}
	public function options()
	{
		return $this->getOptions();
	}
    function getOptions()
    { //At this point the only option is random... but we'll do it like this in the event there is some expansion
        if ( $this->xmlOptions != '' )
        {
            $options = array();
            $dom = new DOMDocument( '1.0', 'utf-8' );
            $dom->loadXML( $this->xmlOptions );
            $optionArray = $dom->getElementsByTagName( "option" );
            if ( $optionArray )
            {
                foreach ( $optionArray as $option )
                {
				$label = $option->getAttribute( "label" );
				$value = $option->getAttribute( "value" );
//eZFire::debug($option,"OPTINO");
//eZFire::debug($label,"LABLE");
				$options[$label] =  $value;
                }
			 return $options;
            }
        }

	}
	public function updateOption( $updateArray )
	{
	/* This updates the option xml. 
	 * <?xml version="1.0" encoding="utf-8"?>
	 * <options><option label="random" value="1"/></options>
	 */
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
//eZFire::debug($updateArray,"UPDATE ARRAY");
		//get existing
		$existingOptions = $this->getOptions();
		$dom = new DOMDocument( '1.0', 'utf-8' );
		$root = $dom->createElement("options");
//eZFire::debug($existingOptions,"OPTION ARRAY");
		//take care of existing
		if ( $existingOptions )
		{
//eZFire::debug("WE HAVE AN OPTION ARRAY");
			foreach ( $existingOptions as $key => $value )
			{	
//eZFire::debug($key." ".$value,"EXISTING OPTION LOADING");
//eZFire::debug($key." ".$value,"EXISTING OPTION LOADING");
				if ( $key != "" ) { //should never happen outside of test circumstances
					$root = $dom->appendChild($root);
					$node = $dom->createElement("option");
					$newnode = $root->appendChild($node);
					if ( array_key_exists($key, $updateArray) ) {
//eZFire::debug($updateArray[$key],"SHOULD BE SETTING NEW VALUE");
						$value = $updateArray[$key];
					}
					$newnode->setAttribute( "label", $key  );
					$newnode->setAttribute( "value", $value );
				}
			}
		}
		//load new

		$newAttributeArray = array_diff_key($updateArray,$existingOptions);
//eZFire::debug($newAttributeArray,"NEW ATTRIBUTE ARRAY");
		if ( $newAttributeArray )
		{
			$root = $dom->appendChild($root);
			foreach ( $newAttributeArray as $key => $value )
			{
				$node = $dom->createElement("option");
				$newnode = $root->appendChild($node);
				$newnode->setAttribute( "label", $key  );
				$newnode->setAttribute( "value", $value );
			}
		} 
		$xmlString = $dom->saveXML();
//eZFire::debug($xmlString,"XMLSTRING");

		$this->setAttribute( 'xmloptions', $xmlString );
		$this->store();
		return $xmlString;
    }
}

?>
