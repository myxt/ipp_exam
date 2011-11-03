{if $survey}
	{def $percent=0}
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
			{set $percent=cond($percents[$answer.id],$percents[$answer.id],0)}
			<td width="10px">
				{$percent}%
			</td>
			<td class="survey-answer">
				<div class="survey-bar" style="width:{$percent}%; float:left;" >&nbsp;</div>
			</td>
		</tr>
		{/foreach}
			
		</table>
		{'Total'|i18n('design/exam')} {$totals[$element.id]}
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
	{if $result.conditional}
			{def $conditionalElement = fetch( 'examen', 'element', hash( 'id', $result.conditional ))}
			{if eq($conditionalElement.type,"text")}
				{exam_view_gui element=$conditionalElement}
			{/if}
	{/if}
{/if}