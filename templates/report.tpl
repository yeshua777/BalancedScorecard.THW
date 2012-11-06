{include file="header.tpl"}

{report->bscArray assign='bscs'}
{assign var="anz_Bscs" value=$bscs.sc_data|@count}
{assign var="anz_Pers" value=$bscs.sc_data.pers_data.0|@count}
				{literal}
                <script>
                    $(function() {
                        $( "#datepicker" ).datepicker();
                        $( "#datepicker" ).datepicker( "option", "maxDate", '+0d' );
                        $( "#datepicker" ).datepicker( "option", "minDate", {/literal}'{report->beginning}'{literal} );
                         });

                        $(function() {
                            $( "#datepicker2" ).datepicker();
                            $( "#datepicker2" ).datepicker( "option", "maxDate", '+0d' );
                            $( "#datepicker2" ).datepicker( "option", "minDate", {/literal}'{report->beginning}'{literal} );
                             });
                </script>
                {/literal}

<div style="float: left; margin-right: 30px;" class="g75p">

<div id="report" class="box g75p">
<h3>Graphen der Scorecard</h3>
<br />
<img src={$bscs.info.sc.0.RadarDia} /><br />
<img src={$bscs.info.sc.0.PerformanceLinie} /><br />
<img src={$bscs.info.sc.0.PerformancePie} /><br />

</div>
<div id="report" class="box g75p">
<h3>Daten der Scorecard</h3>

<table>
<center>
	<tr>
		<th>Datum</th>
		<th>Wert</th>
	</tr>
		{foreach from=$bscs.sc_data item=sc key=scNr}
	<tr  class="{cycle values="odd,even"}">
		<td>{$sc.Datum}</td>
		<td>{$sc.Performance|round:2}</td>
	</tr>
		{/foreach}

</center>
</table>
</div>


{foreach from=$bscs.sc_data.0.pers_data item=perspective key=persNr}
	<div id="report" class="box g75p">
	
	<h2>
	<a name="{$perspective.Name}">
	{$perspective.Name}
	</a>
	</h2>

<table>
	<center>
	<tr>
		<th>Name</th>
		<th>Start-Wert</th>
		<th>End-Wert</th>
		<th>Differenz</th>
		<th>Indikatoren</th>
	</tr>
	<tr>
		<td>{$perspective.Name}</td>
		<td>{$bscs.info.pers.$persNr.start_perf|round:2}</td>
		<td>{$bscs.info.pers.$persNr.end_perf|round:2}</td>
		<td>{$bscs.info.pers.$persNr.dynamic|round:2}</td>
		<td>
		{foreach from=$bscs.sc_data.0.pers_data.$persNr.ind_data item=indicator}
			{$indicator.Name}<br />
		{/foreach}
		</td>
	</tr>
	</center>
</table>

<h3>Graphen der Perspektive "{$perspective.Name}"</h3>


<img src={$bscs.info.pers.$persNr.RadarDia} /><br />
<img src={$bscs.info.pers.$persNr.PerformanceLinie} /><br />
<img src={$bscs.info.pers.$persNr.PerformancePie} /><br />



<h3>Daten der Perspektive "{$perspective.Name}"</h3>
<table>
<center>
<tr>
	<th>Datum</th>
	<th>Wert</th>
	<th>Gewichtung</th>
</tr>
	{foreach from=$bscs.sc_data item=sc key=scNum}
	<tr class="{cycle values="odd,even"}">
	<td>{$bscs.sc_data.$scNum.pers_data.$persNr.Datum}</td>
	<td>{$bscs.sc_data.$scNum.pers_data.$persNr.Performance|round:2}</td>
	<td>{$bscs.sc_data.$scNum.pers_data.$persNr.Gewichtung}</td>
	</tr>
	{/foreach}
</center>
</table>

{foreach from=$bscs.sc_data.0.pers_data.$persNr.ind_data item=indicator key=indNr}
<h3>{$indicator.Name}</h3>

<table>
<center>
<tr>
	<th>Name</th>
	<th>Start-Wert</th>
	<th>End-Wert</th>
	<th>Differenz</th>
	<th>Einheit</th>
	<th>Richtung</th>
</tr>
<tr>
	<td>{$indicator.Name}</td>
	<td>{$bscs.info.pers.$persNr.ind.$indNr.start_perf|round:2}</td>
	<td>{$bscs.info.pers.$persNr.ind.$indNr.end_perf|round:2}</td>
	<td>{$bscs.info.pers.$persNr.ind.$indNr.dynamic|round:2}</td>
	<td>{$indicator.Einheit}</td>
	<td>{$indicator.Richtung}</td>
</tr>
</center>
</table>

Daten des Indikators "{$indicator.Name}" <br>
<table>
<center>
<tr>
	<th>Datum</th>
	<th>Gewichtung</th>
	<th>Min</th>
	<th>Max</th>
	<th>Wert</th>	
</tr>
	{foreach from=$bscs.sc_data item=sc key=scNum}
	<tr  class="{cycle values="odd,even"}">
	<td>{$bscs.sc_data.$scNum.pers_data.$persNr.ind_data.$indNr.Datum}</td>
	<td>{$bscs.sc_data.$scNum.pers_data.$persNr.ind_data.$indNr.Gewichtung}</td>
	<td>{$bscs.sc_data.$scNum.pers_data.$persNr.ind_data.$indNr.Min}</td>
	<td>{$bscs.sc_data.$scNum.pers_data.$persNr.ind_data.$indNr.Max}</td>
	<td>{$bscs.sc_data.$scNum.pers_data.$persNr.ind_data.$indNr.Performance|round:2}</td>
	</tr>
	{/foreach}
</center>
</table>

{/foreach}




	</div>
{/foreach}



</div><!-- end float left -->

<div id="report_menue" class="box g25p">
<form method="post">
<input name="page" type="hidden" value="report">
<div class="startdtm">
<p>Bericht erstellen vom</p>
<p>Startdatum (min {report->beginning}):<br /> <input id="datepicker" type="text" name="Start_Datum"></p>
</div>

<div class="enddtm">
<p>bis zum</p>
<p>Enddatum:<br /> <input id="datepicker2" type="text" name="End_Datum"></p>
</div>
	
	
	<input type="submit" value="html" name="type" />
</form>
</div>

<div id="report" class="box g25p">
<h3>Bericht beinhaltet:</h3> {report->timespan|round:0} Tag(e)<br />
vom: {report->start_date} <br />
bis {report->end_date}

	<h3>Name:</h3>{$bscs.sc_data.0.Name}
	<h3>Start-Wert:</h3> {$bscs.info.sc.0.start_perf|round:2}
	<h3>End-Wert:</h3> {$bscs.info.sc.0.end_perf|round:2}
	<h3>Differenz:</h3> {$bscs.info.sc.0.dynamic|round:2}
	<h3>Perspektiven:</h3>{foreach from=$bscs.sc_data.0.pers_data item=perspective}
				<a href="#{$perspective.Name}">
				{$perspective.Name}<br />
				</a>
			{/foreach}

</div>

{include file="footer.tpl"}
