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
		$type = $row['type'];
		$parent = $row['parent'];
		$this->options = $this->getOptions();
		$this->content = $this->getContent();
		$this->children = $this->getChildren();
		$this->answers = $this->getAnswers();
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
						'options' => array( 'name' => 'xmlOptions',
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
					'function_attributes' => array(  'template_name' => 'templateName', 'content' => 'content', 'options' => 'options', 'children' => 'children', 'answers' => 'getAnswers' ),
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
		if ($this->type == "question" )
		{
		$rows = eZPersistentObject::fetchObjectList( examQuestion::definition(),
											null,
											array( 'question_id' => $this->ID),
											array( 'priority' => 'asc' ),
											null,
											true );
		return $rows;
		} else 
			return false;
	}

	static function getElements( $id, $version = 1, $languageCode = 'eng-GB' )
	{
		$rows = eZPersistentObject::fetchObjectList( examElement::definition(),
										null,
										array( 'contentobject_id' => $id,
												'version' => $version,
												'language_code' => $languageCode ),
										array( 'priority' => 'asc' ),
										null,
										true );
		return $rows;
	}

    function getOptions()
    {
        if ( $this->xmlOptions != '' )
        {
            $dom = new DOMDocument( '1.0', 'utf-8' );
            $dom->loadXML( $this->xmlOptions );
            $optionArray = $dom->getElementsByTagName( "option" );
            if ( $optionArray )
            {
                foreach ( $optionArray as $option )
                {
                    $label = $option->getElementsByTagName( "label" )->item( 0 )->textContent;
                    $value = $option->getElementsByTagName( "value" )->item( 0 )->textContent;
				$options[$label] =  $value;
                }
			 return $options;
            }
        }
	}
    function &templateName()
    {
		$type = $this->type;
		return $type;
	}
	function add( $contentobject_id, $priority = 0, $type = "group", $parent = 0, $version, $language_code )
	{
		$newElement = new examElement();
		$newElement->setAttribute( 'contentobject_id', $contentobject_id );
		$newElement->setAttribute( 'priority', $priority );
		$newElement->setAttribute( 'type', $type );
		$newElement->setAttribute( 'parent', $parent );
		$newElement->setAttribute( 'version', $version );
		$newElement->setAttribute( 'language_code', $language_code );
		$newElement->store();
	}
	function remove( $id )
	{
		
		$element = examElement::fetch( $id );
		$db = eZDB::instance();
		$db->begin();
		/*check for children if group, answers if question and permission*/
		switch ($element->type) {
			case "question":
				$children = count( $element->getAnswers() );
				if ($children != 0 ) {
					$query = "DELETE FROM `exam_question` WHERE `question_id` = ".$id;
					$db->query( $query );
				}
				break;
			case "group":
				$children = count( $element->getChildren() );
				if ($children != 0 ) {
					$query = "DELETE FROM `exam_structure` WHERE `parent` = ".$id;
					$db->query( $query );
				}
				break;
			default: // "text"|"pagebreak"
		}
		$query = "DELETE FROM `exam_structure` WHERE `id` = ".$id;
		$db->query( $query );
		$db->commit();
	}
}

?>
