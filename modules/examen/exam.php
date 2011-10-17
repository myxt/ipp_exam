<?php
//eZFire::debug("IN EXAM.PHP");
$Module = $Params['Module'];
$settingsINI = eZINI::instance( 'examen.ini' );
$secretKey = $settingsINI->variable('examSettings','secretKey');
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$Result = array();
$errors = array();
$examArray = array();


/*We have to get the objectID from the NodeID to get the language and version*/

/*First time through, figure out what the question array is and load it in the session*/
/* turn this off for testing
if (!$http->hasSessionVariable( 'status' )) { //Have to write something to the cookie before we can check that we have one.
	$http->setSessionVariable( 'status', "FRIST" );
/Set the time stamp here too

} elseif ( $http->sessionVariable( 'status' ) == "DONE" ) {  //Maybe should show the results again?  Dunno.
	$errors[] = "threshold_exceeded";
}
*/
if ( !eZSession::userHasSessionCookie() ) { //Have to check every time just in case someone turns cookies off in the middle - is that really what we are checking here?  I don't think so.
//WHAT DO WE DO HERE... RETEST FLAG WON'T WORK AND COMPLICATED MODE WON'T WORK.. WE ALSO WON'T HAVE A UNIQUE ID TO STORE THE RESULTS.  WE SHOULD JUST ERROR OUT HERE, NO?  I'LL DO NOTHING FOR NOW
//This isn't working right
	//$errors[] = "i_can_haz_no_cookie";
	//eZFire::debug("NO COOKIE?");
}

if ( $http->hasPostVariable( "exam_id" ) ) {
	$examID = $http->variable( "exam_id" );
} else {
	$examID = $Params['exam_id'];
}

