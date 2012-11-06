	<form class="form-box" action="./" method="post">
		<fieldset>
			<p>Perspektive</p>

			<div class="form-group-box">
				<label for="name">Name</label>
				<input id="name" name="name" type="text" value="{$perspective->GetName()}" />
			</div>

			<div class="form-group-box">
				<label for="weight">Gewichtung</label>
				<input id="weight" name="weight" type="text" value="{$perspective->GetWeight()}" />
			</div>

			<div class="form-group-box">
				<label for="desc">Beschreibung</label>
				<textarea id="desc" name="desc" cols="10" rows="7">{$perspective->GetDescription()}</textarea>
			</div>
		</fieldset>
		<fieldset>
			<input name="page" type="hidden" value="details" />
			<input name="action" type="hidden" value="save_per" />
			<input name="id_per" type="hidden" value="{$perspective->GetIdentifier()}" />
			<input name="id_bsc" type="hidden" value="{$perspective->GetScorecard()}" />
			<input type="submit" value="Speichern" />
		</fieldset>
	</form>
