<?php
/**
 * File containing the ExamenType class.
 *
 * @copyright Copyright (C) 2011 Leiden Tech/Myxt Web Solutions All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version  1
 * @package examen
 */
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