if (!ctype_digit($examID)) {  //no exam_id, we got nothing then
	$errors[] = "no_exam_id";
}
if (count($errors) == 0) { // only need these the first time through?
	if (!$http->hasSessionVariable( 'index['.$examID.']' )) {
		if ( $http->hasPostVariable( "exam_version" ) ) {
			$examVersion = $http->variable( "exam_version" );
		} else {
			$errors[] = "no_exam_version";
		}
		if ( $http->hasPostVariable( "exam_language" ) ) {
			$examLanguage = $http->variable( "exam_language" );
		} else {
			$errors[] = "no_exam_language";
		}
	}
//Do we need this the second pass?  What if someone removes the version while someone is in the middle of a test. We need this for the first pass to get the value for random and for tthe results pass to get the value for SaveResults
	$contentObject = eZContentObject::fetch( $examID );
//eZFire::debug($contentObject->attribute( "class_identifier" )  ,"CLASS IDENTIFIER");
//eZFire::debug($examID,"ID");
//eZFire::debug($examVersion,"Version");
//eZFire::debug($examLanguage,"Language");

	if (!is_object($contentObject)) {
		/*if either of these is not an object something went bad wrong*/
		$errors[] = "no_object";
	}
	if ( $contentObject->attribute( "class_identifier" ) != "exam" ) {
		$errors[] = "object_not_exam";
	}
} //end if no errors
if ($http->hasSessionVariable( 'exam_array['.$examID.']' )) {
	$examArray = $http->sessionVariable( 'exam_array['.$examID.']' );
//eZFire::debug($examArray,"GETTING EXAM ARRAY FROM SESSION");
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
//eZFire::debug($errors,"ERRORS");

if (count($errors) == 0) {
	$dataMap = $contentObject->DataMap();
	/*start exam*/
	$index = $http->hasSessionVariable( 'index['.$examID.']' ) ? $http->sessionVariable( 'index['.$examID.']' ) : 0;
//eZFire::debug($index,"INDEX");
	
	/********************************
	*                               *
	*    FIRST TIME THROUGH         *
	*                               *
	********************************/


	if (count($examArray) < 1) {
//eZFire::debug("CALCULATING EXAM ARRAY");
		/* First time through have to initialize the element list
			This will be array (	
							array([element_id] => [user_answer])
							array([element_id] => [user_answer])
							array([element_id] => [user_answer])
						)
		*/
//eZFire::debug($examID,"ID");
//eZFire::debug($examVersion,"Version");
//eZFire::debug($examLanguage,"Language");
		//We have to get only the top level structure here first so that we can shuffle on the group level if the random option is set
		$examElements = exam::getStructure($examID,$examVersion,$examLanguage );
//eZFire::debug($examElements,"EXAM STRUCTURE");
		//but we don't want to shuffle if there are pagebreaks, except if the pagebreak is the last element.
		//Doesn't make much sense to shuffle text blocks either.  I can only really see textblocks as being useful as a condition or for 
		//a non-random exam..
		$random=true;
		$conditionObjectArray = examAnswer::getConditions($examID,$examVersion,$examLanguage);
//eZFire::debug($conditionObjectArray,"CONDITION ARRAY");
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
//eZFire::debug($condition->option_id,"CONDITION OPTION ID");
//eZFire::debug($condition->option_value,"CONDITION VALUE ID");
			switch ($condition->option_id) { //This could be a mod at this point but I have a funny feeling this will be extended
				case 1:
				case 3:
				case 5:
				case 7:
					//Should have a whole lot of checking going on here
					$random=false;
					break;
				case 2:
				case 4:
				case 6:
				case 8:
//eZFire::debug("IN THE CASE?");
					$conditionRemoveArray[] = $condition->option_value;
					break;
			}
			/*Gotta match on the question id to be able to do the NOT*/
			$answerConditionArray[$condition->question_id] = array( 'answer_id' => $condition->id, 'option_id' =>  $condition->option_id, 'option_value' => $condition->option_value );
//eZFire::debug($conditionRemoveArray,"CONDITION REMOVE ARRAY IN LOOP");
		}
		$elementCount = count($examElements);

		/*Check if anything overrides random*/
		if ( $dataMap["random"]->DataInt == 1 AND $random == true ) {
			
			foreach($examElements as $ElementIndex => $element) {
				if ($element.type == "pagebreak") {//If there is any top-level pagebreak that is NOT the last element... random has to be turned off.
						if ( $ElementIndex != $elementCount ) {
							$random = false;
						}
						break; //don't need more than one
				}
				if ($element.type == "question") {//parse conditiosn
						if ( $ElementIndex != $elementCount ) {
							$random = false;
						}
						break; //don't need more than one
				}
				if ($element.type == "group") {//Do it all again for the children, sigh.
						if ( $ElementIndex != $elementCount ) {
							$random = false;
						}
						break; //don't need more than one
				}
			}
			if ( $random ) {
//eZFire::debug("WE HAVE RANDOM");
				shuffle($examElements);
			}
		}

		foreach($examElements as $element) {
			if( in_array( $element->ID, $conditionRemoveArray ) ) {
//eZFire::debug($element->ID,"THIS SHOULD BE REMOVED");
				continue;
			}
			switch($element->type) {
				case "pagebreak":
					if (!$random) { //if it's random we can toss because we can't use it anyway
						$examArray[]=array($element->ID , "" );
					}
					break;
				case "text":
				case "question":
					$examArray[]=array($element->ID , "" );	
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
							case "question":
									$groupArray[] = array($child->ID , "" );
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
//eZFire::debug($examID,"SHOULD BE GETTING INFO FROM SESSION");

		if ($http->hasSessionVariable( 'condition_array['.$examID.']' )) {

			$conditionArray = $http->sessionVariable( 'condition_array['.$examID.']' );
//eZFire::debug($conditionArray,"GETTING CONDITION ARRAY FROM SESSION");
		}
		if ($http->hasSessionVariable( 'result_array['.$examID.']' )) {
			$resultArray = $http->sessionVariable( 'result_array['.$examID.']' );
//eZFire::debug("GETTING RESULT ARRAY FROM SESSION");
		}
	}

//eZFire::debug($examArray,"EXAM ARRAY");
//eZFire::debug(count($examArray),"EXAM ARRAY COUNT");
//eZFire::debug($conditionArray,"CONDITION ARRAY");

	/********************************
	*                               *
	*    HANDLE ANSWERS             *
	*                               *
	********************************/

	//if has submit - save answer to array and check for conditions - have to do this BEFORE we hit the results

//eZFire::debug($_POST,"POST");
	foreach($examArray as $checkIndex => $checkArray){ //loading the answers just in case a condition exists to remove something that was answered
		if ( $http->hasPostVariable( "answer_".$checkArray[0]) ) {
			$answerID = $http->variable( "answer_".$checkArray[0]);
//eZFire::debug($answerID,"GOT ANSWER");
			$examArray[$checkIndex][1] = $answerID;
			$checkList[] = $checkArray[0];
		}
		$examID_array[] = $checkArray[0];
	}
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
						if ( $answerID = $answer_id ){
							$examArrayKey = array_search( $option_value, $examID_array );
							if ( $examArray[$examArrayKey][1] = "" ) { //Only remove unanswered
								unset($examArray[$examArrayKey]);
								unset($examID_array[$examArrayKey]);
							}
						}
					
						break;
					case 2: //if picked add
						if ( $answerID = $answer_id ){
							if(!in_array($keyCheck,$examID_array)){ //doesn't already exist;
								$examArray[] = array( $option_value, "" );
							}
						}
						break;
					case 3: //if picked follow with
						if ( $answerID = $answer_id ){
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
						if ( $answerID = $answer_id ){
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
							$result_array[$keyCheck] = $option_value;
							$http->setSessionVariable( 'resultArray['.$examID.']' , $resultArray );
						}
						break;
				} //end swich
			}
		}
	} //end foreach
