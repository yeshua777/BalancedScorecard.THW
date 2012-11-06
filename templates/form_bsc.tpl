
	<form class="form-box" action="./" method="post">
		<fieldset>
			<p>Balanced Scorecard</p>

			<div class="form-group-box">
				<label for="name">Name</label>
				<input id="name" name="name" type="text" value="{$bsc->GetName()}" />
			</div>

			<div class="form-group-box">
				<label for="desc">Beschreibung</label>
				<textarea id="desc" name="desc" cols="10" rows="7">{$bsc->GetDescription()}</textarea>
			</div>
		</fieldset>
		<fieldset>
			<input name="action" type="hidden" value="save_bsc" />
			<input type="submit" value="Speichern" />
		</fieldset>
	</form>
