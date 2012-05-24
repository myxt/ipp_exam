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
			{set $percent=cond($percents[$answer.id],$percents[$answer.id],false,0)}
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
	{if eq($result.correct,0)} {*Only want the incorrect ones*}
		<div class="question">
			<div class="text">
				{$element.getXMLContent}
			</div>
			{foreach $element.answers as $answer}
				<div class="answer">
					<div class="icon{if and(eq($result.answer,$answer.id),eq($answer.correct,0))} incorrect{elseif eq($answer.correct,1)} correct{/if}">&nbsp;</div>{$answer.content}
				</div>
			{/foreach}
		</div>
	{/if}
	{if $result.conditional}
			{def $conditionalElement = fetch( 'examen', 'element', hash( 'id', $result.conditional ))}
			{if eq($conditionalElement.type,"text")}
				{exam_view_gui element=$conditionalElement}
			{/if}
	{/if}
{/if}