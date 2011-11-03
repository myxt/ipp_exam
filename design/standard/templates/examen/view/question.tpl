<div id="exam-view">
	<div id="element question">
		<div class="text question">
			{$element.content}
		</div>
		{foreach $element.randomAnswers as $answer}
			<div class="answer">
				<input type="radio" name="answer_{$element.id}" value="{$answer.id}"> {$answer.content}
			</div>
		{/foreach}
	</div>
</div>
