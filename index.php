<?php
	session_start();

	require('libs/helper/helper.inc.php');

    // delete temporary files
    Putzteufel();

	$smarty = new Smarty();

	// login request
	if (isSet($_POST['action']) && $_POST['action'] === 'login')
        CheckUserLogin($db, $_POST['user'], $_POST['pw'], session_id());

	// validate user session
	$log_status = CheckUserSession($db, session_id() );

	// not logged in
	if ($log_status == false)
	{
		$smarty->assign('title', 'BSC - Login');
		$smarty->display('login.tpl');
	}
	// logged in
	else
	{
        // user data
		$smarty->assign('name', $log_status['Name']);
		$smarty->assign('surname', $log_status['Surname']);

		// recieve available scorecards
        $list = Scorecard::GetScorecards($db);

		// bind bsc to session (use first bsc if available)
		if (!isSet($_SESSION['chosen_bsc']) && count($list) > 0)
			$_SESSION['chosen_bsc'] = $list[0]['bsc_id'];

		// bind date to session
		if (!isSet($_SESSION['chosen_date']))
			$_SESSION['chosen_date'] = date('d.m.Y');

		$smarty->assign('bsclist', $list);
		$smarty->assign('date', $_SESSION['chosen_date']);



		/***************
		* REQUEST TREE *
		***************/
		/**********************************
		* HTTP POST REQUESTS (ohne Login) *
		**********************************/

		// GLOBAL POST REQUESTS
		if (isSet($_POST['action']))
		{
			if ($_POST['action'] === 'change_date')
			{
				if (isSet($_POST['date']))
					$_SESSION['chosen_date'] = $_POST['date'];

				header('Location: '.$server_path.'/index.php?page=details');
			}
			else if ($_POST['action'] === 'change_bsc')
			{
				// create a new scorecard
				if ($_POST['bsc_id'] === 'new')
				{
					$bsc = new Scorecard($db);
					$bsc->Save();

					// set chosen date to beginning of new scorecard
					$_SESSION['chosen_date'] = $bsc->GetBeginning();

					$_POST['bsc_id'] = $bsc->GetIdentifier();
				}

				$_SESSION['chosen_bsc'] = $_POST['bsc_id'];

				header('Location: '.$server_path.'/index.php?page=details');
			}
			else if ($_POST['action'] === 'save_bsc')
			{
				$bsc = new Scorecard($db, $_SESSION['chosen_bsc'], $_SESSION['chosen_date']);
				$bsc->SetName($_POST['name']);
				$bsc->SetDescription($_POST['desc']);
				$bsc->Save();

				// in case a new scorecard was created
				$_SESSION['chosen_bsc'] = $bsc->GetIdentifier();

				header('Location: '.$server_path.'/index.php?page=details');
			}
			else if ($_POST['action'] === 'save_per')
			{
				if (isSet($_POST['id_per']) && strLen($_POST['id_per']) > 0)
					$per = new Perspective($db, $_POST['id_per'], null, $_SESSION['chosen_date']);
				else
					$per = new Perspective($db, null, $_SESSION['chosen_bsc'], $_SESSION['chosen_date']);

				$per->SetName($_POST['name']);
				$per->SetWeight($_POST['weight']);
				$per->SetDescription($_POST['desc']);
				$per->Save();

				header('Location: '.$server_path.'/index.php?page=details');
			}
			else if ($_POST['action'] === 'save_ind')
			{
				if (isSet($_POST['id_ind']) && strLen($_POST['id_ind']) > 0)
					$ind = new Indicator($db, $_POST['id_ind'], null, $_SESSION['chosen_date']);
				else
					$ind = new Indicator($db, null, $_POST['id_per'], $_SESSION['chosen_date']);

				$ind->SetName($_POST['name']);
				$ind->SetDescription($_POST['desc']);
				$ind->SetWeight($_POST['weight']);
				$ind->SetMin($_POST['min']);
				$ind->SetMax($_POST['max']);
				$ind->SetBase($_POST['baseline']);
				$ind->SetTarget($_POST['target']);
				$ind->SetUnit($_POST['unit']);
				$ind->SetValue($_POST['value']);
				$ind->SetMaximize($_POST['optimization']);
				$ind->Save();

				header('Location: '.$server_path.'/index.php?page=details');
			}
		}

		/********************
		* HTTP GET REQUESTS *
		********************/

		// GLOBAL GET REQUESTS
		if ($_GET['action'] === 'logout')
		{
			UserLogout($db, session_id() );

			header('Location: '.$server_path.'/');
		}
		// PAGE GET REQUESTS
		else if ($_GET['page'] === 'details')
		{
			$smarty->assign('title', 'BSC - Details');

			$bsc = new Scorecard($db, $_SESSION['chosen_bsc'], $_SESSION['chosen_date']);
			$bsc->Save();

			// bind scorecard object
			$smarty->assign_by_ref('bsc', $bsc);

			if (isSet($_GET['action']))
			{
				// BSC Requests
				if ($_GET['action'] === 'edit_bsc')
				{
					$smarty->assign('form_bsc', true);
				}
				else if ($_GET['action'] === 'del_bsc')
				{
					$bsc->Delete();

					// clean session entry
					unSet($_SESSION['chosen_bsc']);

					header('Location: '.$server_path.'/index.php?page=details');
				}
				// Perspective Requests
				else if ($_GET['action'] === 'add_per')
				{
					$smarty->assign('form_perspective', true);
					$smarty->assign_by_ref('perspective', new Perspective($db, null, $_SESSION['chosen_bsc']));
				}
				else if ($_GET['action'] === 'edit_per')
				{
					if ( isSet($_GET['id']) )
					{
						$smarty->assign('form_perspective', true);
						$smarty->assign_by_ref('perspective', new Perspective($db, $_GET['id'], null, $_SESSION['chosen_date']));
					}
				}
				else if ($_GET['action'] === 'del_per')
				{
					if ( isSet($_GET['id']) )
					{
						$pers = new Perspective($db, $_GET['id']);
						$pers->Delete();

						header('Location: '.$server_path.'/index.php?page=details');
					}
				}
				// Indicator Requests
				else if ($_GET['action'] === 'add_ind')
				{
					if ( isSet($_GET['id']) )
					{
						$smarty->assign('form_indicator', true);
						$smarty->assign_by_ref('indicator', new Indicator($db, null, $_GET['id']));
					}
				}
				else if ($_GET['action'] === 'edit_ind')
				{
					if ( isSet($_GET['id']) )
					{
						$smarty->assign('form_indicator', true);
						$smarty->assign_by_ref('indicator', new Indicator($db, $_GET['id'], null, $_SESSION['chosen_date']));
					}
				}
				else if ($_GET['action'] === 'del_ind')
				{
					if ( isSet($_GET['id']) )
					{
						$ind = new Indicator($db, $_GET['id']);
						$ind->Delete();

						header('Location: '.$server_path.'/index.php?page=details');
					}
				}
			}

			$smarty->display('details.tpl');
		}
		else if ($_GET['page'] === 'report')
		{
			// if no scorecard is chosen return to details
			if (!isSet($_SESSION["chosen_bsc"]))
			{
				header('Location: ' . $server_path.'/index.php?page=details');
			}
			else
			{
				$smarty->assign('title', 'BSC - Bericht');

				$bsc = new Scorecard($db, $_SESSION['chosen_bsc'], $_SESSION['chosen_date']);

				$smarty->assign_by_ref('bsc', $bsc);

				if(!isSet($_POST['Start_Datum']) || !isSet($_POST['End_Datum']) || $_POST['Start_Datum']== "" || $_POST['Start_Datum']==null || $_POST['End_Datum']=="" || $_POST['End_Datum']==null )
				{
					$date_to = date('d.m.Y');
					$date_from = date('d.m.Y' ,strtotime( '-7 day'.date('d.m.Y')));
				}
				else
				{
					$datetime1 = strtotime($_POST['Start_Datum']);
					$datetime2 = strtotime($_POST['End_Datum']);
					if($datetime2 < $datetime1)
					{
						$temp=$_POST['Start_Datum'];
						$temp2=$_POST['End_Datum'];
						$date_to=$_POST['Start_Datum'];
						$date_from =$temp2;
					}
					else
					{
						$date_to = $_POST['End_Datum'];
						$date_from = $_POST['Start_Datum'];
					}
					//$date_to = $_POST['End_Datum'];
					//$date_from = $_POST['Start_Datum'];
				}

				//$date_to = date('Y-m-d');
				//$date_from = date('Y-m-d' ,strtotime( '-7 day'.date('Y-m-d')));

				$reporttest = new buildReport($db, $bsc->GetIdentifier(), $date_from, $date_to);
				$smarty->register_object('report', $reporttest);

				if ($_POST['type']=="html")
				{

				}
				elseif ($_POST['type']=="pdf")
				{
					$pdftest = new buildPDF($reporttest);
				}

				$smarty->display('report.tpl');
			}
		}
		else if ($_GET['page'] === 'lightbox')
		{
			if ($_GET['action'] == 'dia_bsc')
			{
				$act_bsc=$_GET['id_bsc'];

				if(!isSet($_POST['Start_Datum']) || $_POST['Start_Datum']=="" || $_POST['Start_Datum']==null)
				{
					$st_date=$_SESSION['chosen_date'];
				}
				else
				{
                                    if(!isSet($_POST['End_Datum']) || $_POST['End_Datum']=="" || $_POST['End_Datum']==null)
                                    {
                                    $st_date=$_POST['Start_Datum'];
                                    }
                                    else
                                    {
                                        $datetime1 = strtotime($_POST['Start_Datum']);
                                        $datetime2 = strtotime($_POST['End_Datum']);
                                        if($datetime2<$datetime1)
                                        {
                                            $temp=$_POST['Start_Datum'];
                                            $temp2=$_POST['End_Datum'];
                                            $_POST['End_Datum']=$_POST['Start_Datum'];
                                            $st_date=$temp2;

                                        }
                                        else
                                        {
                                           $st_date=$_POST['Start_Datum'];
                                        }
                                    }
				}

				if (!isSet($_POST['dia_type']) || $_POST['dia_type']=="Fortschritt")
				{
					if(!isSet($_POST['End_Datum']) || $_POST['End_Datum']=="" || $_POST['End_Datum']==null)
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc);
						$smarty->assign('dia', $diagram->drawBscProgress());
					}
					else
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc);
						$smarty->assign('dia', $diagram->drawBscProgress_timespan($_POST['End_Datum']));
					}
				}
				elseif ($_POST['dia_type']=="Effizienz")
				{
					if(!isSet($_POST['End_Datum']) || $_POST['End_Datum']=="" || $_POST['End_Datum']==null)
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc);
						$smarty->assign('dia', $diagram->drawBscPerformance());
					}
					else
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc);
						$smarty->assign('dia', $diagram->drawBscPerformance_timespan($_POST['End_Datum']));
					}
				}
				elseif ($_POST['dia_type']=="Gewichtung")
				{
				   if(!isSet($_POST['End_Datum']) || $_POST['End_Datum']=="" || $_POST['End_Datum']==null)
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc);
						$smarty->assign('dia', $diagram->drawBscWeight());
					}
					else
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc);
						$smarty->assign('dia', $diagram->drawBscWeight_timespan($_POST['End_Datum']));
					}
				}
				elseif ($_POST['dia_type']=="Radar")
				{
					$diagram = new Diagramm($db, $st_date, $act_bsc);
					$smarty->assign('dia', $diagram->drawBscRadar());
				}

				$bsc = new Scorecard($db, $_SESSION["chosen_bsc"]);
                                $smarty->assign('beginning', $bsc->GetBeginning());
                                $smarty->assign('stdate', $st_date);
				$smarty->register_object('bsc', $bsc);
				$smarty->display('diabsc.tpl');
			}
			elseif ($_GET['action'] == 'dia_pers')
			{

                            $act_bsc=$_GET['id_bsc'];
                            $act_pers=$_GET['id_pers'];

                           if(!isSet($_POST['Start_Datum']) || $_POST['Start_Datum']=="" || $_POST['Start_Datum']==null)
				{
					$st_date=$_SESSION['chosen_date'];
				}
				else
				{
					 if(!isSet($_POST['End_Datum']) || $_POST['End_Datum']=="" || $_POST['End_Datum']==null)
                                    {
                                    $st_date=$_POST['Start_Datum'];
                                    }
                                    else
                                    {
                                        $datetime1 = strtotime($_POST['Start_Datum']);
                                        $datetime2 = strtotime($_POST['End_Datum']);
                                        if($datetime2<$datetime1)
                                        {
                                            $temp=$_POST['Start_Datum'];
                                            $temp2=$_POST['End_Datum'];
                                            $_POST['End_Datum']=$_POST['Start_Datum'];
                                            $st_date=$temp2;
                                        }
                                        else
                                        {
                                           $st_date=$_POST['Start_Datum'];
                                        }
                                    }
				}

                            if (!isSet($_POST['dia_type']) || $_POST['dia_type']=="Fortschritt")
				{
					if(!isSet($_POST['End_Datum']) || $_POST['End_Datum']=="" || $_POST['End_Datum']==null)
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers);
						$smarty->assign('dia', $diagram->drawPersProgress());
					}
					else
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers);
						$smarty->assign('dia', $diagram->drawPersProgress_timespan($_POST['End_Datum']));
					}
				}
                           elseif ($_POST['dia_type']=="Effizienz")
				{
					if(!isSet($_POST['End_Datum']) || $_POST['End_Datum']=="" || $_POST['End_Datum']==null)
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers);
						$smarty->assign('dia', $diagram->drawPersPerformance());
					}
					else
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers);
						$smarty->assign('dia', $diagram->drawPersPerformance_timespan($_POST['End_Datum']));
					}
				}
                            elseif ($_POST['dia_type']=="Gewichtung")
				{
				   if(!isSet($_POST['End_Datum']) || $_POST['End_Datum']=="" || $_POST['End_Datum']==null)
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers);
						$smarty->assign('dia', $diagram->drawPersWeight());
					}
					else
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers);
						$smarty->assign('dia', $diagram->drawPersWeight_timespan($_POST['End_Datum']));
					}
				}
                            elseif ($_POST['dia_type']=="Radar")
				{
					$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers);
					$smarty->assign('dia', $diagram->drawPersRadar());
				}

                           $bsc = new Scorecard($db, $_SESSION["chosen_bsc"]);
                           $pers = new Perspective($db, $act_pers, $_SESSION["chosen_bsc"], $st_date);
                           $smarty->assign('beginning', $bsc->GetBeginning());
                           $smarty->assign('stdate', $st_date);
                           $smarty->register_object('bsc', $bsc);
                           $smarty->register_object('pers', $pers);
                           $smarty->display('diapers.tpl');
			}
                        elseif ($_GET['action'] == 'dia_indi')
			{

                            $act_bsc=$_GET['id_bsc'];
                            $act_pers=$_GET['id_pers'];
                            $act_indi=$_GET['id_indi'];

                             if(!isSet($_POST['Start_Datum']) || $_POST['Start_Datum']=="" || $_POST['Start_Datum']==null)
				{
					$st_date=$_SESSION['chosen_date'];
				}
				else
				{
					 if(!isSet($_POST['End_Datum']) || $_POST['End_Datum']=="" || $_POST['End_Datum']==null)
                                    {
                                    $st_date=$_POST['Start_Datum'];
                                    }
                                    else
                                    {
                                        $datetime1 = strtotime($_POST['Start_Datum']);
                                        $datetime2 = strtotime($_POST['End_Datum']);
                                        if($datetime2<$datetime1)
                                        {
                                            $temp=$_POST['Start_Datum'];
                                            $temp2=$_POST['End_Datum'];
                                            $_POST['End_Datum']=$_POST['Start_Datum'];
                                            $st_date=$temp2;
                                        }
                                        else
                                        {
                                           $st_date=$_POST['Start_Datum'];
                                        }
                                    }
				}

                            if (!isSet($_POST['dia_type']) || $_POST['dia_type']=="Wertung")
				{
					if(!isSet($_POST['End_Datum']) || $_POST['End_Datum']=="" || $_POST['End_Datum']==null)
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers, $act_indi);
						$smarty->assign('dia', $diagram->drawIndiWert());
					}
					else
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers, $act_indi);
						$smarty->assign('dia', $diagram->drawIndiWert_timespan($_POST['End_Datum']));
					}
				}
                                elseif ($_POST['dia_type']=="Effizienz")
				{
                                 if(!isSet($_POST['End_Datum']) || $_POST['End_Datum']=="" || $_POST['End_Datum']==null)
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers, $act_indi);
						$smarty->assign('dia', $diagram->drawIndiPerf());
					}
					else
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers, $act_indi);
						$smarty->assign('dia', $diagram->drawIndiPerf_timespan($_POST['End_Datum']));
					}
                                }
                                elseif ($_POST['dia_type']=="Gewichtung")
				{
                                 if(!isSet($_POST['End_Datum']) || $_POST['End_Datum']=="" || $_POST['End_Datum']==null)
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers, $act_indi);
						$smarty->assign('dia', $diagram->drawIndiWeight());
					}
					else
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers, $act_indi);
						$smarty->assign('dia', $diagram->drawIndiWeight_timespan($_POST['End_Datum']));
					}
                                }
                                elseif ($_POST['dia_type']=="Fortschritt")
				{
                                 if(!isSet($_POST['End_Datum']) || $_POST['End_Datum']=="" || $_POST['End_Datum']==null)
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers, $act_indi);
						$smarty->assign('dia', $diagram->drawIndiFort());
					}
					else
					{
						$diagram = new Diagramm($db, $st_date, $act_bsc, $act_pers, $act_indi);
						$smarty->assign('dia', $diagram->drawIndiFort_timespan($_POST['End_Datum']));
					}
                                }
                           $bsc = new Scorecard($db, $_SESSION["chosen_bsc"]);
                           $pers = new Perspective($db, $act_pers, $_SESSION["chosen_bsc"], $st_date);
                           $indi = new indicator($db, $act_indi, $act_pers, $st_date);
                           $smarty->assign('beginning', $bsc->GetBeginning());
                           $smarty->assign('stdate', $st_date);
                           $smarty->register_object('bsc', $bsc);
                           $smarty->register_object('pers', $pers);
                           $smarty->register_object('indi', $indi);
                           $smarty->display('diaindi.tpl');
                        }
		}
		else
		{
			header('Location: '.$server_path.'/index.php?page=details');
		}
	}
?>
