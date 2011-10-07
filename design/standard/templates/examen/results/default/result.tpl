WE ARE IN RESULT DEFAULT<br/>
{if ne(count($errors),0)}
{count($errors)} COUNT ERRORS <br/>
<div class="error">
	{foreach $errors as $error}
		{$error|wash}<br/>
	{/foreach}
</div>
{/if}
{if $survey}
<p>
THIS LOOKS LIKE A SURVEY
</p>
{/if}

{if $passed}
<p>
	YOU PASSED{if $followup} on your second try{/if}
</p>
{else}
<p>
	YOU FAILED. {if $followup|not}Do you want to try again?{else}That was your second try.  Study and try again some other day.{/if}
</p>
{/if}

{if $showStatistics}
SHOW STATISTICS SET</p>
<p>
	You were one of {$totalExam} to take this exam.  Of which {$firstPass} passed{if $retest} on the first try and {$secondPass} passed on the second try{/if}.  The highest score recorded was {$highScore}.  {if eq($score,highScore)}Congratulations, you got the high score.{/if}
</p>
{/if}
{if $showCorrect}
SHOW CORRECT SET<br/>
	{foreach $questions as $question}
QUESTION<br/>
		{exam_result_gui element=$question}
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