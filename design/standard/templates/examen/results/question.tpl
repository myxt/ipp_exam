{if $survey}
	<div class="question">
		<div class="text">
			{$element.content}
		</div>
		<table width="100%">
		{foreach $element.answers as $answer}
		<tr>
			<td width="30%">
				{$answer.content}
			</td>
			<td class="survey-answer">
				<div class="survey-bar" style="width:{$percents[$answer.id]}%; float:left;" >&nbsp;</div>
			</td>
		</tr>
		{/foreach}
		</table>
	</div>
{else}
	<div class="question">
		<div class="text">
			{$element.content}
		</div>
		{foreach $element.answers as $answer}
			<div class="answer">
				<div class="icon{if and(eq($result.answer,$answer.id),eq($answer.correct,0))} incorrect{elseif eq($answer.correct,1)} correct{/if}">&nbsp;</div>{$answer.content}
			</div>
		{/foreach}
	</div>
{/if}