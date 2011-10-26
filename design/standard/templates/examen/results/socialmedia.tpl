<h3>Stuur je resultaten door naar je vriend(inn)en</h3>
<div id="sharebox">
	<form id="examen-send" method="post" action={concat("examen/send/",$examID,"/",$hash)|ezurl()} target="_blank" >
		<fieldset>
			<input type="image" src={"ic-web20-facebook.png"|ezimage} value="facebook" name="FacebookButton" title="{'Publiceer een link naar jouw programma op Facebook'|i18n('design/examen')}" />
			<input type="image" src={"ic-web20-twitter.png"|ezimage} value="twitter" name="TwitterButton" title="{'Publiceer een link naar jouw resultaten op Twitter'|i18n('design/examen')}" />
			<input type="image" src={"ic-web20-hyves.png"|ezimage} value="hyves" name="HyvesButton" title="{'Publiceer een link naar jouw resultaten op Hyves'|i18n('design/examen')}" />
		</fieldset>
	</form>
</div>		