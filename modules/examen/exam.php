<?php
/**
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */

$Module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$Result = array();
$errors = array();
$status = "";

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
	} else {
		$dataMap = $contentObject->DataMap();
	}

	if ( time() < $dataMap["start_date"]->DataInt OR ( time() > $dataMap["end_date"]->DataInt AND $dataMap["end_date"]->DataInt > 0 ) ) {
		$errors[] = "date_out_of_bounds";
	}
}

//The language and version may not be set?  They must be set to get anything in the examArray.  This should always come from the node view so that the language and version are correct for the siteaccess.
if (count($errors) == 0) {
 	//Need to do this after I have an examID
	$status = $http->sessionVariable( 'status['.$examID.']' );

	if ($http->hasPostVariable( "exam_status" ) ) { //This means someone came to the full view with an active session.
		if ( $http->postVariable( "exam_status" ) == false OR $http->postVariable( "exam_status" ) != "" ) {
			exam::removeSession( $http, $examID );
		}
	}

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

	if ( $http->hasSessionVariable( 'hash['.$examID.']' )) {
		$hash =  $http->sessionVariable( 'hash['.$examID.']' );
	} else { //First time through on this exam
		$hash = md5( eZSession::getUserSessionHash().time().rand() );
			$http->setSessionVariable( 'hash['.$examID.']', $hash );
			$http->setSessionVariable( 'status['.$examID.']', "FIRST" );
		if ( !eZSession::userHasSessionCookie() ) { //Have to check every time just in case someone turns cookies off in the middle
			$errors[] = "i_can_haz_no_cookie";
		}
	}

	if ( $status == "RETEST" ) {
		$http->setSessionVariable( 'status['.$examID.']', "RETEST" );
	}

	if ($dataMap["timeout"]->DataInt != 0 ) {
		if( $http->hasSessionVariable( 'timestamp['.$examID.']'  ) ) {
			//if you have a timestamp and an index and datamap timeout is set, see if the last bunch of questions timed-out
			if ( $http->hasSessionVariable( 'count['.$examID.']' ) ) {
				if ( $http->sessionVariable( 'count['.$examID.']' ) != 0 ) {
					if ( time() >  $http->sessionVariable( 'count['.$examID.']' ) * $dataMap["timeout"]->DataInt  + $http->sessionVariable( 'timestamp['.$examID.']' ) ) {
						$errors[] = "user_timed_out";
					}
				}
			}
		}
	}

	$http->setSessionVariable( 'timestamp['.$examID.']', time() );
} //end if no errors

