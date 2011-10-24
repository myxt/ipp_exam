<div id="exam-result">
	{if eq($survey,1)} {*if this is a survey then just show the results and get out*}
		{foreach $elements as $element}
			{exam_result_gui element=$element counts=$counts percents=$percents totals=$totals survey=true()}
		{/foreach}
	{else}
		{if $passed}
			<div class="passed">
				<div class="headline">{'You passed'|i18n('design/exam')|upcase}</div>{if $followup} {'on your second try.'|i18n('design/exam')}{/if}
			</div>
		{else}
			<div class="failed">
				<div class="headline">{'You failed'|i18n('design/exam')|upcase}</div>
				{if $followup|not} {'Do you want to try again?'|i18n('design/exam')}
					<form name="advanced exam" method="post" action={$node.path_identification_string|ezurl}>
						<input type="hidden" name="exam_id" value="{$examID}">
						<input class="button" type="submit" name="SubmitButton" value="{'Restart Exam'|i18n( 'design/exam' )}" title="{'Restart Exam'|i18n( 'design/exam' )}" />
					</form>
				{else}
					{'That was your second attempt.  Study harder and try again some other day.'|i18n('design/exam')}
				{/if}
			</div>
		{/if}
		<br/>

		{'Your score was [score]% correct'|i18n('design/exam','score',hash('[score]',cond($score,$score,0)))}.<br/>

		{if $showStatistics}
		<div class="statistics-text">
			{'You were one of [examCount] to take this exam.  Of which [passFirst] passed.'|i18n('design/exam','score',hash('[examCount]',$examCount,'[passFirst]',$passFirst))}
			{if and($retest,ne($passSecond,0))} {'on the first try and [passSecond] passed on the second try.'|i18n('design/exam','score',hash('[passSecond]',$passSecond))}{/if} {'The highest score recorded was [highScore].'|i18n('design/exam','score',hash('[highScore]',$highScore))}  {if ge($score,$highScore)}{'Congratulations, you got the high score.'|i18n('design/exam')}{/if}
		</div>
		{/if}
		{if $showCorrect}
			{if or(eq($passed,1),eq($followup,1))}
				{foreach $resultArray as $result}
					{exam_result_gui element=$result[1] result=$result[0]}
				{/foreach}
			{/if}
		{/if}
		{if $passed}
			{if $certificate}
				<p>
					{'Do you wish to have a certificate to commemorate your success?'|i18n('design/exam')}
					<form name="download certificate" method="post" action={'examen/download/'|ezurl}>
					<input type="hidden" name="exam_id" value="{$examID}">
					<input type="hidden" name="hash" value="{$hash}">
					<input type="text" name="name" value="">
					<input class="button" type="submit" name="SubmitButton" value="{'Download Certificate'|i18n( 'design/exam' )}" title="{'Download Certificate'|i18n( 'design/exam' )}" />
					</form>
				</p>
			{/if}

			{if $socialMedia}
				<p>
				{include uri="design:examen/results/socialmedia.tpl" url="" message="'I passed the exam at [url]'|i18n('design/exam','url',hash('[url]',$url))"}
				</p>
			{/if}
		{/if} {* end if passed *}
	{/if} {*end if survey*}
</div>