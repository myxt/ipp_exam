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
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
/* This will be needed if we set the xmltext of the content object for diffing
        if ( $currentVersion != false )
        {
            $xmlText = eZXMLTextType::rawXMLText( $originalContentObjectAttribute );
            $contentObjectAttribute->setAttribute( "data_text", $xmlText );
        }
        else
        {
            $parser = new eZXMLInputParser();
            $doc = $parser->createRootNode();
            $xmlText = eZXMLTextType::domString( $doc );
            $contentObjectAttribute->setAttribute( "data_text", $xmlText );
        }
*/
    }
    /*!
     Sets the default value.
    */
    function postInitializeObjectAttribute( $contentObjectAttribute, $currentVersion, $originalContentObjectAttribute )
    { //This creates a new version or copies a version.
//eZFire::debug(__FUNCTION__,"WE ARE HERE");

//eZFire::debug($contentObjectAttribute->attribute( 'version' ),"object attribute version");
//eZFire::debug($currentVersion,"CURRENT VERSION");
//eZFire::debug($originalContentObjectAttribute->attribute( 'version' ),"OriginalContentObjectAttribute version");

		if ( !$currentVersion ) {
			$exam = new exam;
			$exam->setAttribute( 'contentobject_id', $contentObjectAttribute->attribute( 'contentobject_id' ) );
			$exam->setAttribute( 'version', 1 );
			$exam->setAttribute( 'language_code', $contentObjectAttribute->attribute( "language_code" ) );
			$exam->store();
		} else { //if it's a new version gotta clone it
			if ( $contentObjectAttribute->attribute( 'version' ) != $currentVersion )  {

				$examElements = exam::getstructure($originalContentObjectAttribute->attribute( 'contentobject_id' ),$originalContentObjectAttribute->attribute( 'version' ),$originalContentObjectAttribute->attribute( 'language_code' ));
				$elementIdMap = array();
				$answerArray = array();
				$newElementArray = array();
				foreach($examElements as $elementObject) {
					$newElement = examElement::add(	$contentObjectAttribute->attribute( 'contentobject_id' ),
												$elementObject->attribute( 'priority' ) ,
												$elementObject->attribute( 'type' ),
												$elementObject->attribute( 'parent' ),
												$elementObject->attribute( 'xmloptions' ),
												$elementObject->attribute( 'content' ),
												$contentObjectAttribute->attribute( 'version' ),
												$contentObjectAttribute->attribute( 'language_code' ) );
					$elementIdMap[$elementObject->ID] = $newElement->ID;
					$newElementArray[] = $newElement;
					if ($elementObject->attribute( 'type' ) == 'question' ) {

						foreach( $elementObject->getAnswers() as $answer ) {

						$answerArray[] = examAnswer::add(	$contentObjectAttribute->attribute( 'contentobject_id' ),
											$newElement->attribute( 'id' ),//question_id
											$answer->attribute( 'priority' ),
											$answer->attribute( 'option_id' ),
											$answer->attribute( 'option_value' ),
											$answer->attribute( 'correct' ),
											$answer->attribute( 'content' ),
											$contentObjectAttribute->attribute( 'version' ),
											$contentObjectAttribute->attribute( 'language_code' ) );
						}
					}

					if ($elementObject->attribute( 'type' ) == 'group' ) {
						foreach( $elementObject->children as $child) {
							$newElement = examElement::add(	$contentObjectAttribute->attribute( 'contentobject_id' ),
														$child->attribute( 'priority' ) ,
														$child->attribute( 'type' ),
														$elementObject->ID,
														$child->attribute( 'xmloptions' ),
														$child->attribute( 'content' ),
														$contentObjectAttribute->attribute( 'version' ),
														$contentObjectAttribute->attribute( 'language_code' ) );
							$elementIdMap[$child->ID] = $newElement->ID;
							$newElementArray[] = $newElement;
							if ($child->attribute( 'type' ) == 'question' ) {
//How do we know what the new option value or parent is going to be - craaaaap.
								foreach( $child->getAnswers() as $answer ) {
									$answerArray[] = examAnswer::add(	$contentObjectAttribute->attribute( 'contentobject_id' ),
													$newElement->attribute( 'id' ),
													$answer->attribute( 'priority' ),
													$answer->attribute( 'option_id' ),
													$answer->attribute( 'option_value' ),
													$answer->attribute( 'correct' ),
													$answer->attribute( 'content' ),
													$contentObjectAttribute->attribute( 'version' ),
													$contentObjectAttribute->attribute( 'language_code' ) );
								}
							}
						}
					} // if group
				} //foreach structure element
//eZFire::debug($answerArray,"ANSWER ARRAY");
//eZFire::debug($newElementArray,"ELEMENT ARRAY");
//eZFire::debug($elementIdMap,"elementIdMap");
				//we've got to fix the element parent and the option value.
				foreach($answerArray as $checkAnswer) {
					if( $checkAnswer->option_value != 0 ){
						$checkAnswer->setAttribute( 'option_value', $elementIdMap[$checkAnswer->option_value] );
						$checkAnswer->store();
					}
				}
				foreach($newElementArray as $checkElement) {
					$parentID = $checkElement->attribute( 'parent' );
//eZFire::debug($parentID,"parentID");
					if( $parentID != 0 ){
						$checkElement->setAttribute( 'parent', $elementIdMap[$parentID] );
						$checkElement->store();
					}
				}
			} //if $version != currentVersion
		}
    }

	function validateObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
	{
//This is hit BEFORE stuff is saved... which means you can't check using stuff that's in the database.  So... basically have to dump the database values into arrays and check against http values.  Also, only the last message is passed - there is no way to get multiple errors, so, have to either rewrite the edit template or bail out on every failure instead of getting them all at once.
//Also, want to only do this on publish... not store, otherwise it'll end up being impossible to edit.  It would be nice to get an error message on store, but, once again that would mean rewriting the edit template. 
//eZFire::debug(__FUNCTION__,"ARE WE HERE?");
		if ( $http->hasPostVariable( "PublishButton" ) )
		{
			$failStatus = eZInputValidator::STATE_INVALID;
		} else {
			$failStatus = eZInputValidator::STATE_INTERMEDIATE;
		}

		$examObject = eZContentObject::fetch($contentObjectAttribute->attribute( 'contentobject_id' ));
		$version = $examObject->version( $contentObjectAttribute->attribute( 'version' ));
		$dataMap = $version->DataMap();
		$passThreshold = (int) $dataMap['pass_threshold']->DataInt;

		$examElements = exam::getElements($contentObjectAttribute->attribute( 'contentobject_id' ),$contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ));

		$validation = array();
		//Gotta get the version and get the value of passthreshold here.

		/*There should be at least one question left after all conditions have been taken into account.*/
		$questionCount=0;
		$questionCondition=0;
		foreach($examElements as $elementObject){
			if ($elementObject->attribute( 'type' ) == "question" ) {
				$questionCount++;
				$correct=0;
				foreach($elementObject->getAnswers() as $answerCount => $answerObject) {
					$answer_id=$answerObject->ID;
					if ( $http->hasPostVariable( "answer_correct_".$answer_id ) ) {
						if ( $http->variable( "answer_correct_".$answer_id ) == "on" ) {
							$correct++;
						}
					} elseif($answerObject->correct == 1) {
						$correct++;
					}
					$answerOption = $http->hasPostVariable( "answer_condition_".$answer_id ) ?  $http->postVariable( "answer_condition_".$answer_id ) :  $answerObject->option_id;
					$answerValue = $http->hasPostVariable( "answer_value_".$answer_id ) ? $http->postVariable( "answer_value_".$answer_id ) : $answerObject->option_value;

					/*An option id with no option value or vice-versa should be flagged.*/
					if ( $answerOption AND !$answerValue OR $answerValue AND !$answerOption ) {
						$validation['error'] = true;
						$validation['custom_rules'][] = array( 'message' => ezpI18n::tr( 'design/examen', 'Every question with a condition must have a condition value and vice-versa.  Question %1 Answer %2 does not.', null, array($elementObject->ID, $answerObject->ID ) ) );
//eZFire::debug($validation,"VALIDATION");
						$contentObjectAttribute->setValidationError( ezpI18n::tr( 'design/exam', $error['message'] ) );
						$contentObjectAttribute->setHasValidationError();
						return $failStatus;
					}
					/*If a question element is a condition it has to be from the same group.*/
					if ( $answerOption != 0 ) {
						if( $elementObject->parent != 0 ) {
							if ( $answerValue != 0 ) {
								$checkObject = examElement::fetch( $answerValue );
								if ( $checkObject->attribute( 'type' ) == "question" ) {
									$questionCondition++;
									if ( $elementObject->parent != $checkObject->parent ) {
										$validation['error'] = true;
										$validation['custom_rules'][] = array( 'message' => ezpI18n::tr( 'design/examen', 'A condition element can only come from the same group.  Question %1 Answer %2 does not meet this criteria.', null, array($elementObject->ID, $answerObject->ID) ) );
//eZFire::debug($validation,"VALIDATION");

										$contentObjectAttribute->setValidationError( ezpI18n::tr( 'design/exam', $error['message'] ) );
										$contentObjectAttribute->setHasValidationError();
										return $failStatus;
									}
								}
							}
						}
					}

				}  //end foreach answer

				//If not a survey every question must have one correct answer
				if ( $passThreshold != 0 AND $correct != 1 ) {
					$validation['error'] = true;
					$validation['custom_rules'][] = array( 'message' => ezpI18n::tr( 'design/examen', 'If there is a pass threshhold, every question must have one correct answer.  Question %1 does not have one correct answer.', null, array($elementObject->ID) ) );
//eZFire::debug($validation,"VALIDATION");
					$contentObjectAttribute->setValidationError( ezpI18n::tr( 'design/exam', $error['message'] ) );
					$contentObjectAttribute->setHasValidationError();
					return $failStatus;
				}
				//Every question must have at least two answers
//eZFire::debug($answerCount,"ANSEWR COUNT");
				if ( $answerCount < 1 ) { //Count starts at [0] [1]
					$validation['error'] = true;
					$validation['custom_rules'][] = array( 'message' => ezpI18n::tr( 'design/examen', 'Every question must have at least two answers.  Question %1 does not.', null, array($elementObject->ID) ) );
//eZFire::debug($validation,"VALIDATION");
					$contentObjectAttribute->setValidationError( ezpI18n::tr( 'design/exam', $error['message'] ) );
					$contentObjectAttribute->setHasValidationError();
					return $failStatus;
				}

			}//end if question
		} //end foreach elemennt

		if ( $questionCount - $questionConditon < 1 ) {
			$validation['error'] = true;
			$validation['custom_rules'][] = array( 'message' => ezpI18n::tr( 'design/examen', 'After taking into account conditions there are no questions left.' ) );
//eZFire::debug($validation,"VALIDATION");
			$contentObjectAttribute->setValidationError( ezpI18n::tr( 'design/exam', $error['message'] ) );
			$contentObjectAttribute->setHasValidationError();
			return $failStatus;
		}
		//We passed!
