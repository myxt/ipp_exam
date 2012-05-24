{def $cols=88}
<div class="group">
	<label>{"group"|i18n('design/exam')|upcase} {$element.id}</label>
	<input class="button" value="{'Remove'|i18n('design/exam')}" name="CustomActionButton[remove][{$element.id}]" type="submit">
	<input type="hidden" value="0" name="random_{$element.id}" /> {*Have to prime it otherwise never unchecked*}
	{'Random'|i18n('design/exam')}: <input type="checkbox" id="random" name="random_{$element.id}"{if eq($element.options.random,1)} checked{/if}  />
	<div class="listbutton">
		<input type="image" src="/design/admin2/images/button-move_down.gif" alt="Down" name="MoveDown_{$element.id}" title="{'Use these buttons to move elements up or down'|i18n('design/exam')}" />&nbsp;
		<input type="image" src="/design/admin2/images/button-move_up.gif" alt="Up" name="MoveUp_{$element.id}" title="{'Use these buttons to move elements up or down'|i18n('design/exam')}" />
		<input size="2" maxlength="4" type="text" name="element_priority_{$element.id}" value="{$element.priority}" />
	</div>

	{default editorRow=2}
	<div class="oe-window">
		<textarea class="box" id="exam_group_data_text_{$element.id}" name="exam_group_data_text_{$element.id}" cols="{$cols}" rows="{$editorRow}">{$element.input_xml}</textarea>
	</div>
	<script type="text/javascript">
	<!--
	eZOeAttributeSettings = eZOeGlobalSettings;
	eZOeAttributeSettings['ez_attribute_id'] = {$element.id};
	eZOeToggleEditor( 'exam_group_data_text_{$element.id}', eZOeAttributeSettings );
	-->
	</script>

	{/default}
	<div class="children">

		{foreach $element.children as $child}
			{if eq($child.type,"question")} {*need elements for condition choices*}
				{exam_edit_gui element=$child elements=$element.children}
			{else}
				{exam_edit_gui element=$child}
			{/if}
		{/foreach}
	</div>
	<br clear="all" />
	<input class="button" id="newQuestion" name="CustomActionButton[newQuestion][{$element.id}]" value="{'New Question'|i18n('design/exam')}" type="Submit">
	<input class="button" id="newText" name="CustomActionButton[newText][{$element.id}]" value="{'New Text'|i18n('design/exam')}" type="Submit">
	<input class="button" id="newBreak" name="CustomActionButton[newBreak][{$element.id}]" value="{'New Pagebreak'|i18n('design/exam')}" type="Submit">
	<input class="button" id="updatePriorities" name="CustomActionButton[updatePriorities][{$element.id}]" value="{'Update Priorities'|i18n('design/exam')}" type="Submit">
</div>
