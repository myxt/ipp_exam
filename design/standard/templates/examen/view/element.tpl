<div class="exam-element">
	<form name="exam element" method="post" action={'examen/exam/'|ezurl}>
		<input type="hidden" name="exam_id" value="{$exam_id}">
		{foreach $elements as $element}
				{exam_view_gui element=$element random=$random}
		{/foreach}

		{if eq($show_result,1)}
			<input class="button" type="submit" name="SubmitButton" value="{'Continue'|i18n( 'design/admin/node/view/full' )}" title="{'Continue'|i18n( 'design/admin/node/view/full' )}" />
		{/if}
	</form>
</div>
