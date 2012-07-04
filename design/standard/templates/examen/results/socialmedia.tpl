<h3>{"Send your results to your friends"|i18n('design/exam')}</h3>
<div id="sharebox">
	<form id="examen-send" method="post" action={concat("examen/send/",$examID,"/",$hash)|ezurl()} target="_blank" >
		<fieldset>
			<input type="image" src={"ic-web20-facebook.png"|ezimage} value="facebook" name="FacebookButton" title="{'Publish a link to your results on Facebook'|i18n('design/exam')}" />
			<input type="image" src={"ic-web20-twitter.png"|ezimage} value="twitter" name="TwitterButton" title="{'Publish a link to your results on Twitter'|i18n('design/exam')}" />
			<input type="image" src={"ic-web20-hyves.png"|ezimage} value="hyves" name="HyvesButton" title="{'Publish a link to your results on Hyves'|i18n('design/exam')}" />
		</fieldset>
	</form>
</div>