//eZFire::debug($examArray,"EXAM ARRAY BEFORE SETTING SESSION AGAIN");
	$http->setSessionVariable( 'exam_array['.$examID.']' , $examArray );
//eZFire::debug($examArray,"EXAM ARRAY AFTER HANDLE ANSWER");
	/********************************
	*                               *
	*    RESULTS                    *
	*                               *
	********************************/
//eZFire::debug($index,"INDEX");
//eZFire::debug(count($examArray),"COUNT");
	//Results
	if ( count($examArray) <=  $index + count($checkIndex) ) { //We're done - time for results
	/* We should really only save the results to the database (if that option is set) and then redirect to a results page since
        the logic for viewing the results at a later date will have to be the same.  of course, if we aren't to save the results
        we'll have to use the session values instead of database values which will maybe get dicey.  I think I may have to save the
        examArray session variable to the database too. */
		
//eZFire::debug($examArray,"IN RESULTS");

		$followup = false;
		if ( $http->sessionVariable( 'status['.$examID.']' ) == "RETEST" ) {
			$followup = true;
		}
		$survey = false;
		$passed = false;
		$correctCount = 0;
		$saveResults = $dataMap["save_results"]->DataInt;
		$hash = $http->getSessionKey();
//eZFire::debug($saveResults,"SAVE RESULTS");
		if (!$dataMap["pass_threshold"]->DataInt) { //otherwise it's a survey so always save statistics
			$survey = true;
		}
//eZFire::debug($saveResults,"SAVE RESULTS");
//eZFire::debug($survey,"SURVEY");
//eZFire::debug($http->sessionVariable( 'status['.$examID.']' ),"STATUS");
		if ( $http->sessionVariable( 'status['.$examID.']' ) != "DONE" ) { //If this is set to DONE someone hit the back button
			if ( $saveResults OR $survey ) {
//eZFire::debug( "SAVING RESULTS" );
				//Save question results
				//$session = $http->getSessionKey() ? $http->getSessionKey() : md5sum(date(now));
				//$hash = md5($session.$secretKey.$examID);
				//If it's a dated result we'll have to add the exam id just in case they did multiple exams under one session
/* Since the list of answerable questions is dynamic, we have to go by what is is examArray and assume it is correct.  Which means that if there is ever multiple answers or no answer at all, the totals/score will be off */
				foreach( $examArray as $examAnswer ) {
//eZFire::debug($examAnswer[1],"IN DA LOOP");
					$elementObject = examElement::fetch( $examAnswer[1] );
					if ( $elementObject->type == "question" ) {
						$questionIndex++;
if (!$survey) {
						$answerObject = examAnswer::fetch( $examAnswer[1] );
						$correct = $answerObject->correct;
						if ( $correct == true ) {
							$correctCount++;
						}
}
//We're going to have to save the resultArray session variable here too, otherwise there is no way to display it in the results
//eZFire::debug("ARE WE HERE, WHY ARENT WE HERE");
						$newResult = new examResult();
						$newResult->setAttribute( 'contentobject_id', $examID );
						$newResult->setAttribute( 'hash', $hash );
						$newResult->setAttribute( 'question_id', $examAnswer[0] );
						$newResult->setAttribute( 'answer', $examAnswer[1] );
						$newResult->setAttribute( 'correct', $correct );
						$newResult->setAttribute( 'followup', $followup );
						$newResult->setAttribute( 'result_array', implode(",", $result_array ) );
						$newResult->store();
					}
				}
			}//save results		

			if ($dataMap["pass_threshold"]->DataInt) { //otherwise it's a survey
				if ($correctCount != 0) {//no division by zero here - dammit.
					$score = 100 - ceil( ( $resultIndex - $correctCount ) / $resultIndex * 100 );
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
//eZFire::debug($totalExam,"EXAM COUNT SHOULD HAVE INCREMENTED");
				if (!$survey) { //If it's a survey, then this won't mean anything
					if ($followup) {
							$secondPass = $exam->increment( 'pass_second' );
//eZFire::debug($secondPass,"SECOND PASS SHOULD HAVE INCREMENTED");
					}else{
							$firstPass = $exam->increment( 'pass_first' );
//eZFire::debug($totalExam,"FIRST PASS SHOULD HAVE INCREMENTED");
					}
					$highScore = $exam->highScore( $score );
				}	
			} else {//if save results
//WE NEED  $score IN A SESSION VARIABLE IF WE DONT SAVE RESULTS maybe $survey is useful too
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
//eZFire::debug($http->sessionVariable( 'status['.$examID.']' ),"SESSION VARIABLE");
//eZFire::debug("REDIRECTING TO RESULT");
//eZFire::debug($hash,"WHERE DID MY HASH GO");
		$Module->redirectToView("result", array( $examID, $hash ) );

	//	$Module->redirectToView("result", array( $examID, $hash ), array( 0 => "x"), array( "dum" => "unorderParams", "doh" => "userParamenters" ), "anchor" );
/*
    function redirectToView( $viewName = '', $parameters = array(),
                             $unorderedParameters = null, $userParameters = false,
                             $anchor = false )
*/

	} else { //end results
		//fetch element(s) display element(s)
//eZFire::debug("IN THE ELSE FTW");		
		/********************************
		*                               *
		* HANDLE MULTI-PAGE EXAM OUTPUT *
		*                               *
		********************************/
//eZFire::debug($examArray,"EXAM ARRAY BEFORE WHILE LOOP");
//eZFire::debug($index,"INDEX");
		$type = "";
		while($index < count($examArray) AND $type != "pagebreak" AND $recurseCheck < 10 ) {
//eZFire::debug($index,"INDEX");
//Hmmm might want to put a recursive check here
			$elementID = $examArray[$index][0];
			$element = examElement::fetch( $elementID );
				switch($element->type) {
					case "pagebreak":
						if (!$elements) {
							$index++;
						} else {
							$type = $element->type;
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
		$http->setSessionVariable( 'index['.$examID.']' , $index );
		$tpl->setVariable("exam_id", $examID );
		$tpl->setVariable("elements", $elements );
		$Result['content'] = $tpl->fetch( 'design:examen/view/element.tpl' );
	}
} 
if (!$Result['content']) { /*Got errors*/
	$tpl->setVariable("errors", $errors);
	$Result['content'] = $tpl->fetch( 'design:examen/view/error.tpl' );
}
?>