//eZFire::debug("INPUT VALIDATED");
		return eZInputValidator::STATE_ACCEPTED;
	}

    /*!
     Fetches the http post var string input and stores it in the data instance.  On store and save.
    */

    function fetchObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
    {
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
//eZFire::debug($contentObjectAttribute->ID,__FUNCTION__);
//eZFire::debug($http,"http");
//eZFire::debug($base,"base");
//eZFire::debug($contentObjectAttribute,"contentObjectAttribute");
//eZFire::debug($contentObjectAttribute->attribute( "contentobject_id" ) ,"contentObjectAttribute id");
//eZFire::debug($contentObjectAttribute->attribute( "version" ) ,"version");
//eZFire::debug($contentObjectAttribute->attribute( "language_code" ) ,"language");
//eZFire::debug($_POST,"POST");
/*
answer_priority_
condition
element_priority_
exam_answer_data_text_
exam_group_data_text_
exam_question_data_text_
exam_text_data_text_
*/
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
//eZFire::debug($priorityArray,"PriorityArray");
		foreach($examElements as $elementObject) {
			$element_id = $elementObject->ID;

			if ( $http->hasPostVariable( "element_priority_".$element_id ) ) {
				$elementObject->setAttribute('priority',$http->postVariable( "element_priority_".$element_id ));
				if ($http->postVariable( "element_priority_".$element_id ) > $biggest_priority ) {
					$biggest_priority=$http->postVariable( "element_priority_".$element_id );
				}
			}

			if ($elementObject->attribute( 'type' ) == "group" ) {
				if ( $http->hasPostVariable( "exam_group_data_text_".$element_id ) ) {
					$xmlData = $this->eZXMLTextConvert( $http->postVariable( "exam_group_data_text_".$element_id ) );
					$elementObject->setAttribute('content',$xmlData );
				}
				if ( $http->hasPostVariable( "random_".$element_id ) ) {
					if ( $http->variable( "random_".$element_id ) == "on" ) {
						$elementObject->updateOption( array( "random" => "1" ) );
					} else {
						$elementObject->updateOption( array( "random" => "0" ) );
					}
				}
			}
			if ( $http->hasPostVariable( "exam_data_text_".$element_id ) ) {
				$xmlData = $this->eZXMLTextConvert( $http->postVariable( "exam_data_text_".$element_id ) );
				$elementObject->setAttribute('content',$xmlData );
			}
			if ($elementObject->attribute( 'type' ) == "question" ) {
				$answer_priority_array[$element_id] = 0;
				if ( $http->hasPostVariable( "random_".$element_id ) ) {
					if ( $http->variable( "random_".$element_id ) == "on" ) {
						$elementObject->updateOption( array( "random" => "1" ) );
					} else {
						$elementObject->updateOption( array( "random" => "0" ) );
					}
				}
				foreach($elementObject->getAnswers() as $answerObject) {
					$answer_id = $answerObject->ID;
					if ( $http->hasPostVariable( "answer_correct_".$answer_id ) ) {
						if ( $http->variable( "answer_correct_".$answer_id ) == "on" ) {
							$answerObject->setAttribute('correct', 1);
						} else {
							$answerObject->setAttribute('correct', 0);
						}
					}
					if ( $http->hasPostVariable( "answer_priority_".$answer_id ) ) {
						$answerObject->setAttribute('priority',$http->postVariable( "answer_priority_".$answer_id ));
					}
					if ( $http->hasPostVariable( "answer_data_text_".$answer_id ) ) {
						//answer is a textarea not xmltext
						$answerObject->setAttribute('content', $http->postVariable( "answer_data_text_".$answer_id ));
					}
					if ( $http->hasPostVariable( "answer_condition_".$answer_id ) ) {
						$answerObject->setAttribute('option_id',$http->postVariable( "answer_condition_".$answer_id ));
					}					
					if ( $http->hasPostVariable( "answer_value_".$answer_id ) ) {
						$answerObject->setAttribute('option_value',$http->postVariable( "answer_value_".$answer_id ));
					}
					if ($answerObject->attribute( 'priority' ) > $answer_priority_array[$element_id]) {
						$answer_priority_array[$element_id]=$answerObject->attribute( 'priority' );
					}
					$answerObject->store();
				}
			}
			$elementObject->store();
		}
		/* Custom actions */
		if ($http->hasPostVariable( "CustomActionButton" ) ){
			/*Have to find the greatest priority to add this to the end*/
			$priority = $biggest_priority + 1;
			$customAction = $http->postVariable( "CustomActionButton" );
			if ( $customAction["newGroup"] ) { //Always a parent, never a child
				examElement::add( $contentObjectAttribute->attribute( 'contentobject_id' ), $priority , "group", 0, "",$this->eZXMLTextConvert(), $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ) );
			}
			if ( $customAction["newQuestion"] ) {
				$parent = array_keys($customAction['newQuestion']);
				$parent = $parent[0] ? $parent[0] : 0;
				examElement::add( $contentObjectAttribute->attribute( 'contentobject_id' ), $priority , "question", $parent,"", $this->eZXMLTextConvert(), $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ) );
			}
			if ( $customAction["newAnswer"] ) {
				$question = array_keys($customAction['newAnswer']);
				$question_id = $question[0] ? $question[0] : 0;
				$answer_priority = $answer_priority_array[$question_id] + 1;
				examAnswer::add( $contentObjectAttribute->attribute( 'contentobject_id' ), $question_id, $answer_priority ,"","","","", $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ) );
			}
			if ( $customAction["removeAnswer"] ) {
				$element_id = array_keys($customAction['removeAnswer']);
				examAnswer::removeAnswerByID( $element_id[0] );
			}
			if ( $customAction["newText"] ) {
				$parent = array_keys($customAction['newText']);
				$parent = $parent[0] ? $parent[0] : 0;
				examElement::add( $contentObjectAttribute->attribute( 'contentobject_id' ), $priority , "text", $parent, "", $this->eZXMLTextConvert(), $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ) );
			}
			if ( $customAction["newBreak"] ) {
				$parent = array_keys($customAction['newBreak']);
				$parent = $parent[0] ? $parent[0] : 0;
				examElement::add( $contentObjectAttribute->attribute( 'contentobject_id' ), $priority , "pagebreak", $parent,"", "", $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ) );
			}
			if ( $customAction["remove"] ) {
				$element_id = array_keys($customAction['remove']);
				examElement::removeElementByID( $element_id[0] );
			}
		}
		/*Priority Move CustomActions*/
		if ($http->hasPostVariable( "MoveUp" ) ){
			$idArray = exam::getIDs($contentObjectAttribute->attribute( 'contentobject_id' ), $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ));
			$key = array_search($http->variable( "MoveUp" ),$idArray);
			$newkey = $key - 1;
			if($newkey >= 0) {
				$topElement = examElement::fetch( $idArray[$newkey] );
				if (is_object( $topElement )) {
					$topPriority = $topElement->attribute( 'priority' );
					$bottomElement = examElement::fetch( $idArray[$key] );
					if (is_object( $bottomElement )) {
						$bottomPriority = $bottomElement->attribute( 'priority' );
						if ($topPriority != $bottomPriority) {
							$topElement->setAttribute( 'priority', $bottomPriority );
							$topElement->store();
							$bottomElement->setAttribute( 'priority', $topPriority );
							$bottomElement->store();
						}
					}
				}
			}
		}
		if ($http->hasPostVariable( "MoveDown" ) ){
			$idArray = exam::getIDs($contentObjectAttribute->attribute( 'contentobject_id' ), $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ));
			$key = array_search($http->variable( "MoveDown" ),$idArray);
			$newkey = $key + 1;
			if($newkey < count($idArray)) {
				$topElement = examElement::fetch( $idArray[$key] );
				if (is_object( $topElement )) {
					$topPriority = $topElement->attribute( 'priority' );
					$bottomElement = examElement::fetch( $idArray[$newkey] );
					if (is_object( $bottomElement )) {
						$bottomPriority = $bottomElement->attribute( 'priority' );
						if ($topPriority != $bottomPriority) {
							$topElement->setAttribute( 'priority', $bottomPriority );
							$topElement->store();
							$bottomElement->setAttribute( 'priority', $topPriority );
							$bottomElement->store();
						}
					}
				}
			}
		}
		if ($http->hasPostVariable( "AnswerMoveUp" ) ){
			$idArray = examElement::getAnswerIDs($contentObjectAttribute->attribute( 'contentobject_id' ), $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ));
			$key = array_search($http->variable( "AnswerMoveUp" ),$idArray);
			$newkey = $key - 1;
			if($newkey >= 0) {
				$topAnswer = examAnswer::fetch( $idArray[$newkey] );
				if (is_object( $topAnswer )) {
					$topPriority = $topAnswer->attribute( 'priority' );
					$bottomAnswer = examAnswer::fetch( $idArray[$key] );
					if (is_object( $bottomAnswer )) {
						$bottomPriority = $bottomAnswer->attribute( 'priority' );
						if ($topPriority != $bottomPriority) {
							$topAnswer->setAttribute( 'priority', $bottomPriority );
							$topAnswer->store();
							$bottomAnswer->setAttribute( 'priority', $topPriority );
							$bottomAnswer->store();
						}
					}
				}
			}

		}
		if ($http->hasPostVariable( "AnswerMoveDown" ) ){
			$idArray = examElement::getAnswerIDs($contentObjectAttribute->attribute( 'contentobject_id' ), $contentObjectAttribute->attribute( 'version' ),$contentObjectAttribute->attribute( 'language_code' ));
			$key = array_search($http->variable( "AnswerMoveDown" ),$idArray);
			$newkey = $key + 1;
			if($newkey < count($idArray)) {
				$topAnswer = examAnswer::fetch( $idArray[$key] );
				if (is_object( $topAnswer )) {
					$topPriority = $topAnswer->attribute( 'priority' );
					$bottomAnswer = examAnswer::fetch( $idArray[$newkey] );
					if (is_object( $bottomAnswer )) {
						$bottomPriority = $bottomAnswer->attribute( 'priority' );
						if ($topPriority != $bottomPriority) {
							$topAnswer->setAttribute( 'priority', $bottomPriority );
							$topAnswer->store();
							$bottomAnswer->setAttribute( 'priority', $topPriority );
							$bottomAnswer->store();
						}
					}
				}
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
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
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
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
        return exam::fetch($contentObjectAttribute->attribute( 'contentobject_id' ));
    }

    function fetchClassAttributeHTTPInput( $http, $base, $classAttribute )
    {
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
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
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
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
        return false;
    }

    function serializeContentClassAttribute( $classAttribute, $attributeNode, $attributeParametersNode )
    {
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
        $defaultValue = $classAttribute->attribute( 'data_text1' );
        $dom = $attributeParametersNode->ownerDocument;
        $defaultValueNode = $dom->createElement( 'default-value' );
        $defaultValueNode->appendChild( $dom->createTextNode( $defaultValue ) );
        $attributeParametersNode->appendChild( $defaultValueNode );
    }


    function unserializeContentObjectAttribute( $package, $objectAttribute, $attributeNode )
    {
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
       $rootNode = $attributeNode->getElementsByTagName( 'examen' )->item( 0 );
        $xmlString = $rootNode ? $rootNode->ownerDocument->saveXML( $rootNode ) : '';
        $objectAttribute->setAttribute( 'data_text', $xmlString );
    }
    function domString( $domDocument )
    {
        $ini = eZINI::instance();
        $xmlCharset = $ini->variable( 'RegionalSettings', 'ContentXMLCharset' );
        if ( $xmlCharset == 'enabled' )
        {
            $charset = eZTextCodec::internalCharset();
        }
        else if ( $xmlCharset == 'disabled' )
            $charset = true;
        else
            $charset = $xmlCharset;
        if ( $charset !== true )
        {
            $charset = eZCharsetInfo::realCharsetCode( $charset );
        }
        $domString = $domDocument->saveXML();
        return $domString;
    }
    function xmlString()
    {
        $doc = new DOMDocument( '1.0', 'utf-8' );

        return $this->domString( $doc );
    }
    function diff( $old, $new, $options = false )
    {
        $diff = new eZDiff();
        $diff->setDiffEngineType( $diff->engineType( 'xml' ) );
        $diff->initDiffEngine();
        $diffObject = $diff->diff( $old, $new );
        return $diffObject;
    }

	function fixupObjectAttributeHTTPInput( $http, $base, $contentObjectAttribute )
	{
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
	}
	function customObjectAttributeHTTPAction( $http, $action, $contentObjectAttribute, $parameters )
	{
//eZFire::debug(__FUNCTION__,"WE ARE HERE");
	}

    function supportsBatchInitializeObjectAttribute()
    {
        return true;
    }
    function deleteStoredObjectAttribute( $objectAttribute, $version = null )
    { 
        //Gets called on remove object AND remove draft
	  //how do we tell the difference?
        
        $exam = exam::fetch( $objectAttribute->attribute( 'contentobject_id' ) );
        if ( is_object( $exam ) )
        {
			//This removes a version
			if ($version) {
				$exam->removeVersion($objectAttribute->attribute( 'contentobject_id' ), $version, $objectAttribute->attribute( 'language_code' ));
			} else {
				//This removes the entire exam	
				$exam->removeExam();
			}
        }
    }
	function onPublish( $contentObjectAttribute, $contentObject, $publishedNodes )
	{

	}
	function eZXMLTextConvert( $inputXML = "" )
	{
		$xmlData = "<section xmlns:image='http://ez.no/namespaces/ezpublish3/image/' xmlns:xhtml='http://ez.no/namespaces/ezpublish3/xhtml/' xmlns:custom='http://ez.no/namespaces/ezpublish3/custom/' >";
		$xmlData .= $inputXML;
		$xmlData .= "</section>";
//eZFire::debug($xmlData,"XMLDATA GOING IN");
		return $xmlData;
/*
		$xmlObject = new eZXMLText( $inputXML, null );
		$inputHandler = $xmlObject->attribute( 'input' );
		$data =& $inputHandler->convertInput( $xmlData );
		$domString =& eZXMLTextType::domString( $data[0] );

		$domString = preg_replace( "#<paragraph> </paragraph>#", "<paragraph>&nbsp;</paragraph>", $domString );
		$domString = str_replace ( "<paragraph />" , "", $domString );
		$domString = str_replace ( "<line />" , "", $domString );
		$domString = str_replace ( "<paragraph></paragraph>" , "", $domString );
		$domString = preg_replace( "#<paragraph>&nbsp;</paragraph>#", "<paragraph />", $domString );
		$domString = preg_replace( "#<paragraph></paragraph>#", "", $domString );

		$domString = preg_replace( "#[\n]+#", "", $domString );
		$domString = preg_replace( "#&lt;/line&gt;#", "\n", $domString );
		$domString = preg_replace( "#&lt;paragraph&gt;#", "\n\n", $domString );

		$xml = new eZXML();
		$tmpDom = $xml->domTree( $domString, array( 'CharsetConversion' => false ) );
		$domString = eZXMLTextType::domString( $tmpDom );

		return $domString;
*/
	}
}

eZDataType::register( ExamenType::DATA_TYPE_STRING, "ExamenType" );

?>
