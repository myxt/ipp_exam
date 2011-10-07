<br>WE ARE IN THE GROUP EDIT GUI<br>
<div class="listbutton">
	<label>{$element.type|upcase} {$element.id}</label>
	<input class="button" value="Remove" name="CustomActionButton[remove][{$element.id}]" type="submit">
	Random:<input type="checkbox" id="random" name="random_{$element.id}" />
	<input type="image" src="/design/admin2/images/button-move_down.gif" alt="Down" name="MoveDown_{$element.id}" title="" />&nbsp;
	<input type="image" src="/design/admin2/images/button-move_up.gif" alt="Up" name="MoveUp_{$element.id}" title="" />
	<input size="2" maxlength="4" type="text" name="element_priority_{$element.id}" value="{$element.priority}" />
</div>

<div class="group" style="border:1px dashed red;">
{default editorRow=10}
    <div class="oe-window">
        <textarea class="box" id="exam_group_data_text_{$element.id}" name="exam_group_data_text_{$element.id}" cols="70" rows="{$editorRow}">{$element.content}</textarea>
    </div>

    <div class="block">
        {if $input_handler.can_disable}
            <input class="button{if $layout_settings['buttons']|contains('disable')} hide{/if}" type="submit" name="CustomActionButton[{$element.id}_disable_editor]" value="{'Disable editor'|i18n('design/standard/content/datatype')}" />
        {/if}
        <script type="text/javascript">
        <!--

        eZOeAttributeSettings = eZOeGlobalSettings;
        eZOeAttributeSettings['ez_attribute_id'] = {$element.id};


        eZOeToggleEditor( 'exam_group_data_text_{$element.id}', eZOeAttributeSettings );

        -->
        </script>
    </div>

{/default}

{foreach $element.children as $child}
	<div class="element" style="border:1px dashed green;">
	{if eq($element.type,"question")} {*need elements for condition choices*}
		{exam_edit_gui element=$child structure=$structure}
	{else}
		{exam_edit_gui element=$child}
	{/if}
		
	</div>
{/foreach}
<br clear="all" />
<input class="button" id="newQuestion" name="CustomActionButton[newQuestion][{$element.id}]" value="New Question" type="Submit">
<input class="button" id="newText" name="CustomActionButton[newText][{$element.id}]" value="New Text" type="Submit">
<input class="button" id="newBreak" name="CustomActionButton[newBreak][{$element.id}]" value="New Pagebreak" type="Submit">
<input class="button" id="updatePriorities" name="CustomActionButton[updatePriorities][{$element.id}]" value="Update Priorities" type="Submit">

</div>