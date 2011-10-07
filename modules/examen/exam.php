<?php
$Module = $Params['Module'];
$settingsINI = eZINI::instance( 'examen.ini' );
$secretKey = $settingsINI->variable('examSettings','secretKey');
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
//$http->setSessionVariable( 'exam_id', $respondent_id );
/*should have some sort of cookie check to not give them the exam if cookies are turned off... if what? if retest is set to no?  If simple mode?*/
if ( $http->hasPostVariable( "exam_id" ) ) {
	$examID = $http->variable( "exam_id" );
}
/*list($handler, $data) = eZTemplateCacheBlock::retrieve( array( 'begrotingdata', $jaar, $actie ), null, 0 );
if ( !$data instanceof eZClusterFileFailure )
{
	$Result['content']=$data;
} else {
*/
$exam = new exam( $examID );
$contentObject = eZContentObject::fetch( $examID );
/*if either of these is not an object something went bad wrong*/
if (is_object($exam) AND is_object($contentObject)) {
	$dataMap = $contentObject->DataMap();
	$saveResults = $dataMap["save_results"];

	$http->setSessionVariable( 'exam_id', $examID ); //uh-oh this works different now.
	$survey = false;
	$passed = false;
	$correctCount = 0;
	$questions = $exam->getQuestions();
	foreach($questions as $index => $elementObject) {
		$element_id = $elementObject->ID;
		if ( $http->hasPostVariable( "answer_".$element_id ) ) {
			$result = $http->variable( "answer_".$element_id );
			$correct = false;
			foreach($elementObject->getAnswers() as $answer ) {
				if ( $answer->ID == $result ) {
					if ($answer->correct == true ) {
						$correct = true;
						$correctCount=$correctCount++;
						break;
					}
				}
			}
			/*save results */
			//if save is set in the content object attribute
			$session = $http->getSessionKey() ? $http->getSessionKey() : md5sum(date(now));
			$hash = md5($session.$secretKey.$examID);
			
			$followup = false;
			
			$exists = examResult::fetchByHash( $hash, $element_id );
			if (count($exists) > 1 ) {
				$errors[] = "Test threshold exceeded. Results ignored.";
				continue;
			}elseif (count($exists) == 1 AND $dataMap["retest"]->DataInt ) { //followup is not allowed.
				$errors[] = "Retest is not allowed. Results ignored.";
				continue;
			}elseif (count($exists) == 1 AND $dataMap["retest"]->DataInt == 1 ) {
				$followup = true;
			}
			if ( $saveResults ) {
				$newResult = new examResult();
				$newResult->setAttribute( 'contentobject_id', $examID );
				$newResult->setAttribute( 'hash', $hash );
				$newResult->setAttribute( 'question_id', $element_id );
				$newResult->setAttribute( 'answer', $result );
				$newResult->setAttribute( 'correct', $correct );
				$newResult->setAttribute( 'followup', $followup );
				$newResult->store();
			}
		} //end if have hasPostVariable answer_
	} //end foreach question
	/*let's see if they passed*/
	if ($dataMap["pass_threshold"]->DataInt) { //otherwise it's a survey
		if ($correctCount != 0) {//no division by zero here - dammit.
			$score = ceil( ( $index + 1 ) / $correctCount );
			if ( $score >= $dataMap["pass_threshold"]->DataInt ) {
				$passed = true;
			} else {
				$passed = false;
			}
		}
	} else {
		$survey = true;
	}
/*The logic isn't right here. The saveresults above are based on the question depending on the order of a followup question it'll only match on the last one*/
	if ($saveResults) { //Save statistics to the exam object
		$totalExam = $exam->increment( 'count' );
		if (!$survey) {
			if ($followup) {
				$secondPass = $exam->increment( 'pass_second' );
			}else{
				$firstPass = $exam->increment( 'pass_first' );
			}
			$highScore = $exam->highScore( $score );
		}
	}
} else {
/*exam not found*/
	$errors[] = "Exam not found.";
}

$Result = array();
$tpl->setVariable("errors", $errors);
$tpl->setVariable("followup", $followup);
$tpl->setVariable("survey", $survey);
$tpl->setVariable("passed", $passed);

if ($dataMap["show_correct"]->DataInt) {
	$tpl->setVariable("showCorrect", true);
	$tpl->setVariable("questions", $questions);
}
if ($dataMap["show_statistics"]) {
	$tpl->setVariable("showStatistics", true);
	$tpl->setVariable("totalExam", $totalExam);
	$tpl->setVariable("firstPass", $firstPass);
	$tpl->setVariable("secondPass", $secondPass);
	$tpl->setVariable("highScore", $highScore);
	$tpl->setVariable("retest",$dataMap["retest"]->DataInt);
}

$tpl->setVariable("certificate",$dataMap["certificate"]->DataInt);

$mode = $dataMap["mode"]->DataText ? $dataMap["mode"]->DataText : "default";
$mode = "default";
$Result['content'] = $tpl->fetch( 'design:examen/results/'.$mode.'/result.tpl' );


?>
