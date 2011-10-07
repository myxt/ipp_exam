<?php
/**
 * File containing the ExamenType class.
 *

 */

/*!
  \class ExamenType examentype.php
  \ingroup eZDatatype
  \brief Stores an exam object

*/

class ExamenType extends eZDataType
{
    const DATA_TYPE_STRING = "examen";

    function ExamenType()
    {
        $this->eZDataType( self::DATA_TYPE_STRING, ezpI18n::tr( 'kernel/classes/datatypes', "Exam", 'Datatype name' ),
                           array( 'serialize_supported' => true,
                                  'object_serialize_map' => array( 'data_text' => 'text' ) ) );
    }

    /*!
     Set class attribute value for template version
    */
    function initializeClassAttribute( $classAttribute )
    {
    }
    function initializeObjectAttribute( $objectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
    }
    /*!
     Sets the default value.
    */
    function postInitializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    {
		if ( !$currentVersion ) {
			$exam = new exam;
			$exam->setAttribute( 'contentobject_id', $contentObjectAttribute->attribute( 'contentobject_id' ) );
			$exam->setAttribute( 'version', 1 );
			$exam->setAttribute( 'language_code', $contentObjectAttribute->attribute( "language_code" ) );
			$exam->store();
		} else { //if it's a new version gotta clone it
			//if ( $contentObjectAttribute->attribute( 'version' ) != $currentVersion )  {
				$examElements = exam::getElements($originalContentObjectAttribute->attribute( 'contentobject_id' ),$originalContentObjectAttribute->attribute( 'version' ),$originalContentObjectAttribute->attribute( 'language_code' ));
				foreach($examElements as $elementObject) {
					$newElement = examElement::add( $contentObjectAttribute->attribute( 'contentobject_id' ), $elementObject->attribute( 'priority' ) , $elementObject->attribute( 'type' ), $elementObject->attribute( 'parent' ), $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ) );
					$newElement->setAttribute('content', $elementObject->attribute( 'content' ));
					$newElement->store();

					if ($elementObject->type == 'question' ) {
						foreach( $elementObject->answers as $answer ) {
							examAnswer::add( $contentObjectAttribute->attribute( 'id' ),$answer->attribute( 'question_id' ), $answer->attribute( 'priority' ) , $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ) );
						}
					}
				}
			//}
		}
    }

    function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
        $classAttribute = $contentObjectAttribute->contentClassAttribute();


        if ( $http->hasPostVariable( $base . '_data_text_' . $contentObjectAttribute->attribute( 'id' ) ) )
        {
            $data = $http->postVariable( $base . '_data_text_' . $contentObjectAttribute->attribute( 'id' ) );
		$isValid = eZInputValidator::STATE_ACCEPTED;
		$validation = array();
//		$examID = $contentObjectAttribute->attribute( self::CONTENT_VALUE );
//		$exam = $this->fetch( $examID );

		if ( is_object( $exam ) )
            {
			$params = array( 'prefix_attribute' => self::PREFIX_ATTRIBUTE,
						'contentobjectattribute_id' => $contentObjectAttribute->attribute( 'id' ) );
			$status = $exam->validateEditActions( $validation, $params );
            }
        }

        return eZInputValidator::STATE_ACCEPTED;
    }

    /*!
     Fetches the http post var string input and stores it in the data instance.  On store and save.
    */
    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
