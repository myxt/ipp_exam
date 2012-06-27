<?php
/**
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */

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
		$Result['path'] = array(	array(	'url' => false,
									'text' => ezpI18n::tr( 'design/exam', 'Exam' ) ),
							array(	'url' => false,
									'text' =>  $examID ) );
	} else {
		$object = new exam;
		$exams = $object->fetchExams();
		$tpl->setVariable("exams",$exams);
		$Result['content'] = $tpl->fetch( 'design:examen/admin/overview.tpl' );
		$Result['path'] = array(	array( 'url' => false,
							'text' => ezpI18n::tr( 'design/exam', 'Exam' ) ),
							array(	'url' => false,
									'text' =>  ezpI18n::tr( 'design/exam', 'Overview' ) ) );
	}
} else { //overview page
	$object = new exam;
	$exams = $object->fetchExams();
	$tpl->setVariable("exams",$exams);
	$Result['content'] = $tpl->fetch( 'design:examen/admin/overview.tpl' );
	$Result['path'] = array(	array(	'url' => false,
								'text' => ezpI18n::tr( 'design/exam', 'Exam' ) ),
						array(	'url' => false,
								'text' =>  ezpI18n::tr( 'design/exam', 'Overview' ) ) );

}
?>
