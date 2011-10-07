<br>WE ARE IN THE QUESTION RESULT GUI<br>
{def $result=array()}
<div id="element question {$element.id}">
	<div class="text question">
		{$element.content}<br>
	</div>
	{foreach $element.answers as $answer}
		{set $result=fetch( 'examen', 'answer', hash( 'hash', $hash, 'question_id', $answer.id )} 
		<div class="answer{if eq($element.correct,true())} correct{/if}">
		{if eq($answer.correct,true())} correct{elseif }WRONG{/if} {$answer.content}
		</div>
	{/foreach}
</div>
