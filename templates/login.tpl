<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>{$title}</title>

		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

		<link rel="stylesheet" type="text/css" href="./css/style.css" />
	</head>
	<body onload="document.getElementById('user').focus();">
		<div id="wrapper">

			<div id="header">
				<img id="logo" src="./images/thw.png" alt="" />
			</div>

			<div id="content">

				<form id="login" class="form-box-login" action="./" method="post">
					<fieldset>
						<div class="form-group-box">
							<label for="user">Nutzername</label>
							<input id="user" name="user" type="text" />
						</div>
						<div class="form-group-box">
							<label for="pw">Passwort</label>
							<input id="pw" name="pw" type="password" />
						</div>
                    </fieldset>
					<fieldset>
							<input type="hidden" name="action" value="login" />
							<input type="submit" value="Login" />
					</fieldset>
				</form>

{include file="footer.tpl"}
