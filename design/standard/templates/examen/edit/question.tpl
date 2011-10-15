<br>WE ARE IN THE QUESTION EDIT GUI<br>
<div class="question" style="border:1px dashed yellow;">
	<label>{$element.type|upcase} {$element.id}</label>
	<div class="question element" style="border:1px dashed green;">
		<div class="listbutton">
			<input class="button" value="Remove" name="CustomActionButton[remove][{$element.id}]" type="submit">
			<input type="hidden" value="0" name="random_{$element.id}" /> {*Have to prime it otherwise never unchecked*}
			Random: <input type="checkbox" id="random" name="random_{$element.id}"{if eq($element.options.random,1)} checked{/if}  />
			<input type="image" src="/design/admin2/images/button-move_down.gif" alt="Down" name="MoveDown_{$element.id}" title="{'Use these buttons to move elements up or down'|i18n('design/exam')}" />&nbsp;
			<input type="image" src="/design/admin2/images/button-move_up.gif" alt="Up" name="MoveUp_{$element.id}" {'Use these buttons to move elements up or down'|i18n('design/exam')}" />
			<input size="2" maxlength="4" type="text" name="element_priority_{$element.id}" value="{$element.priority}" />
		</div>

		{default	element_base=exam
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
				answer {$answer.id}: 
				<input class="button" value="Remove" name="CustomActionButton[removeAnswer][{$answer.id}]" type="submit">
				<input type="hidden" value="0" name="answer_correct_{$answer.id}" /> {*Have to prime it otherwise never unchecked*}
				Correct: <input type="checkbox" name="answer_correct_{$answer.id}" {if eq($answer.correct,1)}checked{/if} />
				Condition: 
				<select id="answer_condition" name="answer_condition_{$answer.id}">
						{*If these values are changed bad things will happen*}
						<option value="0"></option>
						<option value="1" {if eq($answer.option_id,1)} selected{/if}>{'if picked remove'|i18n('design/exam')}</option>
						<option value="2" {if eq($answer.option_id,2)} selected{/if}>{'if picked add'|i18n('design/exam')}</option>
						<option value="3" {if eq($answer.option_id,3)} selected{/if}>{'if picked follow with'|i18n('design/exam')}</option>
						<option value="4" {if eq($answer.option_id,4)} selected{/if}>{'if picked display in results'|i18n('design/exam')}</option>
						<option value="5" {if eq($answer.option_id,5)} selected{/if}>{'if not picked remove'|i18n('design/exam')}</option>
						<option value="6" {if eq($answer.option_id,6)} selected{/if}>{'if not picked add'|i18n('design/exam')}</option>
						<option value="7" {if eq($answer.option_id,7)} selected{/if}>{'if not picked follow with'|i18n('design/exam')}</option>
						<option value="8" {if eq($answer.option_id,8)} selected{/if}>{'if not picked display in results'|i18n('design/exam')}</option>
				</select>

				<select id="answer_value" name="answer_value_{$answer.id}">
					<option value="">&nbsp;</option>
					{foreach $elements as $link}
						{if or(eq($link.type,"question"),eq($link.type,"group"),eq($link.type,"text"))}
							{if ne($link.id,$element.id)} 
								<option value="{$link.id}" {if eq($answer.option_value,$link.id)} selected{/if}>{$link.type} {$link.id}</option>
							{/if}
						{/if}
					{/foreach}
				</select>
				<input type="image" src="/design/admin2/images/button-move_down.gif" alt="Down" name="AnswerMoveDown_{$answer.id}" title="{'Use these buttons to move elements up or down'|i18n('design/exam')}" />&nbsp;
				<input type="image" src="/design/admin2/images/button-move_up.gif" alt="Up" name="AnswerMoveUp_{$answer.id}" {'Use these buttons to move elements up or down'|i18n('design/exam')}" />
				<input size="2" maxlength="4" type="text" name="answer_priority_{$answer.id}" value="{$answer.priority}" />
			</div>

			{default answer_base='exam_answer'
					html_class='full'}
				<textarea name="answer_data_text_{$answer.id}" cols="70" rows="1">{$answer.content}</textarea>
			{/default}
		{/foreach}
		<br />
		<input class="button" id="newAnswer" name="CustomActionButton[newAnswer][{$element.id}]" value="Add Answer" type="Submit">
	</div>
<br clear="all" />
</div>