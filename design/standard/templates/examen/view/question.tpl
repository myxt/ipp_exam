<div id="exam-view">
	<div id="element question">
		<div class="text question">
			{$element.getXMLContent}
		</div>
		{if ne($random,"false")}
			{def $answers=$element.randomAnswers}
		{else}
			{def $answers=$element.answers}
		{/if}
		<input type="hidden" name="answer_{$element.id}" value="0">
		{foreach $element.randomAnswers as $answer}
			<div class="answer">

				<input type="radio" name="answer_{$element.id}" value="{$answer.id}"> {$answer.content}
			</div>
		{/foreach}
	</div>
</div>
