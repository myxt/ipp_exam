<br>WE ARE IN THE QUESTION VIEW GUI<br>
<div id="element question {$element.id}">
{if $element.parent}MEMBER GROUP {$element.parent}<br>{/if}
	<form action="/examen/exam" method="post">
	<div class="text question">
		{$element.content}
	</div>
	{foreach $element.answers as $answer}
		<div class="answer">
			<input type="radio" name="answer_{$element.id}" value="{$answer.id}"> {$answer.content}
		</div>
	{/foreach}
</div>
