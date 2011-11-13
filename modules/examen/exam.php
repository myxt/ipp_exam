<?php

$Module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$Result = array();
$errors = array();
$examArray = array();

/**************************************
*                                     *
* INITIALIZE VARIABLES                *
*                                     *
**************************************/

if ( $http->hasPostVariable( "exam_id" ) ) {
	$examID = $http->variable( "exam_id" );
} else {
	$examID = $Params['exam_id'];
}
if (count($errors) == 0) { // only need these the first time through?
	if (!ctype_digit($examID)) {  //no exam_id, we got nothing then
		$errors[] = "no_exam_id";
	}
	$contentObject = eZContentObject::fetch( $examID );
	if (!is_object($contentObject)) {
		/*if either of these is not an object something went bad wrong*/
		$errors[] = "no_object";
	} elseif ( $contentObject->attribute( "class_identifier" ) != "exam" ) {
		$errors[] = "object_not_exam";
	}
}
//The language and version may not be set?  They must be set to get anything in the examArray.  This should always come from the node view so that the language and version are correct for the siteaccess.
if (count($errors) == 0) { 
	if (!$http->hasSessionVariable( 'index['.$examID.']' )) { // only need these the first time through or retest
			if ( $http->hasPostVariable( "exam_version" ) ) {
				$examVersion = $http->variable( "exam_version" );
			} else {
				$examVersion = $contentObject->CurrentVersion;
				//$errors[] = "no_exam_version";
			}
			if ( $http->hasPostVariable( "exam_language" ) ) {
				$examLanguage = $http->variable( "exam_language" );
			} else {
				$examLanguage = $contentObject->CurrentLanguage;
				//$errors[] = "no_exam_language";
			}
	} else {
		if (  $http->sessionVariable( 'index['.$examID.']' ) == 0) { //This is retest
			if ( $http->hasPostVariable( "exam_version" ) ) {
				$examVersion = $http->variable( "exam_version" );
			} else {
				$examVersion = $contentObject->CurrentVersion;
				//$errors[] = "no_exam_version";
			}
			if ( $http->hasPostVariable( "exam_language" ) ) {
				$examLanguage = $http->variable( "exam_language" );
			} else {
				$examLanguage = $contentObject->CurrentLanguage;
				//$errors[] = "no_exam_language";
			}
		}
	}
} //end if no errors

//Need to do this after I have an examID
if (!$http->hasSessionVariable( 'status['.$examID.']' )) { //Have to write something to the cookie
	//$http->setSessionVariable( 'status['.$examID.']', time() );
	$http->setSessionVariable( 'status['.$examID.']', "FIRST" );
}
/* If someone hits the back button we just drop through to the results page again.
 elseif ( $http->sessionVariable( 'status' ) == "DONE" ) {  //Maybe should show the results again?  Dunno.
	$errors[] = "threshold_exceeded";
}
*/

//Got to do this AFTER we set something otherwise there potentially isn't a session yet.
//if ( !eZSession::userHasSessionCookie() ) { //Have to check every time just in case someone turns cookies off in the middle
$hash = eZSession::getUserSessionHash();
//$sessionKey = $http->getSessionKey();
if ( !$hash ) {
	$errors[] = "i_can_haz_no_cookie";
}

if ($http->hasSessionVariable( 'exam_array['.$examID.']' )) {
	$examArray = $http->sessionVariable( 'exam_array['.$examID.']' );
	//This should be empty at the beginning of a retest because we potentially have conditional elements in the array.  So we have to rerun the first time through code to build the exam array again.
}
//This is always dynamic so it can't be cached - unless it is really simple.... hmmm....

/**************************************
*                                     *
* RESET SESSION VARIABLES FOR TESTING *
*                                     *
***************************************

$http->setSessionVariable( 'status['.$examID.']' , "FIRST" ); //Status - of someone is taking two tests at the same time.
$http->setSessionVariable( 'index['.$examID.']' , 0 ); //Running count of where we are
$http->setSessionVariable( 'exam_array['.$examID.']', array() ); //array of elements
$http->setSessionVariable( 'condition_array['.$examID.']', array() ); //array of conditions to match on
$http->setSessionVariable( 'result_array['.$examID.']', array() ); //id of text elements to add to the result page on condition
$http->setSessionVariable( 'score['.$examID.']', 0 ); //id of text elements to add to the result page on condition
*/

