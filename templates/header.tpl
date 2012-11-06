<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>{$title}</title>

		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<link rel="stylesheet" type="text/css" href="./css/style.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="./css/jquery-ui-1.8.7.custom.css" media="screen" />
		<link rel="stylesheet" type="text/css" href="./css/thickbox.css" media="screen" />

		<script src="./js/jquery.1.4.4.min.js" type="text/javascript"></script>
		<script src="./js/jquery.ui.core.min.1.8.7.js" type="text/javascript"></script>
		<script src="./js/jquery.ui.datepicker.min.1.8.7.js" type="text/javascript"></script>
		<script src="./js/thickbox.3.min.js" type="text/javascript"></script>

		<script type="text/javascript">
		//<![CDATA[
		{literal}
			$(function() {
				$('.delete').click(function(event) {
					if ( !confirm('Wirklich entfernen?') )
						event.preventDefault();
				});

				$('#caldate').datepicker().datepicker('option', 'minDate', {/literal}'{$bsc->GetBeginning()}'{literal});
			});
		{/literal}
		//]]>
		</script>
	</head>
	<body>
		<div id="wrapper">

			<div id="header">
				<div id="account">
					<table>
						<tr>
							<th>Benutzer:</th>
							<td>{$surname}, {$name}</td>
						</tr>
						<tr>
							<th>Datum:</th>
							<td>{$smarty.now|date_format:'%d.%m.%Y'}</td>
						</tr>
						<tr>
							<th colspan="2"><a title="Logout" href="./index.php?action=logout">Logout</a></th>
						</tr>
					</table>
				</div>

				<img id="logo" src="./images/thw.png" alt="" />

				<div id="navigation">
					<form id="bscselect" action="./" method="post">
						<fieldset>
							<select name="bsc_id" onchange="this.form.submit()">
								<option disabled="disabled" selected="selected">Wählen Sie eine BSC</option>
							{foreach item=scorecard from=$bsclist}
								<option value="{$scorecard.bsc_id}">{$scorecard.bsc_name}</option>
							{/foreach}
								<option disabled="disabled">---------------------</option>
								<option value="new">Neue BSC anlegen</option>
							</select>
							<input type="hidden" name="action" value="change_bsc" />
							<input type="submit" class="pin" value="" />
						</fieldset>
					</form>

					<ul id="menu">
						<li><a href="index.php?page=details">Details</a></li>
						<li><a href="index.php?page=report">Bericht</a></li>
					</ul>

					<form id="cal" action="./" method="post">
						<fieldset>
							<input id="caldate" name="date" type="text" value="{$date}" onchange="this.form.submit()" />
							<input type="hidden" name="action" value="change_date" />
							<input type="submit" class="pin" value="" />
						</fieldset>
					</form>
				</div>
			<!-- /#header -->
			</div>

			<div id="content">
