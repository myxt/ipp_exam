<div id="exam-edit">
	<div class="buttons">
		<input class="button" id="newGroup" name="CustomActionButton[newGroup]" value="New Group" type="Submit">
		<input class="button" id="newQuestion" name="CustomActionButton[newQuestion]" value="New Question" type="Submit">
		<input class="button" id="newText" name="CustomActionButton[newText]" value="New Text" type="Submit">
		<input class="button" id="newBreak" name="CustomActionButton[newBreak]" value="New Pagebreak" type="Submit">
		<input class="button" id="updatePriorities" name="CustomActionButton[updatePriorities]" value="Update Priorities" type="Submit">
	</div>
	{def $elements=fetch('examen', 'elements', hash( 'id', $attribute.contentobject_id, 'version', $attribute.version, 'language_code', $attribute.language_code))
		$structure=fetch('examen', 'structure', hash( 'id', $attribute.contentobject_id, 'version', $attribute.version, 'language_code', $attribute.language_code))
	}
	{foreach $structure as $element}
		{if or(eq($element.type,"question"),eq($element.type,"group"))} {*need elements for condition choices*}
			{exam_edit_gui element=$element elements=$elements}
		{else}
			{exam_edit_gui element=$element}
		{/if}
	{/foreach}
</div>