if (count($errors) == 0) {
	/********************************
	*                               *
	*    START EXAM                 *
	*                               *
	********************************/
	$index = $http->hasSessionVariable( 'index['.$examID.']' ) ? $http->sessionVariable( 'index['.$examID.']' ) : 0;
	$questionCount=0;
	$examArray = array();
	$groupArray = array();
	$conditionRemoveArray = array();
	$answerConditionArray = array();
	$conditionArray = array();
	$resultArray = array();

	if ($http->hasSessionVariable( 'exam_array['.$examID.']' )) {
		$examArray = $http->sessionVariable( 'exam_array['.$examID.']' );
		if ($http->hasSessionVariable( 'condition_array['.$examID.']' )) {

			$conditionArray = $http->sessionVariable( 'condition_array['.$examID.']' );
		}
		if ($http->hasSessionVariable( 'result_array['.$examID.']' )) {
			$resultArray = $http->sessionVariable( 'result_array['.$examID.']' );
		}
	} else {
		/********************************
		*                               *
		*    FIRST TIME THROUGH         *
		*                               *
		********************************/

		/* First time through have to initialize the element list in exam_array
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
		//a non-random exam.
		$random=true;
		$conditionObjectArray = examAnswer::getConditions($examID,$examVersion,$examLanguage);
		/* Conditions
			if [not] picked	Remove			text, group, question 1 5
			if [not] picked	Add				text, group, question 2 6
			if [not] picked	Follow With		text, group, question 3 7
			if [not] picked	Display in Resuts	text				  4 8

			Conditions that override Random UNLESS the <conditional element> is in the same group and the group is NOT random and the priorty of the question is less than the <conditional element>.  Since a group cannot be a member of a group it will always override random
				if [not] picked	Remove
			1 5
			Conditions that imply that the element must be removed from the initial list
				if [not] picked	Add
				if [not] picked	Display in Resuts
				if [not] picked	Follow With
			2 6 4 8 3 7
		*/

		foreach($conditionObjectArray as $condition) {
			switch ($condition->option_id) { //This could be a mod at this point but I have a funny feeling this will be extended
				case 1:
				case 5:
					$random=false;
					break;
/* Follow with is changed to Add and follow with
				case 3:
				case 7:
					//Have to put these in the check array so that we can check on them
					$answerConditionArray[$condition->option_value] = array( 'answer_id' => $condition->ID, 'option_id' =>  $condition->option_id, 'option_value' => $condition->option_value );
					$random=false;
					break;
*/
				case 2:
				case 4:
				case 6:
				case 8:
				case 3:
				case 7:
					$conditionRemoveArray[] = $condition->option_value;
					break;
			}
			/*Gotta match on the question id to be able to do the NOT - also has to be an array - otherwise only the last condition is evaluated */
			$conditionArray[$condition->questionID][] = array( 'answer_id' => $condition->ID, 'option_id' =>  $condition->option_id, 'option_value' => $condition->option_value );
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
						$examArray[]=array($element->ID , "");
					//}
					break;
				case "text":
					$examArray[]=array($element->ID , "");
					break;
				case "question":
					$examArray[]=array($element->ID , "");
					$questionCount++;
					break;
				case "group": //Now we have to recursively do the whole thing again, doh
					$groupArray = array();
					$children = $element->children;
					if ( $element->option->random == 1 ) {
						$childRandom = true;
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
									$groupArray[] = array($child->ID , "");
								}
								break;
							case "text":
									$groupArray[] = array($child->ID , "");
							case "question":
									$groupArray[] = array($child->ID , "");
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
		$http->setSessionVariable( 'condition_array['.$examID.']',$conditionArray );
	} //end first time through

	/********************************
	*                               *
	*    HANDLE ANSWERS             *
	*                               *
	********************************/
	//if has submit - save answer to array and check for conditions - have to do this BEFORE we hit the results
	$checkList = array();
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
				foreach($conditionArray[$keyCheck] as $conditionCheck ) {
					$answerID = $http->variable( "answer_".$keyCheck);
					$answer_id = $conditionCheck['answer_id'];
					$option_id = $conditionCheck['option_id'];
					$option_value = $conditionCheck['option_value'];

					switch ( $option_id ) {
						case 1: //if picked remove
							if ( $answerID == $answer_id ){
								$examArrayKey = array_search( $option_value, $examID_array );
								if ( $examArray[$examArrayKey][1] == "" ) { //Only remove unanswered
									unset($examArray[$examArrayKey]);
									unset($examID_array[$examArrayKey]);
								}
							}
							break;
						case 2: //if picked add
							if ( $answerID == $answer_id ){
								$examArray[] = array( $option_value, "");
								$conditionAdd = true;
							}
							break;
						case 3: //if picked follow with
							if ( $answerID == $answer_id ){
								array_splice($examArray, $index, 0, array( array( $option_value, "") ) );
								$conditionAdd = true;
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
								if ( $examArray[$examArrayKey][1] == "" ) { //Only remove unanswered
									unset($examArray[$examArrayKey]);
									unset($examID_array[$examArrayKey]);
								}
							}
							break;
						case 6: // if not picked add, $element->type
							if ( $answerID != $answer_id ){
								if(!in_array($keyCheck,$examID_array)){ //doesn't already exist;
									$examArray[] = array( $option_value, "");
									$conditionAdd = true;
								}
							}
							break;
						case 7: //if not picked follow with
							if ( $answerID != $answer_id ){
								array_splice($examArray, $index, 0, array( array( $option_value, "") ) );
								$conditionAdd = true;
							}
							break;
						case 8: //if not picked diplay text in results
							if ( $answerID != $answer_id ){
								$resultArray[$keyCheck] = $option_value;
								$http->setSessionVariable( 'result_array['.$examID.']' , $resultArray );
							}
							break;
					} //end switch
				}
			}
		}
	} //end foreach
	$http->setSessionVariable( 'exam_array['.$examID.']' , $examArray );
	/********************************
	*                               *
	*    RESULTS                    *
	*                               *
	********************************/
	$mode = "";
	if ($http->hasPostVariable( 'mode' )) {
		$mode = $http->postVariable( 'mode' );
	}
