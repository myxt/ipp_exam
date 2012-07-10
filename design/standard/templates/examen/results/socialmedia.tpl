{if cond($node.data_map.facebook_text.has_content,true(),$node.data_map.twitter_text.has_content,true(),$node.data_map.hyves_text.has_content,true(),false())}
<h3>{"Send your results to your friends"|i18n('design/exam')}</h3>
<div id="sharebox">
	<form id="examen-send" method="post" action={concat("examen/send/",$examID,"/",$hash)|ezurl()} target="_blank" >
		<fieldset>
		{if $node.data_map.facebook_text.has_content}
			<input type="image" src={"ic-web20-facebook.png"|ezimage} value="facebook" name="FacebookButton" title="{'Publish a link to your results on Facebook'|i18n('design/exam')}" />
		{/if}
		{if $node.data_map.twitter_text.has_content}
			<input type="image" src={"ic-web20-twitter.png"|ezimage} value="twitter" name="TwitterButton" title="{'Publish a link to your results on Twitter'|i18n('design/exam')}" />
		{/if}
		{if $node.data_map.hyves_text.has_content}
			<input type="image" src={"ic-web20-hyves.png"|ezimage} value="hyves" name="HyvesButton" title="{'Publish a link to your results on Hyves'|i18n('design/exam')}" />
		</fieldset>
		{/if}
	</form>
</div>
{/if}