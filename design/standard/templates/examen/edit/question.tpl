<br>WE ARE IN THE QUESTION EDIT GUI<br>
<div class="question" style="border:1px dashed yellow;">
	<label>QUESTION {$element.id}</label>
	<div class="question element" style="border:1px dashed green;">

		<div class="listbutton">
			<input class="button" value="Remove" name="CustomActionButton[remove][{$element.id}]" type="submit">
			Random: <input type="checkbox" id="random" name="Random" />
			<input type="image" src="/design/admin2/images/button-move_down.gif" alt="Down" name="MoveDown_{$element_type}_{$element.id}" title="" />&nbsp;
			<input type="image" src="/design/admin2/images/button-move_up.gif" alt="Up" name="MoveUp_{$element_type}_{$element.id}" title="" />
			<input size="2" maxlength="4" type="text" name="element_priority_{$element.id}" value="{$element.priority}" />
		</div>

		{default	element_base=exam_question
				editorRow=2}
		<div class="oe-window">
			<textarea class="box" id="{$element_base}_data_text_{$element.id}" name="{$element_base}_data_text_{$element.id}" cols="88" rows="{$editorRow}">{$element.content}</textarea>
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

		{foreach $element.answers as $answer}
			<div class="listbutton">
				{$answer.type} {$answer_id}: 
				<input class="button" value="Remove" name="CustomActionButton[removeAnswer][{$answer.id}]" type="submit">
				Correct: <input type="checkbox" name="correct_{$answer.id}" />
				Condition:  <input type="text" id="condition_{$answer.id}" name="Condition" />
				<input type="image" src="/design/admin2/images/button-move_down.gif" alt="Down" name="MoveDown_{$answer.type}_{$answer.id}" title="" />&nbsp;
				<input type="image" src="/design/admin2/images/button-move_up.gif" alt="Up" name="MoveUp_{$answer.type}_{$answer.id}" title="" />
				<input size="2" maxlength="4" type="text" name="answer_priority_{$answer.id}" value="{$answer.priority}" />
			</div>

			{default answer_base='exam_answer'
					html_class='full'}
				<textarea id="ezcoa-{if ne( $answer_base, 'ContentObjectAttribute' )}{$answer_base}-{/if}{$answer.contentclassanswer_id}_{$answer.contentclass_answer_identifier}" class="{eq( $html_class, 'half' )|choose( 'box', 'halfbox' )} ezcc-{$answer.object.content_class.identifier} ezcca-{$answer.object.content_class.identifier}_{$answer.contentclass_answer_identifier}" name="{$answer.type}_data_text_{$answer.id}" cols="70" rows="1">{$answer.content}</textarea>
			{/default}
		{/foreach}

		<input class="button" id="newAnswer" name="CustomActionButton[newAnswer][{$element.id}]" value="Add Answer" type="Submit">
	</div>
<br clear="all" />
</div>