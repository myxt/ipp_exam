<div id="exam-admin">
{*should make this nice with the 10 20 50 preferences etc.*}
{def $contentObject=array()}
{if $exams|count}
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
			{foreach $exams as $exam}
				{set $contentObject=fetch( 'content', 'object', hash( 'object_id', $exam.contentobject_id ))}
				{if eq($contentObject.name,"")}{continue}{/if}
				<tr>
					<td><a href={concat("/examen/statistics/",$exam.contentobject_id)|ezurl}>{$exam.id}</a></td>
					<td><a href={concat("/content/edit/",$exam.contentobject_id)|ezurl}><img src={"edit.gif"|ezimage} alt="{'Edit'|i18n('design/admin/settings')}" /></a> {$contentObject.name}</td>
					<td>{$exam.count}</td>
					<td>{$exam.pass_first}</td>
					<td>{$exam.pass_second}</td>
					<td>{$exam.high_score}</td>
				</tr>
			{/foreach}
		</table>
	</div>
{else}
	{"No exams have been created yet."|i18n('design/exam')}
{/if}
</div>