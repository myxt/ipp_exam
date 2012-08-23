<div class="exam-element">
	<form name="exam element" method="post" action={'examen/exam/'|ezurl}>
		<input type="hidden" name="exam_id" value="{$exam_id}">
		{foreach $elements as $element}
				{exam_view_gui element=$element random=$random}
		{/foreach}

        {if eq($show_result,1)}
            <button class="btn" type="submit" name="SubmitButton" value="{'Continue'|i18n( 'design/admin/node/view/full' )}" data-toggle="button" onClick="this.disabled=true;this.form.submit();">{'Continue'|i18n( 'design/admin/node/view/full' )} <i class="icon-chevron-right"></i></button>
        {/if}
    </form>
</div>
