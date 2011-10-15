<?php
//
//

/*! \file examelement.php
*/

class examResult extends eZPersistentObject
{
    function examResult( $row = array() )
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
										'default' => '0',
										'required' => true ),
						'hash' => array( 'name' => 'hash',
										'datatype' => 'string',
										'default' => '',
										'required' => true ),
						'question_id' => array( 'name' => 'questionID',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'answer' => array( 'name' => 'answer',
										'datatype' => 'string',
										'default' => '',
										'required' => true ),
						'correct' => array( 'name' => 'correct',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'followup' => array( 'name' => 'followup',
										'datatype' => 'integer',
										'default' => '0',
										'required' => true ),
						'conditional' => array( 'name' => 'conditional',
										'datatype' => 'integer',
										'default' => null,
										'required' => false )
					),
					'keys' => array( 'id' ),
					'function_attributes' => array( 'content' => 'content', 'template_name' => 'templateName', ),
					'increment_key' => 'id',
					'class_name' => 'examResult',
					'sort' => array( 'id' => 'asc' ),
					'name' => 'exam_results' );
		return $definition;
	}
	static function fetch( $id , $asObject = true )
	{

		$examResult = eZPersistentObject::fetchObject( examResult::definition(),
														null,
														array( 'question_id' => $id ),
														$asObject );
		return $examResult;
	}
	static function fetchByHash( $hash, $exam_id, $asObject = true )
	{
		$examResult = eZPersistentObject::fetchObjectList( examResult::definition(),
														null,
														array( 'hash' => $hash , 'contentobject_id' => $exam_id ),
														array( 'followup' => 'asc' ),
														$asObject
											);
		return $examResult;
	}
	function &templateName()
	{
		$element = examElement::fetch( $this->questionID );
		return $element->type;
	}
}

?>
