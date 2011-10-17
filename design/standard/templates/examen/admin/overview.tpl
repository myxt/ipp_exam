EXAM LIST ADMIN VIEW<br>
{*got to make this nice with the 10 20 50 preferences etc.*}
{def $contentObject=array()}
{if $exams|count}
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

{foreach $exams as $exam}
	{set $contentObject=fetch( 'content', 'object', hash( 'object_id', $exam.contentobject_id ))}
	<tr>
		<td><a href={concat("/examen/statistics/",$exam.contentobject_id)|ezurl}>{$exam.id}</a></td>
		<td>{$contentObject.name}<a href={concat("/content/edit/",$exam.contentobject_id)|ezurl}><img src={"edit.gif"|ezimage} alt="{'Edit'|i18n('design/admin/settings')}" /></a></td>
		<td>{$exam.count}</td>
		<td>{$exam.pass_first}</td>
		<td>{$exam.pass_second}</td>
		<td>{$exam.enabled}</td>
		<td>{$exam.high_score}</td>
	</tr>
{/foreach}
{else}
	{"No exams have been created yet."|i18n('datatype/exam')}
{/if}
</table>