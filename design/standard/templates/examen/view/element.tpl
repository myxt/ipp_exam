<div class="exam-element">
	<form name="exam element" method="post" action={'examen/exam/'|ezurl}>
		<input type="hidden" name="exam_id" value="{$exam_id}">
		{foreach $elements as $element}
				{exam_view_gui element=$element random=$random}
		{/foreach}
		<input class="button" type="submit" name="SubmitButton" value="{'Submit'|i18n( 'design/admin/node/view/full' )}" title="{'Submit'|i18n( 'design/admin/node/view/full' )}" />
	</form>
</div>