if (count($errors) == 0) {
	$dataMap = $contentObject->DataMap();
	/*start exam*/
	$index = $http->hasSessionVariable( 'index['.$examID.']' ) ? $http->sessionVariable( 'index['.$examID.']' ) : 0;
	$questionCount=0;
	if ($http->hasPostVariable( 'mode' )) {
		$mode = $http->postVariable( 'mode' );
	}
	/********************************
	*                               *
	*    FIRST TIME THROUGH         *
	*                               *
	********************************/
/*First time through, figure out what the question array is and load it in the session*/
	$conditionRemoveArray = array();
	$answerConditionArray = array();
	$conditionArray = array();
	$resultArray = array();
	if (count($examArray) < 1) {
		/* First time through have to initialize the element list
			This will be array (	
							array([element_id] => [user_answer])
							array([element_id] => [user_answer])
							array([element_id] => [user_answer])
						)
		*/
		//We have to get only the top level structure here first so that we can shuffle on the group level if the random option is set
		$examElements = exam::getStructure($examID,$examVersion,$examLanguage );
		//but we don't want to shuffle if there are pagebreaks, except if the pagebreak is the last element.
		//Doesn't make much sense to shuffle text blocks either.  I can only really see textblocks as being useful as a condition or for 
		//a non-random exam..
		$random=true;
		$conditionObjectArray = examAnswer::getConditions($examID,$examVersion,$examLanguage);
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
					$conditionRemoveArray[] = $condition->option_value;
					break;
			}
			/*Gotta match on the question id to be able to do the NOT*/
			$answerConditionArray[$condition->questionID] = array( 'answer_id' => $condition->ID, 'option_id' =>  $condition->option_id, 'option_value' => $condition->option_value );
		}
		$elementCount = count($examElements);

		/*Check if anything overrides random*/
		if ( $dataMap["random"]->DataInt == 1 AND $random == true ) {
			
			foreach($examElements as $ElementIndex => $element) {
				if ($element->attribute( 'type' ) == "pagebreak") {//If there is any top-level pagebreak that is NOT the last element... random has to be turned off.
						if ( $ElementIndex != $elementCount ) {
							$random = false;
						}
						break; //don't need more than one
				}
				if ($element->attribute( 'type' ) == "question") {//parse conditiosn
						if ( $ElementIndex != $elementCount ) {
							$random = false;
						}
						break; //don't need more than one
				}
				if ($element->attribute( 'type' ) == "group") {//Do it all again for the children, sigh.
						if ( $ElementIndex != $elementCount ) {
							$random = false;
						}
						break; //don't need more than one
				}
			}
			if ( $random ) {
				shuffle($examElements);
			}
		}

		foreach($examElements as $element) {
			if( in_array( $element->ID, $conditionRemoveArray ) ) {
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

		$http->setSessionVariable( 'exam_array['.$examID.']' , $examArray );
		$http->setSessionVariable( 'condition_array['.$examID.']',$answerConditionArray );
	} else { 
		if ($http->hasSessionVariable( 'condition_array['.$examID.']' )) {

			$conditionArray = $http->sessionVariable( 'condition_array['.$examID.']' );
		}
		if ($http->hasSessionVariable( 'result_array['.$examID.']' )) {
			$resultArray = $http->sessionVariable( 'result_array['.$examID.']' );
		}
	}

	/********************************
	*                               *
	*    HANDLE ANSWERS             *
	*                               *
	********************************/

	//if has submit - save answer to array and check for conditions - have to do this BEFORE we hit the results

	foreach($examArray as $checkIndex => $checkArray){ //loading the answers just in case a condition exists to remove something that was answered
		if ( $http->hasPostVariable( "answer_".$checkArray[0]) ) {
			$answerID = $http->variable( "answer_".$checkArray[0]);
			$examArray[$checkIndex][1] = $answerID;
			$checkList[] = $checkArray[0];
		}
		$examID_array[] = $checkArray[0];
	}
	$conditionAdd = false;
	//Check for condition and add, remove etc. based on condition this can grow or shrink the examArray
	foreach($checkList as $keyCheck){ //foreach condition
		if ( array_key_exists($keyCheck, $conditionArray ) ) { //A condition with this element id exists
			if ( $http->hasPostVariable( "answer_".$keyCheck ) ) { //We have an element answer for that key
				$answerID = $http->variable( "answer_".$keyCheck);
				$answer_id = $conditionArray[$keyCheck]['answer_id'];
				$option_id = $conditionArray[$keyCheck]['option_id'];
				$option_value = $conditionArray[$keyCheck]['option_value'];
				switch ( $option_id ) {
					case 1: //if picked remove
						if ( $answerID == $answer_id ){
							$examArrayKey = array_search( $option_value, $examID_array );
							if ( $examArray[$examArrayKey][1] = "" ) { //Only remove unanswered
								unset($examArray[$examArrayKey]);
								unset($examID_array[$examArrayKey]);
							}
						}
					
						break;
					case 2: //if picked add

						if ( $answerID == $answer_id ){
							$examArray[] = array( $option_value, "" );
							$conditionAdd = true;
						}
						break;
					case 3: //if picked follow with
						if ( $answerID == $answer_id ){
							if(in_array($keyCheck,$examID_array)){ //We can only follow if it's there.
								$examArrayKey = array_search( $option_value, $examID_array );
								if ( $examArray[$examArrayKey][1] = "" ) { //only do it if it hasn't been answered
									$tmpValue = $examArray[$examArrayKey];
//What should the index be here - I have no idea
//Hmmm what if we are messing with a child of a group here?
									$examArray[$examArrayKey] = $examArray[$index+1];
									$examArray[$index+1] = $tmpValue;
								}
							}
						}
						break;
					case 4: //if picked display text in results
						if ( $answerID == $answer_id ){
							$resultArray[$keyCheck] = $option_value;

							$http->setSessionVariable( 'result_array['.$examID.']' , $resultArray );
						}
						break;
					case 5: //if not picked remove
						if ( $answerID != $answer_id ){
							$examArrayKey = array_search( $option_value, $examID_array );
							if ( $examArray[$examArrayKey][1] = "" ) { //Only remove unanswered
								unset($examArray[$examArrayKey]);
								unset($examID_array[$examArrayKey]);
							}
						}
						break;
					case 6: // if not picked add
						if ( $answerID != $answer_id ){
							if(!in_array($keyCheck,$examID_array)){ //doesn't already exist;
								$examArray[] = array( $option_value, "" );
								$conditionAdd = true;
							}
						}
						break;
					case 7: //if not picked follow with
						if ( $answerID != $answer_id ){
							if(in_array($keyCheck,$examID_array)){ //We can only follow if it's there.
								$examArrayKey = array_search( $option_value, $examID_array );
								if ( $examArray[$examArrayKey][1] = "" ) { //only do it if it hasn't been answered
									$tmpValue = $examArray[$examArrayKey];
//What should the index be here - I have no idea it'll especially be confusing on multi-asnwer pages
//Hmmm what if we are messing with a child of a group here?
									$examArray[$examArrayKey] = $examArray[$index+1];
									$examArray[$index+1] = $tmpValue;
								}
							}
						}
						break;
					case 8: //if not picked diplay text in results
						if ( $answerID != $answer_id ){
							$resultArray[$keyCheck] = $option_value;
							$http->setSessionVariable( 'result_array['.$examID.']' , $resultArray );
						}
						break;
				} //end swich
			}
		}
	} //end foreach
	$http->setSessionVariable( 'exam_array['.$examID.']' , $examArray );
	/********************************
	*                               *
	*    RESULTS                    *
	*                               *
	********************************/
//if it's simple mode then we should be dropping through right now by matching on the $questionCount

	if ( ( $mode == 'simple' AND count($checkList) == $questionCount ) OR ( count($examArray) <  $index + count($checkIndex) AND $conditionAdd == false ) OR count($examArray) == 0 ) {
	//We're done - time for results
	/* We should really only save the results to the database (if that option is set) and then redirect to a results page since
        the logic for viewing the results at a later date will have to be the same.  of course, if we aren't to save the results
        we'll have to use the session values instead of database values which will maybe get dicey.  I think I may have to save the
        examArray session variable to the database too. */
	//Since the result page is what we go to from here AS WELL AS getting archived results we have to do a redirect to view
	//that means NO tpl variables can be passed and we'll have to refetch everything anyway
		$status = $http->sessionVariable( 'status['.$examID.']' ) ;
		$followup = false;
		$survey = false;
		$passed = false;
		$correct = false;
		$correctCount = 0;
		$saveResults = $dataMap["save_results"]->DataInt;
		//Session key is different from hash.
		$hash = $http->getSessionKey();

		if ( $status == "RETEST" ) {
			$followup = true;
		}
		if (!$dataMap["pass_threshold"]->DataInt) { //otherwise it's a survey so always save statistics
			$survey = true;
		}
		if ( $status != "DONE" ) { //If this is set to DONE someone hit the back button and we should just go to the results page
			
			//Save question results
			//$session = $http->getSessionKey() ? $http->getSessionKey() : md5sum(date(now));
			//$hash = md5($session.$secretKey.$examID);
			//If it's a dated result we'll have to add the exam id just in case they did multiple exams under one session
			/* Since the list of answerable questions is dynamic, we have to go by what is is examArray and assume it is correct.  Which means that if there is ever multiple answers or no answer at all, the totals/score will be off */
			$questionIndex = 0;
			foreach( $examArray as $examAnswer ) {
				//We need these even if we don't save results
				$elementObject = examElement::fetch( $examAnswer[0] );
				if ( $elementObject->type == "question" ) {
					$questionIndex++;
					if ($survey == false) {
						$answerObject = examAnswer::fetch( $examAnswer[1] );
						$correct = $answerObject->correct;
						if ( $correct == true ) {
							$correctCount++;
						}
					}
				}
//We're going to have to save the resultArray session variable here too, otherwise there is no way to display it in the results
//We need the correct count even if we aren't saving results
				if ( $saveResults == 1 OR $survey == true ) {
					$newResult = new examResult();
					$newResult = new examResult();
					$newResult->setAttribute( 'contentobject_id', $examID );
					$newResult->setAttribute( 'hash', $hash );
					$newResult->setAttribute( 'question_id', $examAnswer[0] );
					$newResult->setAttribute( 'answer', $examAnswer[1] );
					$newResult->setAttribute( 'correct', $correct );
					$newResult->setAttribute( 'followup', $followup );
					$newResult->setAttribute( 'conditional', $resultArray[$examAnswer[0]] );
					$newResult->store();
				}//end save results		
			}//end foreach
			$score = 0; //For survey

			if ($dataMap["pass_threshold"]->DataInt) { //otherwise it's a survey
				if ($correctCount != 0) {//no division by zero here - dammit.
					//$score = 100 - ceil( ( $resultIndex - $correctCount ) / $resultIndex * 100 );
					$score = ceil( $correctCount / $questionIndex * 100 );
					if ( $score >= $dataMap["pass_threshold"]->DataInt ) {
						$passed = true;
					} else {
						$passed = false;
					}
				}
			}
			if ( $saveResults ) {
				$exam = exam::fetch( $examID );
				$totalExam = $exam->increment( 'count' );
				if (!$survey AND $passed) { //If it's a survey, then this won't mean anything
					if ($followup) {
						$secondPass = $exam->increment( 'pass_second' );
					}else{
						$firstPass = $exam->increment( 'pass_first' );
					}
				}	
				$highScore = $exam->highScore( $score );
				$oldTally = $exam->attribute( 'score_tally' );
				$exam->setAttribute( 'score_tally', $oldTally + $score );
				$exam->store();
			} else {//if save results
//WE NEED $score and $passed IN A SESSION VARIABLE IF WE DONT SAVE RESULTS
				$http->setSessionVariable( 'passed['.$examID.']', $passed );
				$http->setSessionVariable( 'score['.$examID.']', $score );
			}
		} //if not DONE

		//Reset retest status indicator
		if ( $http->sessionVariable( 'status['.$examID.']' ) == "FIRST" ) {
			if ( $dataMap["retest"]->DataInt == 1 AND $passed == false ) { //if we passed, we are done
				$http->setSessionVariable( 'status['.$examID.']', "RETEST" );
			} else {
				$http->setSessionVariable( 'status['.$examID.']', "DONE" );
			}
		} else { //Closing out a retest OR it's already done
			$http->setSessionVariable( 'status['.$examID.']', "DONE" );
		}
//Reinitialize values for retest
//$http->removeSessionVariable()
$http->setSessionVariable( 'index['.$examID.']' , 0 ); //Running count of where we are
$http->setSessionVariable( 'exam_array['.$examID.']', array() ); //array of elements
$http->setSessionVariable( 'condition_array['.$examID.']', array() ); //array of conditions to match on
$http->setSessionVariable( 'result_array['.$examID.']', array() ); //id of text elements to add to the result page on condition
//$http->setSessionVariable( 'score['.$examID.']', 0 ); //id of text elements to add to the result page on condition
		//Since the result page is what we go to from here AS WELL AS getting archived results we have to do a redirect to view
		//that means NO tpl variables can be passed and we'll have to refetch everything anyway
		$Module->redirectToView("result", array( $examID, $hash ) );
//$Result['content'] = $tpl->fetch( 'design:examen/results/default/result.tpl' );
	//	$Module->redirectToView("result", array( $examID, $hash ), array( 0 => "x"), array( "dum" => "unorderParams", "doh" => "userParamenters" ), "anchor" );
/*
    function redirectToView( $viewName = '', $parameters = array(),
                             $unorderedParameters = null, $userParameters = false,
                             $anchor = false )
*/

	} else { //end results
		//fetch element(s) display element(s)
		/********************************
		*                               *
		* HANDLE MULTI-PAGE EXAM OUTPUT *
		*                               *
		********************************/
		$type = "";
		while($index < count($examArray) AND $type != "pagebreak" AND $recurseCheck < 10 ) {
//Hmmm might want to put a recursive check here
			$elementID = $examArray[$index][0];
			if($recurseCheck != 0 AND in_array($elementID,$conditionArray) ) {
				continue;
			}
			$element = examElement::fetch( $elementID );
				switch($element->type) {
					case "pagebreak":
						$index++;
						$type="pagebreak";
						break;
					case "text":
					case "question":
					case "group":
//Can't have a pagebreak in a group otherwise it'll strand the rest of the group? 
						//$type = $element->type;
						$elements[] = $element;
						$index++;
						break;
				} //end switch
			$recurseCheck++;
		}
		$http->setSessionVariable( 'index['.$examID.']' , $index );
		$tpl->setVariable("exam_id", $examID );
		$tpl->setVariable("elements", $elements );
		$Result['content'] = $tpl->fetch( 'design:examen/view/element.tpl' );
		$Result['path'] = array(	array(	'url' => false,
									'text' => ezpI18n::tr( 'design/exam', 'Exam' ) ),
							array(	'url' => false,
									'text' =>  $examID ) );
	}
} 
if (!$Result['content']) { /*Got errors*/
	$tpl->setVariable("errors", $errors);
	$Result['content'] = $tpl->fetch( 'design:examen/view/error.tpl' );
	$Result['path'] = array(	array(	'url' => false,
								'text' => ezpI18n::tr( 'design/exam', 'Exam' ) ),
						array(	'url' => false,
								'text' => ezpI18n::tr( 'design/exam', 'Error' ) ) );
}
?>
