{*This is getting the structure to loop through the top level items since children will have to be handled on a lower level have to get the elements for questions so that the condition list can be populated.  Group needs it to pass to a child question. *}

{def $elements=fetch('examen', 'elements', hash(	'id', $attribute.contentobject_id,
										'version', $attribute.version,
										'language_code', $attribute.language_code )
				)
	$structure=fetch('examen', 'structure', hash( 	'id', $attribute.contentobject_id,
											'version', $attribute.version,
											'language_code', $attribute.language_code )
				)
}
<div id="exam-edit">
	<div class="buttons">
		<input class="button" id="newGroup" name="CustomActionButton[newGroup]" value="{'New Group'|i18n('design/exam')}" type="Submit">
		<input class="button" id="newQuestion" name="CustomActionButton[newQuestion]" value="{'New Question'|i18n('design/exam')}" type="Submit">
		<input class="button" id="newText" name="CustomActionButton[newText]" value="{'New Text'|i18n('design/exam')}" type="Submit">
		<input class="button" id="newBreak" name="CustomActionButton[newBreak]" value="{'New Pagebreak'|i18n('design/exam')}" type="Submit">
		<input class="button" id="updatePriorities" name="CustomActionButton[updatePriorities]" value="{'Update Priorities'|i18n('design/exam')}" type="Submit">
	</div>

	{foreach $structure as $element}
		{if or(eq($element.type,"question"),eq($element.type,"group"))} {*need elements for condition choices*}
			{exam_edit_gui element=$element elements=$elements}
		{else}
			{exam_edit_gui element=$element}
		{/if}
	{/foreach}
</div>
