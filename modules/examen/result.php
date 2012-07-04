<?php
/**
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */

/* This can be reached in several ways:

we could be coming from just having taken the test and we have valid session variables - this will be the case if results are not saved to the database... or

we can be coming months later to the exam/hash result that was displayed on the results page the first time we came through.

*/

$Module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$Result = array();
$errors = array();
$status="";
$elements=array();
$survey=false;
$followup=false;

if ( $http->hasPostVariable( "exam_id" ) ) {
	$examID = $http->variable( "exam_id" );
} else {
	$examID = $Params['exam_id'];
}

if (!ctype_digit($examID)) {  //no exam_id, we got nothing then
	$errors[] = "no_exam_id";
} else {
	$contentObject = eZContentObject::fetch( $examID );
	if (!is_object($contentObject)) {
		/*if either of these is not an object something went bad wrong*/
		$errors[] = "no_object";
	} elseif ( $contentObject->attribute( "class_identifier" ) != "exam" ) {
		$errors[] = "object_not_exam";
	}
}


if ( count($errors) == 0 ) {
	/*These are the object options that determine results output
	//	These happen in exam...
	//	$dataMap["mode"]->DataText
	//	$dataMap["retest"]->DataInt
	//	$dataMap["save_results"]->DataInt
	//	These happen here...
	//	$dataMap["show_correct"]->DataInt
	//	$dataMap["certificate"]->DataInt,
	//	$dataMap["show_statistics"]->DataInt
	//	$dataMap["pass_threshold"]->DataInt
	*/
	//If it's a survey, we don't need a hash.  And we need ALL results not just the ones that match to the hash - so we have to figure out if this is a survey first.

	$dataMap = $contentObject->DataMap();
	$survey=false;
	$hash="";
	if ($dataMap["pass_threshold"]->DataInt) { //otherwise it's a survey and we don't care
		$hash = $Params['hash'];
		if (!$hash) {  //no exam_id, we got nothing then
			$hash = $http->sessionVariable( 'hash['.$examID.']' );
			if (!$hash) {  //no exam_id, we got nothing then
				$errors[] = "no_hash";
			}
		}
	} else {
		$survey=true;
	}
	$retestObjectID = $examID;
	if ( $dataMap["pass_threshold"]->DataInt == 0 ) { //it's a survey and results will be different
	// if it's a survey we're going to have to return an array of count( total => x, answer1 => x, answer2 => x );
		//$results = examResult::fetchSurvey( $examID );
		//A survey could still have multiple questions.
		//This is returning the results only for the language and version since there Question and Answer Ids are different for each language and each version.  If someone edits the object the results will be cleared but there isn't any way to do fix this since there is no link between an answer's previous version.
		$exam = exam::fetch( $examID );
		$examVersion = $contentObject->CurrentVersion;
		$examLanguage = $contentObject->CurrentLanguage;
		$examQuestions = $exam->getQuestions( $examVersion, $examLanguage );
		$questionArray=array();
		$countArray = array();
		$totalArray = array();
		$percArray = array();
		foreach ( $examQuestions as $question ) {
			$questionID = $question->ID;
			$elements[] = $question;
			$totalArray[$questionID]= examResult::fetchSurveyQuestionCount( $examID, $questionID );
			foreach($question->getAnswers() as $answer) {
				$answerID = $answer->ID;
				$answerCount = examResult::fetchSurveyAnswerCount( $questionID, $answerID );
				$countArray[$questionID][$answerID] = $answerCount;
				$percArray[$answerID] = round( $answerCount / $totalArray[$questionID] * 100 );

			}
		}
		$tpl->setVariable("totals", $totalArray);
		$tpl->setVariable("elements", $elements);
		$tpl->setVariable("counts", $countArray);
		$tpl->setVariable("percents", $percArray);
	} else {
		if ($dataMap["save_results"]->DataInt == 0) { //info is coming from sessin
		//If save results not set we need to get these from the session variable.
			$passed = $http->sessionVariable( 'passed['.$examID.']');
			$score = $http->sessionVariable( 'score['.$examID.']');
			$tpl->setVariable("fromSession", true);
			$tpl->setVariable("resultArray", $http->sessionVariable( 'result_array['.$examID.']' ));
			$dataMap["show_statistics"] = false;
			if ($dataMap["show_correct"]->DataInt) {
				$badArray = array();
				$examArray = $http->sessionVariable( 'exam_array['.$examID.']');
				foreach( $examArray as $examAnswer ) {
					//We need these even if we don't save results
					$elementObject = examElement::fetch( $examAnswer[0] );
					if ( $elementObject->type == "question" ) {
						foreach($elementObject->getAnswers() as $answerObject) {
							if($answerObject->correct == true AND $examAnswer[1] != $answerObject->ID ) {
								$badArray[$examAnswer[0]] = array( $examAnswer[1], $answerObject->ID );
							}
						}
					}
				}
			}
			$tpl->setVariable("incorrect", $badArray);
			if ( $status == "RETEST" ) { //IS THIS EVEN kNOWABLE SINCE THE SESSION IS CLEARED AT THE END OF RESULTS?
				$followup = true;
				$tpl->setVariable("followup", $followup);
			}
		}else{
			$results = examResult::fetchByHash( $hash, $examID );
			//Fetch by hash is getting all the results for a followup and for the previous.  So we have to loop through to only get one or the other
			if ($results)
				$savedvalue = $results[0]->attribute( 'followup' );
	//This is where the followup thing is done
			$correctCount=0;
			$resultIndex=0;
			$followup=false;
			$score=0;
			$passed=false;

			foreach( $results as $result ) {
				if ( $result->attribute( 'followup') != $savedvalue ) {
					$followup = true;
					continue; //so that we only display the followup if it is one.
				}
				//This is where the score is calculated
				$questionObject =  examElement::fetch( $result->questionID );
				//ResultArray = array( "id of chosen answer", questionObject );
				$resultArray[] = array( $result,   $questionObject );
				$optionArray = $questionObject->options;
				If( is_array($optionArray)) {
					if ( array_key_exists("weight", $optionArray ) ) {
						$weight = $optionArray["weight"];
						if ( $weight == 0 ) $weight = 1;
					} else {
						$weight = 1;
					}
				} else {
					$weight = 1;
				}

				if ( $result->attribute( 'correct' ) ) $correctCount = $correctCount + $weight;
				$resultIndex = $resultIndex + $weight;
			}

			if ($resultIndex != 0) {//no division by zero here - dammit.
				$score = ceil( $correctCount / $resultIndex * 100 );
				if ( $score >= $dataMap["pass_threshold"]->DataInt ) {
					$passed = true;
				} else {
					$passed = false;
				}
			}
		}
		$retest = $http->sessionVariable( 'status['.$examID.']' );

		exam::removeSession( $http, $examID );

		//If we failed check if there is an object relation that is of the exam class - if so, use that as the retest node.

		$originalExamObjectID = $examID;
		if ( $passed == false AND $dataMap["retest"]->DataInt == true) { //otherwise we don't care

			$relatedObjects = eZContentFunctionCollection::fetchRelatedObjects( $examID, false, array( 'xml_embed', 'xml_link', 'common' ), false, false );
			foreach( $relatedObjects['result'] as $relatedObject ) {
				if ( $relatedObject->attribute( 'class_identifier' ) == "exam" ) {
					$retestObjectID = $relatedObject->attribute( 'id' );
					break;
				}
			}

			if ( $retest != "FOLLOWUP" ) { //Only one retest now
				$http->setSessionVariable( 'status['.$retestObjectID .']' ,"RETEST" );
				$http->setSessionVariable( 'hash['.$retestObjectID .']' , $hash );
			}
		} elseif( $passed != true AND $dataMap["retest"]->DataInt == true) {
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
		}
		//We're getting the status from a session variable the first time through, but the second time through it won't be around..
		if ($savedvalue ) {
			$followup = true;
			$relatedObjects = eZContentFunctionCollection::fetchReverseRelatedObjects( $examID, false, array( 'common' ), false );
			foreach( $relatedObjects['result'] as $relatedObject ) {
				if ( $relatedObject->attribute( 'class_identifier' ) == "exam" ) {
					$originalExamObjectID = $relatedObject->attribute( 'id' );
					break;
				}
			}
		}

		$tpl->setVariable("followup", $followup);

		$tpl->setVariable("passed", $passed);
		$tpl->setVariable("score", $score);

		if ($dataMap["show_correct"]->DataInt) {
			$tpl->setVariable("showCorrect", true);
			$tpl->setVariable("elements", $elements);

		}
		if ($dataMap["show_statistics"]) {
			$exam = exam::fetch( $originalExamObjectID );
			if ($exam) {
				$tpl->setVariable("average", $exam->average());
				$tpl->setVariable("showStatistics", true);
				$tpl->setVariable("examCount", $exam->attribute( 'count' ));
				$tpl->setVariable("passFirst", $exam->attribute( 'pass_first' ));
				$tpl->setVariable("passSecond", $exam->attribute( 'pass_second' ));
				$tpl->setVariable("highScore", $exam->attribute( 'high_score' ));
			}

		}
	} //end show survey

	$tpl->setVariable("resultArray", $resultArray);
	$tpl->setVariable("results", $results);
	$tpl->setVariable("retest",$dataMap["retest"]->DataInt);
	$tpl->setVariable("certificate",$dataMap["certificate"]->DataInt);
	$tpl->setVariable("survey", $survey);
	$tpl->setVariable("hash",$hash);
	$tpl->setVariable("examID",$examID);
	$nodeID = eZContentObjectTreeNode::findMainNode( $retestObjectID );
	$node =  eZContentObjectTreeNode::fetchByContentObjectID( $retestObjectID );
	$tpl->setVariable("nodeID",$nodeID);
	$tpl->setVariable("node",$node[0]);
	$mode = $dataMap["mode"]->DataText ? $dataMap["mode"]->DataText : "default";

	$Result['content'] = $tpl->fetch( 'design:examen/results/'.$mode.'/result.tpl' );
	$Result['path'] = array(	array(	'url' => false,
								'text' => ezpI18n::tr( 'design/exam', 'Exam' ) ),
						array(	'url' => false,
								'text' =>  $contentObject->attribute( 'name' ) ) );
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
