EXAM LIST ADMIN VIEW<br>
{if $exam}
	{*got to make this nice with the 10 20 50 preferences etc.*}
	{def $contentObject=array()}
	{set $contentObject=fetch( 'content', 'object', hash( 'object_id', $exam.contentobject_id ))}

	<div class="attribute-title">
	{attribute_view_gui attribute=$contentObject.data_map.title}
	</div>
	<div class="attribute-intro">
	{attribute_view_gui attribute=$contentObject.data_map.intro}
	</div>
	{*repeat line from overview*}
	<table>
		<thead>
			<th>id</th>
			<th>content object id</th>
			<th>number of testees</th>
			<th>pass on first run</th>
			<th>pass on second run</th>
			<th>enabled</th>
			<th>High score</th>
		</thead>

		<tr>
			<td><a href={concat("/examen/statistics/",$exam.contentobject_id)|ezurl}>{$exam.id}</a></td>
			<td><a href={concat("/content/view/full/",$exam.contentobject_id)|ezurl}>{$exam.contentobject_id}</a></td>
			<td>{$exam.count}</td>
			<td>{$exam.pass_first}</td>
			<td>{$exam.pass_second}</td>
			<td>{$exam.enabled}</td>
			<td>{$exam.high_score}</td>
		</tr>
	</table>
	<br/>
	<table>
		<thead>
			<th>id</th>
			<th>text</th>
			<th>total answered</th>
			<th>correct on first run</th>
			<th>correct on second run</th>
		</thead>

		{foreach $exam.questions as $question}
			<tr>
				<td>QUESTION {$question.id}</td>
				<td>{$question.content}</td>
				<td>TOTAL {$question.statistics['total']}</td>
				<td>{$question.statistics['first_pass']}</td>
				<td>{$question.statistics['second_pass']}</td>
			</tr>
			{foreach $question.answers as $answer}
				<tr>
					<td></td>
					<td>ANSWER {$answer.id}</td>
					<td>{$answer.content}</td>
					<td></td>
					<td>Times chosen: {$question.statistics['answer_count'][$answer.id]}</td>
				</tr>
			{/foreach}
		{/foreach}
	</table>
{else}
	exam not found.
{/if}