//if it's simple mode then we should be dropping through right now by matching on the $questionCount
//$questionCount only exists the first time through.  Doh.
/*
eZFire::debug($mode, "MODE" );
eZFire::debug(count($checkList),"COUNT CHECKLIST");
eZFire::debug($questionCount,"QUESTION COUNT");
eZFire::debug(count($examArray),"COUNT EXAM ARRAY");
eZFire::debug($index ,"INDEX");
eZFire::debug($conditionAdd ? "true" : "false","CONDITION ADD");
*/
	$examCount=count($examArray);
	if ( ( $mode == 'simple' AND count($checkList) == $questionCount ) OR ( $examCount <= $index AND $conditionAdd == false ) OR $examCount <= 0  OR ( $examCount - $index <= 0 AND $conditionAdd == false )) {
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
		//$hash = $http->getSessionKey();

		$originalExamObjectID = $examID;
		if ( $status == "RETEST" ) {
			$followup = true;
			$relatedObjects = eZContentFunctionCollection::fetchReverseRelatedObjects( $examID, false, array( 'common' ), false );
			foreach( $relatedObjects['result'] as $relatedObject ) {
				if ( $relatedObject->attribute( 'class_identifier' ) == "exam" ) {
					$originalExamObjectID = $relatedObject->attribute( 'id' );
					break;
				}
			}
		}

		if (!$dataMap["pass_threshold"]->DataInt) { //otherwise it's a survey so always save statistics
			$survey = true;
		}
		//exam::removeSession( $http, $examID );
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
//We're going to have to save the resultArray session variable here too, otherwise there is no way to display it in the results
//We need the correct count even if we aren't saving results
					if ( $saveResults == 1 OR $survey == true ) {
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
					}//end if question
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
				$exam = exam::fetch( $originalExamObjectID );
					if ($exam) { //Otherwise no elements - should never happen.
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
				}
			} else {//if save results
//WE NEED $score and $passed IN A SESSION VARIABLE IF WE DONT SAVE RESULTS
				$http->setSessionVariable( 'passed['.$examID.']', $passed );
				$http->setSessionVariable( 'score['.$examID.']', $score );
			}
		} //if not DONE

		if ( $followup ) { //Means we finished the restest
			$http->setSessionVariable( 'status['.$examID .']' ,"FOLLOWUP" );
		}

		//Since the result page is what we go to from here AS WELL AS getting archived results we have to do a redirect to view
		//that means NO tpl variables can be passed and we'll have to refetch everything anyway
		$Module->redirectToView("result", array( $examID, $hash ) );
	} else { //end results
		//fetch element(s) display element(s)
		/********************************
		*                               *
		* HANDLE MULTI-PAGE EXAM OUTPUT *
		*                               *
		********************************/
		$type = "";
		$recurseCheck=0;
		$examCount=count($examArray);
		while($index < $examCount AND $type != "pagebreak" AND $recurseCheck < 10 ) {
//Hmmm might want to put a recursive check here
			$elementID = $examArray[$index][0];
			if($recurseCheck != 0 AND in_array($elementID,$conditionArray) ) {
				continue;
			}
			$element = examElement::fetch( $elementID );
				switch($element->type) {
					case "pagebreak":
						$type="pagebreak";
						$index++;
						//Have to check if there are multiple pagebreaks in a row, this can happen with conditional responses.
						if ($conditionAdd){
							for($i=$index;$i<=$examCount;$i++){
								$pagecheckID=$examArray[$i][0];
								$pagecheck=examElement::fetch( $pagecheckID );
								if ($pagecheck->type == "pagebreak" ) {
									$index++;
								}else{
									break;
								}
							}
						}
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

if($examCount == $index AND $dataMap["show_results"]->DataInt == 0) {
	$tpl->setVariable("show_result", false );
}else{
	$tpl->setVariable("show_result", true );
}
		$http->setSessionVariable( 'index['.$examID.']' , $index );
		$http->setSessionVariable( 'count['.$examID.']' , ($recurseCheck) );
		$tpl->setVariable("random", $random );
		$tpl->setVariable("exam_id", $examID );
		$tpl->setVariable("elements", $elements );
		$Result['content'] = $tpl->fetch( 'design:examen/view/element.tpl' );
		$Result['path'] = array(	array(	'url' => false,
									'text' => ezpI18n::tr( 'design/exam', 'Exam' ) ),
							array(	'url' => false,
									'text' =>  $examID ) );
	}
}
if (count($errors) != 0) { /*Got errors*/
	exam::removeSession( $http, $examID );
	$tpl->setVariable("errors", $errors);
	$Result['content'] = $tpl->fetch( 'design:examen/view/error.tpl' );
	$Result['path'] = array(	array(	'url' => false,
								'text' => ezpI18n::tr( 'design/exam', 'Exam' ) ),
						array(	'url' => false,
								'text' => ezpI18n::tr( 'design/exam', 'Error' ) ) );
}
?>
