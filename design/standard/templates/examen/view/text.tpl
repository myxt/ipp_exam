<br>WE ARE IN THE TEXT VIEW GUI<br>
<div id="element text {$element.id}">
	<div class="text-block">
		{$element.content}
	</div>

	{if ne($simple,true())}
		<form action="/examen/exam" method="post">
			<input type="button" value="next">
		</form>
	{/if}
</div>