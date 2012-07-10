<?php
/**
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */

function replace( $string = "", $title = "", $score = "", $passed = "", $link = "" ) {
	$searchArray =  array(
					'[[name]]',
					'[[naam]]',
					'[[score]]',
					'[[punten]]',
					'[[passed]]',
					'[[geslaagd]]',
					'[[link]]'
				);
	$replaceArray =  array(
					$title,
					$title,
					$score,
					$score,
					$passed,
					$passed,
					$link
				);

	return str_replace( $searchArray, $replaceArray, $string );
}

$Module = $Params['Module'];
$settingsINI = eZINI::instance( 'examen.ini' );
$http = eZHTTPTool::instance();
$tpl = eZTemplate::factory();
$Result = array();
$errors = array();

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
	$dataMap = $contentObject->DataMap();
	$mode = $dataMap["mode"]->DataText ? $dataMap["mode"]->DataText : "default";
	$survey = false;
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
		$tpl->setVariable( 'survey', $survey );
	}
	if (!$survey) {
		$results = examResult::fetchByHash( $hash, $examID );
		if ($results)
			$savedvalue = $results[0]->attribute( 'followup' );

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
				$passed = ezpI18n::tr( 'design/exam',"passed" );
			} else {
				$passed = ezpI18n::tr( 'design/exam',"failed" );
			}
		}

		//$tpl->setVariable( 'passed', $passed);
		//$tpl->setVariable( 'score', $score );
	}

	/* To generate bitly urls - leaving it here just in case but if we're doing something like this it'll take more setup than what I should be doing in this extension

	//these should be ini settings
	$bitlyLogin = $settingsINI->variable( 'bitlySettings', 'BitlyLogin' );
	$bitlyAPIKey = $settingsINI->variable( 'bitlySettings', 'BitlyKey' );

	$APIcall = file_get_contents("http://api.bit.ly/shorten?version=2.0.1&longUrl=".$FullURL."&login=".$bitlyLogin."&apiKey=".$bitlyAPIKey);
	$bitlyInfo = json_decode( utf8_encode( $APIcall ),true );
	if ( $bitlyInfo['errorCode']==0 )
	{
		$bitlyLink = $bitlyInfo['results'][urldecode($FullURL)]['shortUrl'];
	}

	*/
	$originalExamObjectID = $examID;
	$relatedObjects = eZContentFunctionCollection::fetchReverseRelatedObjects( $examID, false, array( 'common' ), false );
	foreach( $relatedObjects['result'] as $relatedObject ) {
		if ( $relatedObject->attribute( 'class_identifier' ) == "exam" ) {
			$originalExamObjectID = $relatedObject->attribute( 'id' );
			break;
		}
	}
}
if ( count($errors) == 0 ) {
	/*This is not the way to do it.  We don't want the MainNode we want the url_alias of the siteaccess you are in.*/
	$nodeID = eZContentObjectTreeNode::findMainNode( $originalExamObjectID );
	$node =  eZContentObjectTreeNode::fetchByContentObjectID( $originalExamObjectID, true );
	$nodeDataMap = $node[0]->DataMap();

	$ini = eZINI::instance();
	$link = $ini->variable( 'SiteSettings', 'SiteURL' )."/". $node[0]->attribute( 'path_identification_string' );
	$tpl->setVariable( 'link', $link );

	//Post to Twitter
	if( $http->hasPostVariable( "TwitterButton" )  )
	{
		//$twitter = $tpl->fetch( 'design:examen/results/'.$mode.'/twitter.tpl' );
		$name=$node[0]->attribute( 'name' );;
		$twitter = $nodeDataMap['twitter_text']->content();

		$twitter = replace( $twitter, $name, $score, $passed, $link );
		$twitter = strip_tags( $twitter );
		$twitter = urlencode( $twitter );
		$twitter = str_replace("%7C","=",$twitter);
		$twitter = "http://twitter.com/share?text=".$twitter;
		return $Module->redirectTo( $twitter );
	}

	//Post to Hyves
	if( $http->hasPostVariable( "HyvesButton" )  )
	{
/*title|{'I'|i18n('design/exam')} {if $passed}{'passed'|i18n('design/exam')}{else}{'failed'|i18n('design/exam')}{/if}^body|{'I took the exam at'|i18n('design/exam')} {$link} {'and'|i18n('design/exam')} {if $passed}{'passed'|i18n('design/exam')}{else}{'failed'|i18n('design/exam')}{/if} {'with a score of'|i18n('design/exam')} {$score}.<br><br>[url|{$link}]Do you want to try it too?[/url]^category|12^type|9
*/
		//$hyves = $tpl->fetch( 'design:examen/results/'.$mode.'/hyves.tpl' );
		$name=$node[0]->attribute('name');
		$title = $nodeDataMap['hyves_title']->content();
		$body = $nodeDataMap['hyves_text']->content();
		$link = "[url|".$link."]".ezpI18n::tr( 'design/exam',"link")."[/url]";
		$hyves = "title|".replace( $title, $name, $score, $passed, $link )."^body|".replace( $body, $name, $score, $passed, $link )."^category|12^type|9";
		$hyves = strip_tags( $hyves );
		$hyves = urlencode( trim($hyves) );
		$hyves = str_replace("%7C","=",$hyves);
		$hyves = str_replace("%5E","&",$hyves);
		$hyves = "http://www.hyves.nl/hyvesconnect/smartbutton?".$hyves;
		return $Module->redirectTo( $hyves );
	}

	//Post to Facebook
	if( $http->hasPostVariable( "FacebookButton" )  )
	{
	/*This is depreciated for the like button.  Facebook wants to track you even if you aren't logged in and they couldn't do it with the share button I guess.  Looks like someone has to set up a facebook application to be able to fill in a comment - way more complicated than I should be getting into here.
	http://hitech-tips.blogspot.com/2010/05/facebook-like-button-xfbml-tutorial.html

		$tpl->setVariable( 'link', $link );
		$facebook = $tpl->fetch( 'design:examen/results/'.$mode.'/facebook.tpl' );
		$facebook = strip_tags( $facebook );
		$facebook = urlencode( trim($facebook) );
		$facebook = str_replace("%7C","=",$facebook);
		$facebook = str_replace("%5E","&",$facebook);
	*/
	/*link has to be something that facebook can actually get to, then it will have to get the title, body and image from meta tags - the problem is, that this has to come from the exam page and not the results page, so we can't actually load any results... only a comment of sorts.  For testing, this has to be coming from a page that facebook can get to - and they cache the link, so if you test it twice it WILL NOT pick up changes.  This is what needs to be loaded:
	<meta name="title" content="Smith hails 'unique' Wable legacy">
	<meta name="description" content="John Smith claims beautiful football ..." />
	<link rel="image_src" href="http://www.onjd.com/design05/images/PH2/WableAFC205.jpg" />
	it doesn't appear to have to be in the <head>
	*/

		$facebook = "http://www.facebook.com/sharer.php?u=".$link; //."&t=".replace($text, $title, $score, $passed, $link ); t is ignored now
		return $Module->redirectTo( $facebook );
	}
	$errors[] = "broken_send_submit";
}
$tpl->setVariable("errors", $errors);
$Result['content'] = $tpl->fetch( 'design:examen/view/error.tpl' );
$Result['path'] = array(	array(	'url' => false,
							'text' => ezpI18n::tr( 'design/exam', 'Exam' ) ),
					array(	'url' => false,
							'text' => ezpI18n::tr( 'design/exam', 'Error' ) ) );
?>
