<div class="pagebreak">
	<label>{'pagebreak'|i18n('design/exam')|upcase} {$element.id}</label>
	<input class="button" value="{'Remove'|i18n('design/exam')}" name="CustomActionButton[remove][{$element.id}]" type="submit">
	<div class="listbutton">
		<input type="image" src="/design/admin2/images/button-move_down.gif" alt="Down" name="MoveDown_{$element.id}" title="{'Use these buttons to move elements up or down'|i18n('design/exam')}" />&nbsp;
		<input type="image" src="/design/admin2/images/button-move_up.gif" alt="Up" name="MoveUp_{$element.id}" title="{'Use these buttons to move elements up or down'|i18n('design/exam')}" />
		<input size="2" maxlength="4" type="text" name="element_priority_{$element.id}" value="{$element.priority}" />
	</div>
	<hr>
</div>