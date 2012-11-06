<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title></title>

		<link rel="stylesheet" type="text/css" href="/css/style.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="/css/jquery-ui-1.8.7.custom.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="/css/thickbox.css" media="screen" />

		<script src="/js/jquery.1.4.4.min.js" type="text/javascript"></script>
		<script src="/js/jquery.ui.core.min.1.8.7.js" type="text/javascript"></script>
		<script src="/js/thickbox.3.min.js" type="text/javascript"></script>
		<script src="/js/jquery.form.js" type="text/javascript"></script>
		<script src="/js/jquery.ui.datepicker.min.1.8.7.js" type="text/javascript"></script>

		<script type="text/javascript">
		//<![CDATA[
		{literal}
			$(function()
			{
				$('#datepicker').datepicker().datepicker('option', 'minDate', {/literal}'{$beginning}'{literal}).datepicker('option', 'maxDate', '+0d');
				$('#datepicker2').datepicker().datepicker('option', 'minDate', {/literal}'{$beginning}'{literal}).datepicker('option', 'maxDate', '+0d');

				$('#bscForm').submit(function(event)
				{
					$.post($(this).attr('action'), $(this).serialize(), function(html)
					{
						$('#diagram').html(html);
					});

					event.preventDefault();
				});
			});
		{/literal}
		//]]>
		</script>
    </head>
	<body>
		<div id="diagram">
			<form id="bscForm" class="box" name="bscForm" action="./index.php?page=lightbox&action=dia_bsc&id_bsc={bsc->GetIdentifier}" method="post">
				<fieldset>
					<p>BSC: {bsc->GetName}</p>

					<div class="form-group-box">
						<label for="dia_type">Diagrammtyp</label>
						<select id="dia_type" name="dia_type">
							<option value="Fortschritt">Fortschritt</option>
							<option value="Effizienz">Effizienz</option>
							<option value="Radar">Radar</option>
							<option value="Gewichtung">Gewichtung</option>
						</select>
					</div>

					<div class="form-group-box">
						<label for="datepicker">Startdatum</label>
						<input id="datepicker" type="text" name="Start_Datum" value={$stdate}/>
					</div>

					<div class="form-group-box">
						<label for="datepicker2">Enddatum</label>
						<input id="datepicker2" type="text" name="End_Datum" />
					</div>
				</fieldset>
				<fieldset>
					<input type="submit">
				</fieldset>
			</form>

			<img src="{$dia}" alt="" />
		<!-- /#diagram -->
		</div>
	</body>
</html>