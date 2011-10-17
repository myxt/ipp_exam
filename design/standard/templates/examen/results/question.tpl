<br>WE ARE IN THE QUESTION RESULT GUI<br>
{*$element|ezfire("ELEMENT IN QUESTION TEMPLATE")*}
{*$result|ezfire("RESULT IN QUESTION TEMPLATE")*}
<div id="element question {$element.id}">
	<div class="text question">
		{$element.content}<br>
	</div>
	{foreach $element.answers as $answer}
		<div class="answer{if eq($result.correct,true())} correct{/if}">
		{if eq($result.answer,$answer.id)}YOU CHOSE THIS{else}{/if}{$answer.content}
		</div>
	{/foreach}
</div>
