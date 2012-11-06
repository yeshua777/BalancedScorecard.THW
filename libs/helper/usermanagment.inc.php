<?php

	function CheckUserLogin($db, $user, $pw, $session)
	{
		$result = $db->prepare('SELECT UserID FROM user WHERE UserName = ? AND UserPassword = MD5(?)');
		$result->bind_param('ss', $user, $pw);
		$result->execute();
		$result->store_result();
		$result->bind_result($UserID);
		$result->fetch();

		if ($result->num_rows != 1)
			return false;

		$result = $db->prepare('UPDATE user SET UserSession = ?, UserLogin = NOW() WHERE UserID = ?');
		$result->bind_param('si', $session, $UserID);
		$result->execute();

		return true;
	}



	function UserLogout($db, $session)
	{
		$result = $db->prepare('UPDATE user SET UserSession = NULL WHERE UserSession = ?');
		$result->bind_param('s', $session);
		$result->execute();
	}



	function CheckUserSession($db, $session)
	{
		$result = $db->prepare('SELECT UserID, Name, Surname, UserName, UserLogin FROM user WHERE UserSession = ?');
		$result->bind_param('s', $session);
		$result->execute();
		$result->store_result();

		if ($result->num_rows != 1)
			return false;

		$result->bind_result($UserID, $Name, $Surname, $UserName, $UserLogin);
		$result->fetch();
		$meta['UserID']    = $UserID;
		$meta['Name']      = $Name;
		$meta['Surname']   = $Surname;
		$meta['UserName']  = $UserName;
		$meta['UserLogin'] = $UserLogin;

		$result = $db->prepare('UPDATE user SET LastClick = NOW() WHERE UserId = ?');
		$result->bind_param('i', $UserID);
		$result->execute();
		$result->free_result();

		return $meta;
	}



	function CheckAdmin($db, $session)
	{
		$result = $db->prepare('SELECT UserID, UserLevel FROM user WHERE UserSession = ?');
		$result->bind_param('s', $session);
		$result->execute();
		$result->store_result();

		if ($result->num_rows != 1)
			return false;

		$result->bind_result($UserID, $UserLevel);
		$result->fetch();
		$result->free_result();

		return $UserLevel == 666;
	}

?>