/*
Get the list of element ids from the exam and put it in an array
for each element check if there is a corresponding postVariable
if so, update the table. for the appropriate element.  We'll need the element id and the question/anwser id...
*/

		$examElements = exam::getElements($contentObjectAttribute->attribute( 'contentobject_id' ),$contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ));
		$biggest_priority = 0;
		foreach($examElements as $priorityObject) {
			$priorityArray[$priorityObject->ID] = array( $priorityObject->attribute( 'priority'), $priorityObject );
		}
		foreach($examElements as $elementObject) {
			$element_id = $elementObject->ID;

			if ( $http->hasPostVariable( "MoveDown_".$element_id ) ) {

				//$elementObject->setAttribute('priority',$http->postVariable( "element_priority_".$element_id ));
				//$elementObject->store();
				$elementObject->priorityUp;
			}

			if ( $http->hasPostVariable( "MoveUp_".$element_id ) ) {
				//$elementObject->setAttribute('priority',$http->postVariable( "element_priority_".$element_id ));
				//$elementObject->store();
				$elementObject->priorityDown;
			}

			if ( $http->hasPostVariable( "element_priority_".$element_id ) ) {
				$elementObject->setAttribute('priority',$http->postVariable( "element_priority_".$element_id ));
				$elementObject->store();
				if ($http->postVariable( "element_priority_".$element_id ) > $biggest_priority ) {
					$biggest_priority=$http->postVariable( "element_priority_".$element_id );
				}
			}

			if ($elementObject->type == "group" ) {
				if ( $http->hasPostVariable( "exam_group_data_text_".$element_id ) ) {
					$elementObject->setAttribute('content',$http->postVariable( "exam_group_data_text_".$element_id ));
					$elementObject->store();
				}
			}
			if ( $http->hasPostVariable( "exam_data_text_".$element_id ) ) {
				$elementObject->setAttribute('content',$http->postVariable( "exam_data_text_".$element_id ));
				$elementObject->store();
			}
			if ($elementObject->type == "question" ) {
				$answer_priority_array[$element_id] = 0;
				foreach($elementObject->answers as $answerObject) {
					if ( $http->hasPostVariable( "answer_correct_".$answer_id ) ) {
						if ( $http->variable( "answer_correct_".$answer_id ) == "on" ) {
							$answerObject->setAttribute('correct', 1 );
						} else {
							$answerObject->setAttribute('correct', 0 );
						}
						$answerObject->store();
					}
					if ( $http->hasPostVariable( "MoveDown_answer_".$answer_id ) ) {
						$answerObject->priorityUp;
					}

					if ( $http->hasPostVariable( "MoveUp_answer_".$answer_id ) ) {
						$answerObject->priorityDown;
					}
							if ( $http->hasPostVariable( "answer_priority_".$answer_id ) ) {
						$answerObject->setAttribute('priority',$http->postVariable( "answer_priority_".$answer_id ));
						$answerObject->store();
					}
					if ( $http->hasPostVariable( "answer_data_text_".$answer_id ) ) {
						$answerObject->setAttribute('content',$http->postVariable( "answer_data_text_".$answer_id ));
						$answerObject->store();
					}
					if ( $http->hasPostVariable( "answer_condition_".$answer_id ) ) {
						$answerObject->setAttribute('option_id',$http->postVariable( "answer_condition_".$answer_id ));
						$answerObject->store();
					}					
					if ( $http->hasPostVariable( "answer_value_".$answer_id ) ) {
						$answerObject->setAttribute('option_value',$http->postVariable( "answer_value_".$answer_id ));
						$answerObject->store();
					}
					if ($answerObject->attribute( 'priority' ) > $answer_priority_array[$element_id])
						$answer_priority_array[$element_id]=$answerObject->attribute( 'priority' );
				}
			}
		}
		/* Custom actions */
		if ($http->hasPostVariable( "CustomActionButton" ) ){
			/*Have to find the greatest priority to add this to the end*/
			$priority = $biggest_priority + 1;
			$customAction = $http->postVariable( "CustomActionButton" );
			if ( $customAction["newGroup"] ) { //Always a parent, never a child
				examElement::add( $contentObjectAttribute->attribute( 'contentobject_id' ), $priority , "group", 0, $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ) );
			}
			if ( $customAction["newQuestion"] ) {
				$parent = array_keys($customAction['newQuestion']);
				$parent = $parent[0] ? $parent[0] : 0;
				examElement::add( $contentObjectAttribute->attribute( 'contentobject_id' ), $priority , "question", $parent, $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ) );
			}
			if ( $customAction["newAnswer"] ) {
				$question = array_keys($customAction['newAnswer']);
				$question_id = $question[0] ? $question[0] : 0;
				$answer_priority = $answer_priority_array[$question_id] + 1;
				examAnswer::add( $contentObjectAttribute->attribute( 'contentobject_id' ), $question_id, $answer_priority , $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ) );
			}
			if ( $customAction["removeAnswer"] ) {
				$element_id = array_keys($customAction['removeAnswer']);
				examAnswer::removeAnswerByID( $element_id[0] );
			}
			if ( $customAction["newText"] ) {
				$parent = array_keys($customAction['newText']);
				$parent = $parent[0] ? $parent[0] : 0;
				examElement::add( $contentObjectAttribute->attribute( 'contentobject_id' ), $priority , "text", $parent, $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ) );
			}
			if ( $customAction["newBreak"] ) {
				$parent = array_keys($customAction['newBreak']);
				$parent = $parent[0] ? $parent[0] : 0;
				examElement::add( $contentObjectAttribute->attribute( 'contentobject_id' ), $priority , "pagebreak", $parent, $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ) );
			}
			if ( $customAction["remove"] ) {
				$element_id = array_keys($customAction['remove']);
				examElement::removeElementByID( $element_id[0] );
			}
		}

