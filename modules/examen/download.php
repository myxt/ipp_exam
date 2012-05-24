<?php
/**
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */

//To download a generated (pdf) certificate from the result page
$Module = $Params['Module'];
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$Result = array();
$errors = array();
$examArray = array();

//This should most definitely be cached
$Result = array();


if ( $http->hasPostVariable( "exam_id" ) ) {
	$examID = $http->variable( "exam_id" );
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

if ( !$http->hasPostVariable( "hash" ) ) {
	$errors[] = "no_hash";
}
if ($errors) { /*Got errors*/
	$tpl->setVariable("errors", $errors);
	$Result['content'] = $tpl->fetch( 'design:examen/view/error.tpl' );
} else {
	/*We could cache by examid/hash Hmm and name.
	/*list($handler, $data) = eZTemplateCacheBlock::retrieve( array( 'examdata',$http->sessionID() , $examId ), null, 0 );
	if ( !$data instanceof eZClusterFileFailure )
	{
		$Result['content']=$data;
	} else {
	*/
	$title=$contentObject->attribute( 'name' );
	if ($http->hasSessionVariable( 'name['.$examID.']' )) { //So they can't download for their friends.
		$name = $http->sessionVariable( 'name['.$examID.']' );
	} else {
		if ($http->hasPostVariable('name')) {
			$name = $http->postVariable('name'); //should wash this
		}
		$http->setSessionVariable( 'name['.$examID.']' , $name );
	}
	$firstline	=  ezpI18n::tr('design/exam',"Congratulations");
	$secondline	= escapeshellcmd( $name );
	$thirdline	=  ezpI18n::tr('design/exam',"you passed");
	$fourthline	= $title;

	$Result["pagelayout"] = false;
	$Result['path'] = array();
	set_time_limit( 0 );
	header("Pragma: public"); // required
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false); // required for certain browsers
	header("Content-Disposition: attachment; filename=\"certificate.pdf\";" );
	header("Content-Transfer-Encoding: binary");
	$boundary = md5(time());
	header('Content-Type: multipart/form-data; boundary='.$boundary);

	$source_image = realpath( dirname( __FILE__ ) . "/../../design/standard/images/certificate.png" );
	$command =	'/usr/bin/convert '.$source_image.' -pointsize 62 -fill black ' .
				'-draw "text 80, 120 \''.$firstline.'\'" ' .
				'-draw "text 60, 212 \''.$secondline.'\'" ' .
				'-draw "text 80, 284 \''.$thirdline.'\'" ' .
				'-draw "text 60, 366 \''.$fourthline.'\'" ' .
				'PDF:-';
	ob_clean();
	passthru($command);
	ob_flush();
	eZExecution::cleanExit();
} //end if error

?>
