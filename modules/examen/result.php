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
		$results = examResult::fetchSurvey( $examID );
		//A survey could still have multiple questions.
		//This is returning the results for all versions and all languages.  This isn't always necessarily correct.
		//Let's make sure to only display results for question IDs that are valid.  It means if someone edits the object the results will be off but there isn't any way to do this.
		$exam = exam::fetch( $examID );
		$examVersion = $contentObject->CurrentVersion;
		$examLanguage = $contentObject->CurrentLanguage;
		$examQuestions = $exam->getQuestions( $examVersion, $examLanguage );
		$questionArray=array();
		foreach ( $examQuestions as $question ) {
			$questionArray[] = $question->ID;
		}

		$total=count($results);
		$countArray = array();
		foreach( $results as $result ) {
			if ( in_array( $result->attribute('question_id'), $questionArray ) ) {
				if(!isset($countArray[$result->attribute('question_id')][$result->attribute('answer')])) {
					$countArray[$result->attribute('question_id')][$result->attribute('answer')] = 1;
				} else {
					$countArray[$result->attribute('question_id')][$result->attribute('answer')] = $countArray[$result->attribute('question_id')][$result->attribute('answer')] + 1;
				}
			} else {
				$total = $total - 1;
			}
		}

		$percArray = array();
		$totalArray = array();

		foreach($countArray as $question_id => $answerArray ) {
			$elements[] =  examElement::fetch( $question_id );
			$totalArray[$question_id] = array_sum( $countArray[$question_id] );
			foreach($answerArray as $index => $answer ) {
				
				$percArray[$index] = round( $answer / $total * 100 );
			}
		}
		$tpl->setVariable("totals", $totalArray);
		$tpl->setVariable("elements", $elements);
		$tpl->setVariable("counts", $countArray);
		$tpl->setVariable("percents", $percArray);
	} else {
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
			if ( array_key_exists("weight", $optionArray ) ) {
				$weight = $optionArray["weight"];
				if ( $weight == 0 ) $weight = 1;
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
		$retest = $http->sessionVariable( 'status['.$examID.']' );

		exam::removeSession( $http, $examID );
		//If we failed check if there is an object relation that is of the exam class - if so, use that as the retest node.

		$originalExamObjectID = $examID;
		if ( $passed == false AND $dataMap["retest"]->DataInt == true) { //otherwise we don't care

			$relatedObjects = eZContentFunctionCollection::fetchRelatedObjects( $examID, false, array( 'xml_embed', 'xml_link', 'common' ), false );
			foreach( $relatedObjects['result'] as $relatedObject ) {
				if ( $relatedObject->attribute( 'class_identifier' ) == "exam" ) {
					$retestObjectID = $relatedObject->attribute( 'id' );
					break;
				}
			}
;
			if ( $retest != "FOLLOWUP" ) { //Only one retest now
				$http->setSessionVariable( 'status['.$retestObjectID .']' ,"RETEST" );
				$http->setSessionVariable( 'hash['.$retestObjectID .']' , $hash );
			}
		} elseif( $passed = true AND $dataMap["retest"]->DataInt == true) { 
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
			$tpl->setVariable("retest",$dataMap["retest"]->DataInt);
			$tpl->setVariable("certificate",$dataMap["certificate"]->DataInt);
			$tpl->setVariable("resultArray", $resultArray);
			$tpl->setVariable("results", $results);
		}
	} //end show survey
	$tpl->setVariable("survey", $survey);
	$tpl->setVariable("hash",$hash);
	$tpl->setVariable("examID",$examID);
	$nodeID = eZContentObjectTreeNode::findMainNode( $retestObjectID );
	$node =  eZContentObjectTreeNode::fetchByContentObjectID( $retestObjectID );
	$tpl->setVariable("nodeID",$nodeID);
	$tpl->setVariable("node",$node[0]);
	$mode = $dataMap["mode"]->DataText ? $dataMap["mode"]->DataText : "default";
	$mode = "default";
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
