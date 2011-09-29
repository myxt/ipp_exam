<?php
//
//

/*! \file examquestion.php
*/

class examQuestion extends eZPersistentObject
{
    function examQuestion( $row = array() )
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
						'question_id' => array( 'name' => 'questionID',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'priority' => array( 'name' => 'priority',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'type' => array( 'name' => 'type',
										'datatype' => 'enum',
										'default' => '',
										'required' => true ),
						'options' => array( 'name' => 'xmlOptions',
										'datatype' => 'string',
										'default' => '',
										'required' => false ),
						'correct' => array( 'name' => 'correct',
										'datatype' => 'integer',
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
					'function_attributes' => array( 'options' => 'options' ),
					'increment_key' => 'id',
					'class_name' => 'examQuestion',
					'sort' => array( 'id' => 'asc' ),
					'name' => 'exam_question' );
		return $definition;
	}

    static function fetch( $id , $asObject = true )
    {

        $examQuestion = eZPersistentObject::fetchObject( examQuestion::definition(),
                                                                 null,
                                                                 array( 'id' => $id ),
                                                                 $asObject );
        return $examQuestion;
    }
	function content( $languageCode = "eng-GB" )
	{
		return $this->getContent( $languageCode );
	}
	function getContent($languageCode = "eng-GB" )
	{
		$rows = eZPersistentObject::fetchObjectList( examQuestion::definition(),
											null,
											array( 'id' => $this->ID),
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


	function add( $question_id, $priority = 0, $type = "answer", $version, $language_code = "eng-GB" )
	{
		$newAnswer = new examQuestion();
		$newAnswer->setAttribute( 'question_id', $question_id );
		$newAnswer->setAttribute( 'priority', $priority );
		$newAnswer->setAttribute( 'type', $type );
		$newAnswer->setAttribute( 'version', $version );
		//$newAnwser->setAttribute( 'language_code', $language_code );
		$newAnswer->store();
	}
	function remove( $id )
	{
		$db = eZDB::instance();
		$db->begin();
		$query = "DELETE FROM `exam_question` WHERE `id` = ".$id;
		$db->query( $query );
		$db->commit();
	}

}

?>
