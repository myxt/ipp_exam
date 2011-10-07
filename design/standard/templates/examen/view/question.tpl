<br>WE ARE IN THE QUESTION VIEW GUI<br>
<div id="element question {$element.id}">
	<form action="/examen/exam" method="post">
	<div class="text question">
		{$element.content}
	</div>
	{foreach $element.answers as $answer}
		<div class="answer">
			{* <input type="checkbox" name="answer_{$element.id}" value="{$answer.id}"> {$answer.content} *}
			<input type="radio" name="answer_{$element.id}" value="{$answer.id}"> {$answer.content}
		</div>
	{/foreach}

	{if ne($simple,true())}
		<form action="/examen/exam" method="post">
			<input type="button' value="next>
		</form>
	{/if}
</div>