/* We should do this only if we convert the structure to xml to get the diff instead of getting the diff from the elements
        if ( $http->hasPostVariable( $base . "_data_text_" . $contentObjectAttribute->attribute( "id" ) ) )
        {
            $data = $http->postVariable( $base . "_data_text_" . $contentObjectAttribute->attribute( "id" ) );
            $contentObjectAttribute->setAttribute( "data_text", $data );
            return true;
        }
        return false;
*/
    }

    /*!
     Store the content.
    */
    function storeObjectAttribute( $attribute )
    {
    }

    /*!
     Simple string insertion is supported.
    */
    function isSimpleStringInsertionSupported()
    {
        return true;
    }

    /*!
     Inserts the string \a $string in the \c 'data_text' database field.
    */
    function insertSimpleString( $object, $objectVersion, $objectLanguage,
                                 $objectAttribute, $string,
                                 &$result )
    {
        $result = array( 'errors' => array(),
                         'require_storage' => true );
        $objectAttribute->setContent( $string );
        $objectAttribute->setAttribute( 'data_text', $string );
        return true;
    }

    /*!
     Returns the content.
    */
    function objectAttributeContent( $contentObjectAttribute )
    {
        return exam::fetch($contentObjectAttribute->attribute( 'contentobject_id' ));
    }

    function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
    }

    /*!
     Returns the meta data used for storing search indeces.
    */
    function metaData( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( "data_text" );
    }

    /*!
     \return string representation of an contentobjectattribute data for simplified export

    */
    function toString( $contentObjectAttribute )
    {
        return $contentObjectAttribute->attribute( 'data_text' );
    }

    function fromString( $contentObjectAttribute, $string )
    {
        return $contentObjectAttribute->setAttribute( 'data_text', $string );
    }

    function hasObjectAttributeContent( $contentObjectAttribute )
    {
        return trim( $contentObjectAttribute->attribute( 'data_text' ) ) != '';
    }

    /*!
     Returns the text.
    */
    function title( $data_instance, $name = null )
    {
    }

    function isIndexable()
    {
        return true;
    }

    function isInformationCollector()
    {
        return true;
    }

    function serializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
/*
        $defaultValue = $classAttribute->attribute( 'data_text1' );
        $dom = $attributeParametersNode->ownerDocument;
        $defaultValueNode = $dom->createElement( 'default-value' );
        $defaultValueNode->appendChild( $dom->createTextNode( $defaultValue ) );
        $attributeParametersNode->appendChild( $defaultValueNode );
*/
    }

    function unserializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
/*
        $textColumns = $attributeParametersNode->getElementsByTagName( 'text-column-count' )->item( 0 )->textContent;
        $classAttribute->setAttribute( self::COLS_FIELD, $textColumns );
*/
    }

    function diff( $old, $new, $options = false )
    {
        $diff = new eZDiff();
        $diff->setDiffEngineType( $diff->engineType( 'xml' ) );
        $diff->initDiffEngine();
        $diffObject = $diff->diff( $old, $new );
        return $diffObject;
    }

    function supportsBatchInitializeObjectAttribute()
    {
        return true;
    }
    function deleteStoredObjectAttribute( $objectAttribute, $version = null )
    { 
        $exam = exam::fetch( $objectAttribute->attribute( 'contentobject_id' ) );
        if ( is_object( $exam ) )
        {
            $exam->removeExam();
        }
    }
}

eZDataType::register( ExamenType::DATA_TYPE_STRING, "ExamenType" );

?>
