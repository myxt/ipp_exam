WE ARE IN EXAMEN DEFAULT RESULT<br/>

{if ne(count($errors),0)}{*We should never get errors on these pages*}
<div class="error">
	{foreach $errors as $error}
		{$error|wash}<br/>
	{/foreach}
</div>
{else}
	{if $survey} {*if this is a survey then just show the results and get out*}
	<p>
	THIS LOOKS LIKE A SURVEY
		{foreach $elements as $element}
			{exam_result_gui element=$element[1]}
		{/foreach}
	</p>
	{else}
		{if $passed}
		<p>
			YOU PASSED{if $followup} on your second try{/if}
		</p>
		{else}
		<p>
			YOU FAILED. {if $followup|not}Do you want to try again?{else}That was your second try.  Study hard and try again some other day.{/if}
		</p>
		{/if}
		Your score was {$score}% correct.<br/>

		{if $showStatistics}
		SHOW STATISTICS SET</p>
		<p>
			You were one of {$examCount} to take this exam.  Of which {$passFirst} passed{if $retest} on the first try and {$passSecond} passed on the second try{/if}.  The highest score recorded was {$highScore}.  {if eq($score,highScore)}Congratulations, you got the high score.{/if}
		</p>
		{/if}
		{if $showCorrect}
		SHOW CORRECT ANSWERS SET<br/>
			{foreach $elements as $element}
{*$element[1]|ezfire("ELEMENT ID IN TEMPLATE")*}
		QUESTION<br/>
				{exam_result_gui result=$element[1]}
			{/foreach}
		{/if}
		{if $certificate}
		<p>
			PDF DOWNLOAD BUTTON HERE
		</p>
		{/if}
		{if $socialMedia}
		<p>
			SOCIAL MEDIA BUTTONS HERE
		</p>
		{/if}
	{/if} {*end if survey*}
{/if} {*end if error*}