<div id="exam-admin">
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
<div class="overview">
	<table>
		<thead>
			<th class="id">{'id'|i18n('design/exam')}</th>
			<th class="title">{'title'|i18n('design/exam')}</th>
			<th class="number">{'number of testees'|i18n('design/exam')}</th>
			<th class="first">{'pass on first try'|i18n('design/exam')}</th>
			<th class="second">{'pass on second try'|i18n('design/exam')}</th>
			<th class="score">{'High score'|i18n('design/exam')}</th>
		</thead>

		<tr>
			<td>{$exam.id}</td>
			<td><a href={concat("/content/edit/",$exam.contentobject_id)|ezurl}><img src={"edit.gif"|ezimage} alt="{'Edit'|i18n('design/admin/settings')}" /></a> {$contentObject.name}</td>
			<td>{$exam.count}</td>
			<td>{$exam.pass_first}</td>
			<td>{$exam.pass_second}</td>
			<td>{$exam.high_score}</td>
		</tr>
	</table>
</div>

	<br/>
<div class="exam">
	<table>
		<thead>
			<th class="id">{'id'|i18n('design/exam')}</th>
			<th class="text">{'text'|i18n('design/exam')}</th>
			<th class="total">{'total answered'|i18n('design/exam')}</th>
			<th class="first">{'correct on first try'|i18n('design/exam')}</th>
			<th class="second">{'correct on second try'|i18n('design/exam')}</th>
		</thead>

		{foreach $exam.questions as $question}
			<tr>
				<td>{'question'|i18n('design/exam')|upcase} {$question.id}</td>
				<td>{$question.content}</td>
				<td>{'total'|i18n('design/exam')|upcase} {$question.statistics['total']}</td>
				<td>{$question.statistics['first_pass']}</td>
				<td>{$question.statistics['second_pass']}</td>
			</tr>
			{foreach $question.answers as $answer}
				<tr>
					<td>{'answer'|i18n('design/exam')|upcase} {$answer.id}</td>
					<td>{$answer.content}</td>
					<td></td>
					<td{if $answer.correct} class="correct"{/if}></td>
					<td>{'Times chosen'|i18n('design/exam')}: {$question.statistics['answer_count'][$answer.id]}</td>
				</tr>
			{/foreach}
		{/foreach}
	</table>
</div>
{else}
	{'Exam not found.'|i18n('design/exam')}
{/if}
</div>