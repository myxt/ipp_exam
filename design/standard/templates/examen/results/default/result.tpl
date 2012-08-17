{def $status=cond(ezhttp_hasvariable(concat('status[',$node.object.id,']'), 'session'),ezhttp(concat('status[',$node.object.id,']'), 'session' ),false())}
<div id="result-{$exam_id}" class="exam-result">
	{if eq($survey,true())} {*if this is a survey then just show the results and get out*}
		{foreach $elements as $element}
			{exam_result_gui element=$element counts=$counts percents=$percents totals=$totals survey=true()}
		{/foreach}
	{else}
		{if $passed}
			<div class="alert alert-success">
				<h2>{'You passed'|i18n('design/exam')}</h2>{if $followup} {'on your second try.'|i18n('design/exam')}{/if}
			</div>
		{else}
			<div class="alert alert-error">
				<h2>{'You failed'|i18n('design/exam')}</h2>
{*not a followup or if status = retest*}
				{if eq($status,"RETEST")}
                                    <p>{'Do you want to try again?'|i18n('design/exam')}</p>
                                    <form name="advanced exam" method="post" action={$node.url_alias|ezurl}>
                                        <input type="hidden" name="exam_id" value="{$node.node_id}">
                                        <input type="hidden" name="exam_status" value="{$node.object.current_language}">
                                        <button class="btn btn-danger" type="submit" name="SubmitButton" value="{'Restart Exam'|i18n( 'design/exam' )}" /><i class="icon-chevron-left icon-white"></i> {'Restart Exam'|i18n( 'design/exam' )}</button>
                                    </form>
				{else}
					{'That was your second attempt.  Study harder and try again some other day.'|i18n('design/exam')}
				{/if}
			</div>
		{/if}

                {if $score}
                <p>{'Your score was [score]% correct'|i18n('design/exam','score',hash('[score]',cond($score,$score,0)))}.</p>
                {/if}
                
		{if $showStatistics}
		<p>
			{'You were one of [examCount] to take this exam.  Of which [passFirst] passed'|i18n('design/exam','score',hash('[examCount]',$examCount,'[passFirst]',$passFirst))}
			{if and($retest,ne($passSecond,0))} {'on the first try and [passSecond] passed on the second try'|i18n('design/exam','score',hash('[passSecond]',$passSecond))}{/if} {'The average score is [average]%.'|i18n('design/exam','score',hash('[average]',$average))} {'The highest score recorded is [highScore]%.'|i18n('design/exam','score',hash('[highScore]',$highScore))}  {if ge($score,$highScore)}{'Congratulations, you got the high score.'|i18n('design/exam')}{/if}
		</p>
		{/if}
		{if and($showCorrect,ne($score,100))}
                <h3>{'These were the correct answers'|i18n('design/exam')}</h3>
				{if $fromSession}
					{if $incorrect} {*if we have this statistics weren't saved and we had to get incomplete info from the badarray*}
					{def $incorrectElement=array()}
						{foreach $incorrect as $key => $badAnswer}
							{set $incorrectElement = fetch( 'examen', 'element', hash( 'id', $key ))}
							<div class="question control-group">
                                                            <label class="control-label">
                                                                {$incorrectElement.content}
                                                            </label>
                                                            <div class="controls">
                                                                {foreach $incorrectElement.answers as $answer}
                                                                    <div class="answer">
                                                                        <div class="icon{if eq($answer.id,$badAnswer[0])} incorrect{elseif eq($answer.id,$badAnswer[1])} correct{/if}">&nbsp;</div>{$answer.content}
                                                                    </div>
                                                                {/foreach}
                                                            </div>
							</div>
						{/foreach}
					{/if}
					{if $resultArray}
						{def $resultElement=array()}
						{foreach $resultArray as $result}
							{set $resultElement = fetch( 'examen', 'element', hash( 'id', $result ))}
							{if eq($resultElement.type,"text")}
								{exam_view_gui element=$resultElement}
							{/if}
						{/foreach}
					{/if}
				{else}
					{foreach $resultArray as $result}
						{if eq($result[1].type,"question")}
							{exam_result_gui element=$result[1] result=$result[0] survey=false()}
						{/if}
					{/foreach}
				{/if}
		{/if}
		{if $passed}
                    {if $certificate}
                    <h3>{'Download Certificate'|i18n( 'design/exam' )}</h3>
                        <p>
                            {'Do you wish to have a certificate to commemorate your success?'|i18n('design/exam')}
                            <form class="well form-inline" name="download certificate" method="post" action={'examen/download/'|ezurl}>
                                <input type="hidden" name="exam_id" value="{$examID}">
                                <input type="hidden" name="hash" value="{$hash}">

                                <input type="text" name="name" value="" placeholder="Naam">
                                <input class="btn" type="submit" name="SubmitButton" value="{'Download Certificate'|i18n( 'design/exam' )}" title="{'Download Certificate'|i18n( 'design/exam' )}" />
                            </form>
                        </p>
                    {/if}

		{include uri="design:examen/results/socialmedia.tpl" examID=$examID hash=$hash}
		{/if} {* end if passed *}
	{/if} {*end if survey*}
</div>
