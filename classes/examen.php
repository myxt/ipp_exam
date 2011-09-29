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
		$this->options = $this->getOptions();
		$this->structure = $this->getStructure();
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
						'xml_options' => array( 'name' => 'xmlOptions',
										'datatype' => 'string',
										'default' => '',
										'required' => true ),
						'enabled' => array( 'name' => 'enabled',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'high_score' => array( 'name' => 'version',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true )
					),
					'keys' => array( 'id' ),
					'function_attributes' => array(  'structure' => 'structure', 'options' => 'options' ),
					'increment_key' => 'id',
					'class_name' => 'exam',
					'sort' => array( 'id' => 'asc' ),
					'name' => 'exam_statistics' );
		return $definition;
	}

	static function fetch( $id, $asObject = true )
	{
		$examObject = eZPersistentObject::fetchObject(
										exam::definition(),
										null,
										array( 'contentobject_id' => $id ),
										$asObject );
		return $examObject;
	}

	function fetchExam( $id )
	{

		$exam = $this->fetch( $id );
		if ( !$exam OR !$exam->enabled )
			$exam = false;
		return array( 'result' => $exam );
	}

	public function structure()
	{
		$contentObjectID = $this->contentObjectID;
		return $this->getStructure();
	}
	function getStructure( $languageCode = 'eng-GB' )
	{ //Only top level items.
		$rows = eZPersistentObject::fetchObjectList( examElement::definition(),
								null,
								array( 'contentobject_id' => $this->contentObjectID,
										'parent' => 0,
										'language_code' => $languageCode ),
								array( 'priority' => 'asc' ),
								null,
								true );
		return $rows;
	}

	public function options()
	{
		return $this->getOptions();
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

    public function toXML()
    {
        $dom = new DOMDocument( '1.0', 'utf-8' );
        $dom->formatOutput = true;
        $success = $dom->loadXML('<exam />');

        $pageNode = $dom->documentElement;

        foreach ( $this->attributes as $attrName => $attrValue )
        {
            switch ( $attrName )
            {

                default:
                    $node = $dom->createElement( $attrName );
                    $nodeValue = $dom->createTextNode( $attrValue );
                    $node->appendChild( $nodeValue );
                    $pageNode->appendChild( $node );
                    break;
            }
        }

        return $dom->saveXML();
    }


    public function attributes()
    {
        return array_keys( $this->attributes );
    }



	public function __clone()
	{ //used by copy

	}

	function validateEditActions( $validation, $params )
	{ //called by validateObjectAttributeHTTPInput
	}
}

?>