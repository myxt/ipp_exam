<?php

/* This can be reached in several ways:
 
we could be coming from just having taken the test and we have valid session variables - this will be the case if results are not saved to the database... or 

we can be coming months later to the exam/hash result that was displayed on the results page the first time we came through.

*/

//eZFire::debug("IN RESULT.PHP");
$Module = $Params['Module'];
$settingsINI = eZINI::instance( 'examen.ini' );
$secretKey = $settingsINI->variable('examSettings','secretKey');
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$Result = array();
$errors = array();

$examID = $Params['exam_id'];

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
//If it's a survey, we don't need a hash.  And we need ALL results not just the ones that match to the hash - so we have to figure out if this is a survey first.
$dataMap = $contentObject->DataMap();

if ($dataMap["pass_threshold"]->DataInt) { //otherwise it's a survey and we don't care
	$hash = $Params['hash'];
	if (!$hash) {  //no exam_id, we got nothing then
		$errors[] = "no_hash";
	}
} else {
	$survey=true;
}

if ( count($errors) == 0 ) {
/*We could cache by examid/hash - or lack of hash
/*list($handler, $data) = eZTemplateCacheBlock::retrieve( array( 'examdata',$http->sessionID() , $examId ), null, 0 );
if ( !$data instanceof eZClusterFileFailure )
{
	$Result['content']=$data;
} else {
*/



	/*These are the object options that determine results output
	//	These happen in exam...
	//	eZFire::debug($dataMap["mode"]->DataText,"mode");
	//	eZFire::debug($dataMap["retest"]->DataInt,"retest");
	//	eZFire::debug($dataMap["save_results"]->DataInt,"save results");
	//	These happen here...
	//	eZFire::debug($dataMap["show_correct"]->DataInt,"show correct");
	//	eZFire::debug($dataMap["certificate"]->DataInt,"certificate");
	//	eZFire::debug($dataMap["show_statistics"]->DataInt,"show statistics");
	//	eZFire::debug($dataMap["pass_threshold"]->DataInt,"pass threshold");
	*/

	if ($dataMap["pass_threshold"]->DataInt) { //otherwise it's a survey and we don't care
		if ($correctCount != 0) {//no division by zero here - dammit.
			$score = 100 - ceil( ( $resultIndex - $correctCount ) / $resultIndex * 100 );
//eZFire::debug($dataMap["pass_threshold"]->DataInt,"Threshold");
			if ( $score >= $dataMap["pass_threshold"]->DataInt ) {
				$passed = true;
			} else {
				$passed = false;
			}
		}
	}
	if ( $survey ) {
	// if it's a survey we're going to have to return an array of count( total => x, answer1 => x, answer2 => x );
	// could maybe do the calculation here for the 
		$results = examResult::fetchSurvey( $examID );
		//A survey could still have multiple questions.
		$total=count($results);
		foreach( $results as $result ) {
			if(!$countArray[$result->attribute('question_id')][$result->attribute('answer')]) {
				$countArray[$result->attribute('question_id')][$result->attribute('answer')] = 1;
			} else {
				$countArray[$result->attribute('question_id')][$result->attribute('answer')] = $countArray[$result->attribute('question_id')][$result->attribute('answer')] + 1;
			}
		}
		$percArray = array();
		foreach($countArray as $question_id => $answerArray ) {
//eZFire::debug($question_id,"QUESTION ID");
;$elements[] =  examElement::fetch( $question_id );
			foreach($answerArray as $index => $answer ) {

//eZFire::debug($answer,"ANSWER");
//eZFire::debug($index,"INDEX");
//eZFire::debug(ceil( $answer / $total * 100 ),"PERCENTAGE");
				$percArray[$index] = round( $answer / $total * 100 );
			}
		}
//eZFire::debug($total,"TOTAL");
//eZFire::debug($countArray,"COUNT ARRAY");
//eZFire::debug($percArray,"PERCENT ARRAY");
//eZFire::debug($elements,"ELEMENTS");

		$tpl->setVariable("elements", $elements);
		$tpl->setVariable("counts", $countArray);
		$tpl->setVariable("percents", $percArray);
	} else {
//eZFire::debug("Better not be a survey");
		$results = examResult::fetchByHash( $hash, $examID );
//eZFire::debug( count($elements) , "ELEMENT COUNT" );
//eZFire::debug( $results,"RESULTS");
		//Fetch by hash is getting all the results for a followup and for the previous.  So we have to loop through to only get one or the other
		if ($results)
			$savedvalue = $results[0]->attribute( 'followup' );
//eZFire::debug( $savedvalue ,"SAVED VALUE"); 

		foreach( $results as $result ) {
	//eZFire::debug( $result, "RESULT" );
			if ( $result->attribute( 'followup') != $savedvalue ) {
	//eZFire::debug( "BAILING OUT BECAUSE IT'S THE FIRST PASS OF A FOLLOWUP"); 
				$followup = true;
				break; //so that we only display the followup if it is one.
			}
	//eZFire::debug($result->questionID,"QUESTION ID");
	//ResultArray( "what you answered", elementObject );
			$resultArray[] = array( $result,  examElement::fetch( $result->questionID ));
			if ( $result->attribute( 'correct' ) ) $correctCount++;
			$resultIndex++;
		}

	
	//eZFire::debug($passed,"PASSED");
	//eZFire::debug($followup,"FOLLOWUP");
		$tpl->setVariable("followup", $followup);
		
		$tpl->setVariable("passed", $passed);
		$tpl->setVariable("score", $score);

		if ($dataMap["show_correct"]->DataInt) {
	//eZFire::debug($dataMap["show_correct"]->DataInt,"SHOW CORRECT");
			$tpl->setVariable("showCorrect", true);
			$tpl->setVariable("elements", $elements);

//eZFire::debug($resultArray,"RESULT ARRAY");
		}
		if ($dataMap["show_statistics"]) {
			$exam = exam::fetch( $examID );
	//eZFire::debug($exam,"EXAM");
	//eZFire::debug($dataMap["show_statistics"]->DataInt,"SHOW STATS");
	//eZFire::debug($exam->attribute( 'count' ),"COUNT");
	//eZFire::debug($exam->attribute( 'pass_first' ),"pass_first");
	//eZFire::debug($exam->attribute( 'pass_second' ),"pass_second");
	//eZFire::debug($exam->attribute( 'high_score' ),"high_score");

			$tpl->setVariable("showStatistics", true);
			$tpl->setVariable("examCount", $exam->attribute( 'count' ));
			$tpl->setVariable("passFirst", $exam->attribute( 'pass_first' ));
			$tpl->setVariable("passSecond", $exam->attribute( 'pass_second' ));
			$tpl->setVariable("highScore", $exam->attribute( 'high_score' ));
			$tpl->setVariable("retest",$dataMap["retest"]->DataInt);
			$tpl->setVariable("certificate",$dataMap["certificate"]->DataInt);
			$tpl->setVariable("resultArray", $resultArray);
			$tpl->setVariable("results", $results);
		}
	} //end show survey
	$tpl->setVariable("survey", $survey);
	$tpl->setVariable("hash",$hash);
	$tpl->setVariable("examID",$examID);

	$mode = $dataMap["mode"]->DataText ? $dataMap["mode"]->DataText : "default";
	$mode = "default";
	$Result['content'] = $tpl->fetch( 'design:examen/results/'.$mode.'/result.tpl' );
}

if (!$Result['content']) { /*Got errors*/
	$tpl->setVariable("errors", $errors);
	$Result['content'] = $tpl->fetch( 'design:examen/view/error.tpl' );
}
?>
