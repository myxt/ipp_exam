<?php
/* For the backend statistics module */
/* if we have no parameter, do the overview.  If there is a parameter and it is an exam id - show the full view of the exam.exam_id */

$Module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();

$Result = array();
$examID = $Params["exam_id"];
if( ctype_digit($examID) ) {
	$exam = new exam($examID);
	if (is_object($exam)) {
		$tpl->setVariable("exam",$exam);
		$Result['content'] = $tpl->fetch( 'design:examen/admin/exam.tpl' );
	} else {
		$object = new exam;
		$exams = $object->fetchExams();
		$tpl->setVariable("exams",$exams);
		$Result['content'] = $tpl->fetch( 'design:examen/admin/overview.tpl' );
	}
} else { //overview page
	$object = new exam;
	$exams = $object->fetchExams();
	$tpl->setVariable("exams",$exams);
	$Result['content'] = $tpl->fetch( 'design:examen/admin/overview.tpl' );
}
?>
