<?php
/**
 * File containing the examElement class.
 *
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */

class examElement extends eZPersistentObject
{
    function examElement( $row = array() )
    {
        $this->eZPersistentObject( $row );
		$id = $row['id'];
		$priority = $row['priority'];
		$type = $row['type'];
		$parent = $row['parent'];
		$this->content = $this->getContent();
		$this->children = $this->getChildren();
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
					'function_attributes' => array(  'template_name' => 'templateName', 'content' => 'content', 'children' => 'children', 'answers' => 'getAnswers', 'randomAnswers' => 'randomAnswers', 'randomChildren' => 'randomChildren', 'options' => 'getOptions', 'statistics' => 'getStats', 'getXMLContent' => 'getXMLContent', 'input_xml' => 'inputXML' ),
					'increment_key' => 'id',
					'class_name' => 'examElement',
					'sort' => array( 'id' => 'asc' ),
					'name' => 'exam_structure' );
		return $definition;
	}

	static function fetch( $id , $asObject = true, $istplfetch = false )
	{

		$examElement = eZPersistentObject::fetchObject( examElement::definition(),
														null,
														array( 'id' => $id ),
														$asObject );
		if ($istplfetch) return array( 'result' => $examElement );
		else return $examElement;
	}
	function content( $languageCode = "eng-GB" )
	{
		return $this->getContent( $languageCode );
	}
	function getContent( $languageCode = 'eng-GB' )
	{
		return $this->content;
	}
	function getXMLContent()
	{
		$xmlObject = new eZXMLText( $this->content, null );
		$output = $xmlObject->attribute('output')->attribute("output_text");
		if ($output)
			return $output;
		else
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
	function randomChildren()
	{
		$children = $this->getChildren();
		$optionArray = $this->options;
		if($optionArray['random'])
			shuffle($children);
		return $children;
	}
	function getAnswers()
	{
		if ($this->type != "question" ) return;
		$rows = eZPersistentObject::fetchObjectList( examAnswer::definition(),
											null,
											array( 'question_id' => $this->ID),
											array( 'priority' => 'asc' ),
											null,
											true );
		return $rows;
	}
	function randomAnswers()
	{
		$answers = $this->getAnswers();
		$optionArray = $this->options;
		if($optionArray['random'])
			shuffle($answers);
		return $answers;
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
		if( $this->type != "question" ) return false;
		$db = eZDB::instance();
		$db->begin();
		$query = "SELECT COUNT(*) AS total FROM `exam_results` WHERE `question_id` = ".$this->ID;
		$queryResult = $db->arrayQuery( $query, array( 'limit' => 1, 'column' => 'total' ) );
		$result['total'] = $queryResult[0];
		$query = "SELECT COUNT(*) AS first_pass FROM `exam_results` WHERE `question_id` = ".$this->ID." AND `correct` = 1 AND `followup` = 0";
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
		$db->commit();
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
	static function add( $contentobject_id, $priority = 0, $type = "group", $parent = 0, $xmlOptions, $content, $version, $language_code )
	{
		$newElement = new examElement();
		$newElement->setAttribute( 'contentobject_id', $contentobject_id );
		$newElement->setAttribute( 'priority', $priority );
		$newElement->setAttribute( 'type', $type );
		$newElement->setAttribute( 'parent', $parent );
		$newElement->setAttribute( 'xmloptions', $xmlOptions );
		$cleanContent = preg_replace("/&nbsp;/i", "", $content );
		$newElement->setAttribute( 'content', $cleanContent );
		$newElement->setAttribute( 'version', $version );
		$newElement->setAttribute( 'language_code', $language_code );
		$newElement->store();
		return $newElement;
	}
	function removeElement()
	{
		$db = eZDB::instance();
		$db->begin();
		/*check for children if group, answers if question and permission*/
		switch ($this->type) {
			case "question":
				$children = count( $this->getAnswers() );
				if ($children != 0 ) {
					$query = "DELETE FROM `exam_answer` WHERE `question_id` = ".$this->ID;
					$db->query( $query );
				}
				break;
			case "group":
				$children = count( $this->getChildren() );
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
    {
 //At this point the only option is random... but we'll do it like this in the event there is some expansion
/* in the template {$element.options['random']} */
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
					$options[$label] =  $value;
				}
				return $options;
			}
		}
		return false;

	}
	public function updateOption( $updateArray )
	{
	/* This updates the option xml.
	 * <?xml version="1.0" encoding="utf-8"?>
	 * <options><option label="random" value="1"/></options>
	 */
		//get existing
		$existingOptions = $this->getOptions();
		$dom = new DOMDocument( '1.0', 'utf-8' );
		$root = $dom->createElement("options");
		//take care of existing
		if ( $existingOptions )
		{
			foreach ( $existingOptions as $key => $value )
			{
				if ( $key != "" ) { //should never happen outside of test circumstances
					$root = $dom->appendChild($root);
					$node = $dom->createElement("option");
					$newnode = $root->appendChild($node);
					if ( array_key_exists($key, $updateArray) ) {
						$value = $updateArray[$key];
					}
					$newnode->setAttribute( "label", $key  );
					$newnode->setAttribute( "value", $value );
				}
			}
		}
		//load new

		$newAttributeArray = array_diff_key($updateArray,$existingOptions);
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

		$this->setAttribute( 'xmloptions', $xmlString );
		$this->store();
		return $xmlString;
    }
	function xmlData()
	{
		return $this->XMLData;
	}
	function inputXML()
	{
		$xmlObject = new eZXMLText( $this->content, null );
		$input = $xmlObject->attribute('input')->attribute('input_xml');
		//This is the good code - will be used exclusively after everything has been resaved.
		if ($input) {
			return $input;
		} else {
			$dom = new DOMDocument( '1.0', 'utf-8' );
			if (!is_object($xmlObject)) return false;
			$data = $xmlObject->XMLData;
			$cleanData = preg_replace("/&nbsp;/i", "", $data );
			$success = $dom->loadXML( $cleanData );
			$editOutput = new eZSimplifiedXMLEditOutput();
			$dom->formatOutput = true;
			if ( eZDebugSetting::isConditionTrue( 'kernel-datatype-ezxmltext', eZDebug::LEVEL_DEBUG ) )
				eZDebug::writeDebug( $dom->saveXML(), eZDebugSetting::changeLabel( 'kernel-datatype-ezxmltext', __METHOD__ . ' xml string stored in database' ) );
			if (!is_object($editOutput )) return false;
			$output = $editOutput->performOutput( $dom );
			return $output;
		}
	}
}

?>
