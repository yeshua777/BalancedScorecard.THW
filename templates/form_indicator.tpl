	<form class="form-box" action="./" method="post">
		<fieldset>
			<p>Indikator</p>

			<div class="form-group-box">
				<label for="name">Name</label>
				<input id="name" name="name" type="text" value="{$indicator->GetName()}" />
			</div>

			<div class="form-group-box">
				<label for="value">Wert</label>
				<input id="value" name="value" type="text" value="{$indicator->GetValue()}" />
			</div>

			<div class="form-group-box">
				<label for="baseline">Basiswert (Startwert)</label>
				<input id="baseline" name="baseline" type="text" value="{$indicator->GetBase()}" />
			</div>

			<div class="form-group-box">
				<label for="target">Zielwert</label>
				<input id="target" name="target" type="text" value="{$indicator->GetTarget()}" />
			</div>

			<div class="form-group-box">
				<label for="min">Minimum (Skala)</label>
				<input id="min" name="min" type="text" value="{$indicator->GetMin()}" />
			</div>

			<div class="form-group-box">
				<label for="max">Maximum (Skala)</label>
				<input id="max" name="max" type="text" value="{$indicator->GetMax()}" />
			</div>

			<div class="form-group-box">
				<label for="unit">Einheit</label>
				<input id="unit" name="unit" type="text" value="{$indicator->GetUnit()}" />
			</div>

			<div class="form-group-box">
				<label for="weight">Gewichtung</label>
				<input id="weight" name="weight" type="text" value="{$indicator->GetWeight()}" />
			</div>

			<div class="form-group-box">
				<label for="optimization">Optimierung</label>
				<select id="optimization" name="optimization">
				{if $indicator->GetMaximize() == 1}
					<option selected="selected" value="1">Maximierung</option>
					<option value="0">Minimierung</option>
				{else}
					<option value="1">Maximierung</option>
					<option selected="selected" value="0">Minimierung</option>
				{/if}
				</select>
			</div>

			<div class="form-group-box">
				<label for="desc">Beschreibung</label>
				<textarea id="desc" name="desc" cols="10" rows="7">{$indicator->GetDescription()}</textarea>
			</div>
		</fieldset>
		<fieldset>
			<input name="page" type="hidden" value="details" />
			<input name="action" type="hidden" value="save_ind" />
			<input name="id_ind" type="hidden" value="{$indicator->GetIdentifier()}" />
			<input name="id_per" type="hidden" value="{$indicator->GetPerspective()}" />
			<input type="submit" value="Speichern" />
		</fieldset>
	</form>
