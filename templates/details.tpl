{include file=header.tpl}

	<div id="datagrid" class="box g75p">
		<table>
			<thead>
				<tr>
					<th></th>
					<th>Fortschritt</th>
					<th>Effizienz</th>
					<th>Wert</th>
					<th>Basis</th>
					<th>Ziel</th>
					<th>Gewichtung</th>
					<th>Einheit</th>
					<th>Optionen</th>
				</tr>
			</thead>
			<tbody>
				<tr class="bscrow">
					<td class="head">{$bsc->GetName()}</td>
					<td>{$bsc->GetProgress()|round:0}%</td>
					<td>{$bsc->GetPerformance()|round:0}%</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td class="detail_options">
						<a href="./index.php?page=details&action=edit_bsc"><img src="./images/spanner_16.png" alt="" title="Bearbeiten" /></a>
						<a href="./index.php?page=lightbox&action=dia_bsc&id_bsc={$bsc->GetIdentifier()}&KeepThis=true&TB_iframe=true&height=500&width=700" class="thickbox"><img src="./images/pie_chart_16.png" alt="" title="Diagramme" /></a>
						<a href="./index.php?page=details&action=del_bsc" class="delete"><img src="./images/cancel_16.png" alt="" title="Entfernen" /></a>
						<a href="./index.php?page=details&action=add_per"><img src="./images/add_16.png" alt="" title="Hinzufügen" /></a>
					</td>
				</tr>
			{foreach item=per from=$bsc->GetPerspectives()}
				<tr class="perrow">
					<td class="head">{$per->GetName()}</td>
					<td>{$per->GetProgress()|round:0}%</td>
					<td>{$per->GetPerformance()|round:0}%</td>
					<td></td>
					<td></td>
					<td></td>
					<td>{$per->GetWeight()}</td>
					<td></td>
					<td class="detail_options">
						<a href="./index.php?page=details&action=edit_per&id={$per->GetIdentifier()}"><img src="./images/spanner_16.png" alt="" title="Bearbeiten" /></a>
						<a href="./index.php?page=lightbox&action=dia_pers&id_bsc={$bsc->GetIdentifier()}&id_pers={$per->GetIdentifier()}&KeepThis=true&TB_iframe=true&height=500&width=700" class="thickbox"><img src="./images/pie_chart_16.png" alt="" title="Diagramme" /></a>
						<a href="./index.php?page=details&action=del_per&id={$per->GetIdentifier()}" class="delete"><img src="./images/cancel_16.png" alt="" title="Entfernen" /></a>
						<a href="./index.php?page=details&action=add_ind&id={$per->GetIdentifier()}"><img src="./images/add_16.png" alt="" title="Hinzufügen" /></a>
					</td>
				</tr>
			{foreach item=ind from=$per->GetIndicators()}
				<tr class="{cycle values=odd,even}">
					<td class="head">{$ind->GetName()}</td>
					<td>{$ind->GetProgress()|round:0}%</td>
					<td>{$ind->GetPerformance()|round:0}%</td>
					<td>{$ind->GetValue()}</td>
					<td>{$ind->GetBase()}</td>
					<td>{$ind->GetTarget()}</td>
					<td>{$ind->GetWeight()}</td>
					<td>{$ind->GetUnit()}</td>
					<td class="detail_options">
						<a href="./index.php?page=details&action=edit_ind&id={$ind->GetIdentifier()}"><img src="./images/spanner_16.png" alt="" title="Bearbeiten" /></a>
						<a href="./index.php?page=lightbox&action=dia_indi&id_bsc={$bsc->GetIdentifier()}&id_pers={$per->GetIdentifier()}&id_indi={$ind->GetIdentifier()}&KeepThis=true&TB_iframe=true&height=500&width=700" class="thickbox"><img src="./images/pie_chart_16.png" alt="" title="Diagramme" /></a>
						<a href="./index.php?page=details&action=del_ind&id={$ind->GetIdentifier()}" class="delete"><img src="./images/cancel_16.png" alt="" title="Entfernen" /></a>
					</td>
				</tr>
			{/foreach}
			{/foreach}
			</tbody>
		</table>
	</div>

{if $form_bsc == true}
	<div class="box g25p">{include file=form_bsc.tpl}</div>
{elseif $form_perspective == true}
    <div class="box g25p">{include file=form_perspective.tpl}</div>
{elseif $form_indicator == true}
    <div class="box g25p">{include file=form_indicator.tpl}</div>
{else}
    <div class="box g25p">
    	<h3>Legende</h3>
        <ul style="list-style-type: none">
            <li><img src="./images/spanner_16.png" alt="" /> Bearbeiten</li>
            <li><img src="./images/pie_chart_16.png" alt="" /> Diagramme</li>
			<li><img src="./images/cancel_16.png" alt="" /> Entfernen</li>
            <li><img src="./images/add_16.png" alt="" /> Hinzufügen</li>
        </ul>
    </div>
{/if}

{include file=footer.tpl}
