<br>WE ARE IN THE TEXT EDIT GUI<br>
<div class="listbutton">
	<label>{$element.type|upcase} {$element.id}</label>
	<input class="button" value="Remove" name="CustomActionButton[remove][{$element.id}]" type="submit">
	<input type="image" src="/design/admin2/images/button-move_down.gif" alt="Down" name="MoveDown_{$element.id}" title="{'Use these buttons to move elements up or down'|i18n('design/exam')}" />&nbsp;
	<input type="image" src="/design/admin2/images/button-move_up.gif" alt="Up" name="MoveUp_{$element.id}" {'Use these buttons to move elements up or down'|i18n('design/exam')}" />
	<input size="2" maxlength="4" type="text" name="element_priority_{$element.id}" value="{$element.priority}" />
</div>

{default	element_base=exam
		editorRow=2}
<div class="oe-window">
	<textarea class="box" id="{$element_base}_data_text_{$element.id}" name="{$element_base}_data_text_{$element.id}" cols="70" rows="{$editorRow}">{$element.content}</textarea>
</div>

<div class="block">

	<input class="button{if $layout_settings['buttons']|contains('disable')} hide{/if}" type="submit" name="CustomActionButton[{$element.id}_disable_editor]" value="{'Disable editor'|i18n('design/standard/content/datatype')}" />
	
	<script type="text/javascript">
	<!--

	eZOeAttributeSettings = eZOeGlobalSettings;
	eZOeAttributeSettings['ez_element_id'] = {$element.id};


	eZOeToggleEditor( '{$element_base}_data_text_{$element.id}', eZOeAttributeSettings );

	-->
	</script>
</div>
{/default}