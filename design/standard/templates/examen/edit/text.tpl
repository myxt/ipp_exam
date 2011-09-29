<br>WE ARE IN THE TEXT EDIT GUI<br>
<div class="listbutton">
	{$element.type}: 
	<input class="button" value="Remove" name="CustomActionButton[remove][{$element.id}]" type="submit">
	Random: <input type="checkbox" id="random" name="Random" />
	<input type="image" src="/design/admin2/images/button-move_down.gif" alt="Down" name="MoveDown_{$element_type}_{$element.id}" title="" />&nbsp;
	<input type="image" src="/design/admin2/images/button-move_up.gif" alt="Up" name="MoveUp_{$element_type}_{$element.id}" title="" />
	<input size="2" maxlength="4" type="text" name="element_priority_{$element.id}" value="{$element.priority}" />
</div>

{default	element_base=exam_question
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