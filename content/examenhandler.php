<?php

//
class ExamenHandler extends eZContentObjectEditHandler
{
	function ExamHandler()
	{
	}

	static function storeActionList()
	{
		return array( "AnswerMoveUp","AnswerMoveDown","MoveUp","MoveDown" );
	}

	function publish( $contentObjectID, $contentObjectVersion )
	{
	}

}

?>