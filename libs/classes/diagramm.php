<?php
/**
 * @author Tim Raschmann
 * @version 18.11.2010
 */
class Diagramm {



private $dia_type = null;
private $dia_bsc = null;
private $dia_perspective = null;
private $dia_width = null;
private $dia_height = null;
private $date = null;
private $bsc = null;
private $bsc_id = null;
private $pers = null;
private $pers_id = null;
private $indi = null;
private $indi_id = null;
private $db = null;
private $start_date = null;
private $end_date = null;
public function __construct($db = null, $date = null, $id = null, $act_pers = null, $act_indi = null) {

$this->db=$db;
$this->start_date=$date;
$this->bsc_id=$id;
$this->pers_id=$act_pers;
$this->indi_id=$act_indi;
}

public function drawBscProgress()
{
$this->bsc =  new Scorecard($this->db, $this->bsc_id, $this->start_date);
include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

$myData = new pData();
$bsc_progress = $this->bsc->GetProgress();

$myData->addPoints(array(round($bsc_progress)),"Serie1");
$myData->setSerieDescription($this->bsc->GetName(),"Serie 1");
$myData->setSerieOnAxis("Serie1",0);


$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Fortschritt");
$myData->setAxisUnit(0,"%");

$myPicture = new pImage(400,300,$myData);
$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,399,299,array("R"=>214, "G"=>214, "B"=>214));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>138, "G"=>138, "B"=>138);

$myPicture->drawText(200,25,"Fortschritt von ".$this->bsc->GetName()." am $this->start_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,375,260);
$myPicture->setFontProperties(array("R"=>161,"G"=>161,"B"=>161,"FontName"=>"libs/pchart/fonts/Bedizen.ttf","FontSize"=>8));

$Settings = array("Pos"=>690101, "Mode"=>690203, "LabelingMethod"=>691012, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("Gradient"=>1, "DisplayValues"=>1, "Rounded"=>1);
$myPicture->drawBarChart($Config);

$Config = array("R"=>166, "G"=>166, "B"=>166, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "Caption"=>"Threshold");
$myPicture->drawThreshold(0,$Config);

 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;
}
public function drawBscProgress_timespan($enddate)
{
$this->end_date=$enddate;
//umwandeln in unix-zeitstempel um die differentz (timespan) zu ermitteln
$datetime1 = strtotime($this->start_date);
$datetime2 = strtotime($this->end_date);
$this->timespan=($datetime2-$datetime1)/86400;

//umwandeln in DateTime, um Tage Kalndergetreu addieren zu k�nnen
$datetime1 = new DateTime($this->start_date);
$bsc = null;
$array_progress=array();
$array_time=array();
$name =null;
for ($i = 0; $i<=$this->timespan; $i++)
{

        $bsc = new Scorecard($this->db, $this->bsc_id, $datetime1->format('d.m.Y'));
        $array_progress[$i]= round($bsc->GetProgress());

        $name = $bsc->GetName();
        $array_time[$i]=$datetime1->format('d.m.Y');
        //Erhöhung um einen Tag
	$datetime1->add(new DateInterval('P1D'));
}


include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

$myData = new pData();

$myData->addPoints($array_progress,"Serie1");
$myData->setSerieDescription($bsc->GetName(),"Serie 1");
$myData->setSerieOnAxis("Serie1",0);

$myData->addPoints($array_time,"Absissa");
$myData->setAbscissa("Absissa");

$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Fortschritt");
$myData->setAxisUnit(0,"%");

$myPicture = new pImage(600,300,$myData);
$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,599,299,array("R"=>214, "G"=>214, "B"=>214));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>138, "G"=>138, "B"=>138);

$myPicture->drawText(200,25,"Fortschritt von ".$bsc->GetName()." vom $this->start_date bis $this->end_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,575,260);
$myPicture->setFontProperties(array("R"=>161,"G"=>161,"B"=>161,"FontName"=>"libs/pchart/fonts/Bedizen.ttf","FontSize"=>8));

$Settings = array("Pos"=>690101, "Mode"=>690203, "LabelingMethod"=>691012, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("Gradient"=>1, "DisplayValues"=>1, "Rounded"=>1, "DisplayPos"=>LABEL_POS_INSIDE);
$myPicture->drawBarChart($Config);

$Config = array("R"=>166, "G"=>166, "B"=>166, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "Caption"=>"Threshold");
$myPicture->drawThreshold(0,$Config);

 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;
}

public function drawBscPerformance()
{
  $this->bsc =  new Scorecard($this->db, $this->bsc_id, $this->start_date);
  $name = $this->bsc->GetName();

include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pPie.class");
include_once("libs/pchart/class/pImage.class");

  $temp = $this->bsc->GetPerspectives();
  $array_performance=array();
  $array_name=array();
  $sum="0";
  $i="0";
  foreach($temp as $perspektive)
  {
      $array_performance[$i] = round($perspektive->GetPerformance());
      $sum = $sum+$array_performance[$i];
      $array_name[$i] = $perspektive->GetName()." - ".round($perspektive->GetPerformance())."%";
      $i++;
  }

/* Create and populate the pData object */
$MyData = new pData();
$MyData->addPoints($array_performance,"ScoreA");
$MyData->setSerieDescription("ScoreA","Application A");

/* Define the absissa serie */
$MyData->addPoints($array_name,"Labels");
$MyData->setAbscissa("Labels");

/* Create the pChart object */
$myPicture = new pImage(600,300,$MyData);




/* Add a border to the picture */
$myPicture->drawRectangle(0,0,599,299,array("R"=>214, "G"=>214, "B"=>214));

/* Write the picture title */
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/MankSans.ttf","FontSize"=>14));
$myPicture->drawText(14,25,"Effizienz von ".$this->bsc->GetName()." am ".$this->start_date."",array("R"=>0,"G"=>0,"B"=>0));

/* Set the default font properties */
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>12,"R"=>80,"G"=>80,"B"=>80));

/* Create the pPie object */
$PieChart = new pPie($myPicture,$MyData);

/* Define the slice color */
$PieChart->setSliceColor(0,array("R"=>143,"G"=>197,"B"=>0));
$PieChart->setSliceColor(1,array("R"=>97,"G"=>177,"B"=>63));
$PieChart->setSliceColor(2,array("R"=>97,"G"=>113,"B"=>63));


/* Draw an AA pie chart */
$PieChart->draw3DPie(300,160,array("DrawLabels"=>TRUE,"Border"=>FALSE, "Radius"=>120, "WriteValues"=>FALSE));

/* Enable shadow computing */
$myPicture->setShadow(TRUE,array("X"=>3,"Y"=>3,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));


/* Write the legend */
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/MankSans.ttf","FontSize"=>11));
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));
$myPicture->drawText(140,250,"Gesamt BSC Effizienz: ".round($this->bsc->GetProgress())."%",array("R"=>0,"G"=>0,"B"=>0,"Align"=>TEXT_ALIGN_TOPMIDDLE));

  $File = null;
  $File = "temp/".time()."".rand(5, 500).".png";
//Render the picture (choose the best way) */
$myPicture->Render($File);
  return $File;
}

public function drawBscPerformance_timespan($enddate)
{
  $this->end_date=$enddate;
//umwandeln in unix-zeitstempel um die differentz (timespan) zu ermitteln
$datetime1 = strtotime($this->start_date);
$datetime2 = strtotime($this->end_date);
$this->timespan=($datetime2-$datetime1)/86400;
//umwandeln in DateTime, um Tage Kalndergetreu addieren zu k�nnen
$datetime1 = new DateTime($this->start_date);
$bsc = null;
$array_Performance=array();
$array_name=array();
$array_time=array();
for ($i = 0; $i<=$this->timespan; $i++)
{
        $j="0";
        $this->bsc = new Scorecard($this->db, $this->bsc_id, $datetime1->format('d.m.Y'));
        $temp = $this->bsc->GetPerspectives();

        foreach($temp as $perspektive)
        {
        $array_Performance[$j][$i]= round($perspektive->GetPerformance());
        $array_name[$j] = $perspektive->GetName();
        $j++;
        }
        $array_time[$i]=$datetime1->format('d.m.Y');
        //Erhöhung um einen Tag
	$datetime1->add(new DateInterval('P1D'));

}

$name = $this->bsc->GetName();
// Standard inclusions
include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

$myData = new pData();
        for ($i = 0; $i<count($array_name); $i++)
        {
        $myData->addPoints($array_Performance[$i],$array_name[$i]);
        $myData->setSerieDescription($array_name[$i],$array_name[$i]);
        $myData->setSerieOnAxis($array_name[$i],0);
        }

$myData->addPoints($array_time,"Tage");
$myData->setAbscissa("Tage");

$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Effizienz");
$myData->setAxisUnit(0,"%");

$myPicture = new pImage(700,300,$myData);
$Settings = array("R"=>232, "G"=>232, "B"=>232, "Dash"=>1, "DashR"=>252, "DashG"=>252, "DashB"=>252);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,699,299,array("R"=>0,"G"=>0,"B"=>0));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>56, "G"=>56, "B"=>56);
$myPicture->drawText(205,25,"Effizienz von ".$this->bsc->GetName()." zwischen $this->start_date und $this->end_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,675,250);
$myPicture->setFontProperties(array("R"=>0,"G"=>0,"B"=>0,"FontName"=>"libs/pchart/fonts/MankSans.ttf","FontSize"=>9));

$Settings = array("Pos"=>690101, "Mode"=>690201, "LabelingMethod"=>691011, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("DisplayValues"=>TRUE);
$myPicture->drawSplineChart($Config);

$Config = array("R"=>0, "G"=>0, "B"=>0, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "WriteCaption"=>1, "Caption"=>"Threshold", "DrawBox"=>1);
$myPicture->drawThreshold(0,$Config);

$Config = array("FontR"=>0, "FontG"=>0, "FontB"=>0, "FontName"=>"libs/pchart/fonts/MankSans.ttf", "FontSize"=>8, "Margin"=>6, "Alpha"=>30, "BoxSize"=>5, "Style"=>690800, "Mode"=>690902);
$myPicture->drawLegend(47,286,$Config);

  $File = null;
  $File = "temp/".time()."".rand(5, 500).".png";
  $myPicture->Render($File);
  return $File;      
}

public function drawBscWeight()
{
$this->bsc =  new Scorecard($this->db, $this->bsc_id, $this->start_date);
$name = $this->bsc->GetName();

include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

// Dataset definition
$MyData = new pData();

  $temp = $this->bsc->GetPerspectives();
  $array_weight=array();
  $array_name=array();
  $sum="0";
  $i="0";
  $average2=array();
  $average=null;
  $array_werte=array();
  foreach($temp as $perspektive)
  {
      $array_name[$i] = $perspektive->GetName();
      $array_werte[$i] = round($perspektive->GetWeight());
        $i++;
}
/* Create and populate the pData object */
$MyData->addPoints($array_werte,"Gewichtung");
$MyData->setAxisName(0,"Gewichtung");
$MyData->addPoints($array_name,"Perspektiven");
$MyData->setSerieDescription("Perspektiven","Perspektiven");
$MyData->setAbscissa("Perspektiven");
 
/* Create the pChart object */
$myPicture = new pImage(600,350,$MyData);
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/MankSans.ttf","FontSize"=>9));
 
/* Draw the chart scale */
$myPicture->setGraphArea(150,30,580,330);
$myPicture->drawScale(array("CycleBackground"=>TRUE,"DrawSubTicks"=>TRUE,"GridR"=>0,"GridG"=>0,"GridB"=>0,"GridAlpha"=>10,"Pos"=>SCALE_POS_TOPBOTTOM)); //
 
/* Turn on shadow computing */
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 
/* Draw the chart */
$myPicture->drawBarChart(array("DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>TRUE,"Rounded"=>TRUE,"Surrounding"=>30));
 

  $File = "temp/".time()."".rand(5, 500).".png";
  $myPicture->Render($File);
  return $File;

}
public function drawBscWeight_timespan($enddate)
{
$this->end_date=$enddate;
//umwandeln in unix-zeitstempel um die differentz (timespan) zu ermitteln
$datetime1 = strtotime($this->start_date);
$datetime2 = strtotime($this->end_date);
$this->timespan=($datetime2-$datetime1)/86400;
//umwandeln in DateTime, um Tage Kalndergetreu addieren zu k�nnen
$datetime1 = new DateTime($this->start_date);
$bsc = null;
$array_gewichtung=array();
$array_name=array();
for ($i = 0; $i<=$this->timespan; $i++)
{
        $j="0";
        $this->bsc = new Scorecard($this->db, $this->bsc_id, $datetime1->format('d.m.Y'));
        $temp = $this->bsc->GetPerspectives();
        foreach($temp as $perspektive)
        {

        $array_gewichtung[$j][$i]= $perspektive->GetWeight();
        $array_name[$j] = $perspektive->GetName();
        $j++;
        }

        //Erhöhung um einen Tag
	$datetime1->add(new DateInterval('P1D'));
}

$name = $this->bsc->GetName();
// Standard inclusions
include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

$myData = new pData();
 
        for ($i = 0; $i<count($array_name); $i++)
        {
        $myData->addPoints($array_gewichtung[$i],$array_name[$i]);
        $myData->setSerieDescription($array_name[$i],$array_name[$i]);
        $myData->setSerieOnAxis($array_name[$i],0);
        }


$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Gewichtung");
$myData->setAxisUnit(0,"");

$myPicture = new pImage(600,300,$myData);
$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,599,299,array("R"=>214, "G"=>214, "B"=>214));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>138, "G"=>138, "B"=>138);

$myPicture->drawText(200,25,"Gewichtung von ".$this->bsc->GetName()." zwischen $this->start_date und $this->end_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,575,260);
$myPicture->setFontProperties(array("R"=>161,"G"=>161,"B"=>161,"FontName"=>"libs/pchart/fonts/Bedizen.ttf","FontSize"=>8));

$Settings = array("Pos"=>690101, "Mode"=>690203, "LabelingMethod"=>691012, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("Gradient"=>1, "DisplayValues"=>1, "Rounded"=>1);
$myPicture->drawBarChart($Config);

$Config = array("R"=>166, "G"=>166, "B"=>166, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "Caption"=>"Threshold");
$myPicture->drawThreshold(0,$Config);
$myPicture->drawLegend(50,280,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;

}

public function drawBscRadar()
{
$this->bsc =  new Scorecard($this->db, $this->bsc_id, $this->start_date);
$name = $this->bsc->GetName();

// Standard inclusions
include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pRadar.class");
include_once("libs/pchart/class/pImage.class");

  $temp = $this->bsc->GetPerspectives();
  $array_performance=array();
  $array_name=array();

  $i="0";
  foreach($temp as $perspektive)
  {
      $array_performance[$i] = round($perspektive->GetPerformance());
      $array_name[$i] = $perspektive->GetName()." - w(".$perspektive->GetWeight().")";
      $i++;
  }

/* Prepare some nice data & axis config */
$MyData = new pData();
$MyData->addPoints($array_performance,"ScoreA");
$MyData->setSerieDescription("ScoreA",$this->bsc->GetName());

/* Create the X serie */
$MyData->addPoints($array_name,"Labels");
$MyData->setAbscissa("Labels");

/* Create the pChart object */
$myPicture = new pImage(400,300,$MyData);

/* Do some cosmetics */

$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

/* Draw the top bar */

$myPicture->drawRectangle(0,0,399,229,array("R"=>255,"G"=>255,"B"=>255));
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Silkscreen.ttf","FontSize"=>8));
$myPicture->drawText(10,13,"pRadar - Draw radar charts",array("R"=>255,"G"=>255,"B"=>255));

/* Define general drawing parameters */
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/MankSans.ttf","FontSize"=>10,"R"=>80,"G"=>80,"B"=>80));
$myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

/* Create the radar object */
$SplitChart = new pRadar();

/* Draw the 1st radar chart */
$myPicture->setGraphArea(10,25,340,295);
$Options = array("Layout"=>RADAR_LAYOUT_STAR,"BackgroundGradient"=>array("StartR"=>255,"StartG"=>255,"StartB"=>255,"StartAlpha"=>50,"EndR"=>32,"EndG"=>109,"EndB"=>174,"EndAlpha"=>30));
$SplitChart->drawRadar($myPicture,$MyData,$Options);


/* Write down the legend */
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/MankSans.ttf","FontSize"=>8));
$myPicture->drawLegend(40,40,array("Style"=>LEGEND_BOX,"Mode"=>LEGEND_HORIZONTAL));

  $File = "temp/".time()."".rand(5, 500).".png";
  $myPicture->Render($File);
  return $File;
}

public function drawPersProgress()
{
$this->pers =  new Perspective($this->db, $this->pers_id, $this->bsc_id, $this->start_date);
include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

 // Dataset definition
$myData = new pData();
$pers_progress = $this->pers->GetProgress();

$myData->addPoints(array(round($pers_progress)),"Fortschritt");
$myData->setSerieDescription($this->pers->GetName(),"Serie 1");
$myData->setSerieOnAxis("Fortschritt",0);


$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Fortschritt");
$myData->setAxisUnit(0,"%");

$myPicture = new pImage(400,300,$myData);
$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,399,299,array("R"=>214, "G"=>214, "B"=>214));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>138, "G"=>138, "B"=>138);

$myPicture->drawText(200,25,"Fortschritt von ".$this->pers->GetName()." am $this->start_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,375,260);
$myPicture->setFontProperties(array("R"=>161,"G"=>161,"B"=>161,"FontName"=>"libs/pchart/fonts/Bedizen.ttf","FontSize"=>8));

$Settings = array("Pos"=>690101, "Mode"=>690203, "LabelingMethod"=>691012, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("Gradient"=>1, "DisplayValues"=>1, "Rounded"=>1);
$myPicture->drawBarChart($Config);

$Config = array("R"=>166, "G"=>166, "B"=>166, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "Caption"=>"Threshold");
$myPicture->drawThreshold(0,$Config);

 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;
}
public function drawPersProgress_timespan($enddate)
{
$this->end_date=$enddate;
//umwandeln in unix-zeitstempel um die differentz (timespan) zu ermitteln
$datetime1 = strtotime($this->start_date);
$datetime2 = strtotime($this->end_date);
$this->timespan=($datetime2-$datetime1)/86400;
//umwandeln in DateTime, um Tage Kalndergetreu addieren zu k�nnen
$datetime1 = new DateTime($this->start_date);
$bsc = null;
$array_progress=array();
$array_time=array();
$name =null;
for ($i = 0; $i<=$this->timespan; $i++)
{

        $this->pers = new Perspective($this->db, $this->pers_id, $this->bsc_id, $datetime1->format('d.m.Y'));
        $array_progress[$i]= round($this->pers->GetProgress());
        $name = $this->pers->GetName();
        $array_time[$i]=$datetime1->format('d.m.Y');
        //Erhöhung um einen Tag
	$datetime1->add(new DateInterval('P1D'));
}

include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

$myData = new pData();

$myData->addPoints($array_progress,"Fortschritt");
$myData->setSerieDescription($this->pers->GetName(),"Serie 1");
$myData->setSerieOnAxis("Fortschritt",0);

$myData->addPoints($array_time,"Tage");
$myData->setAbscissa("Tage");

$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Fortschritt");
$myData->setAxisUnit(0,"%");

$myPicture = new pImage(600,300,$myData);
$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,599,299,array("R"=>214, "G"=>214, "B"=>214));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>138, "G"=>138, "B"=>138);

$myPicture->drawText(200,25,"Fortschritt von ".$this->pers->GetName()." vom $this->start_date bis $this->end_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,575,260);
$myPicture->setFontProperties(array("R"=>161,"G"=>161,"B"=>161,"FontName"=>"libs/pchart/fonts/Bedizen.ttf","FontSize"=>8));

$Settings = array("Pos"=>690101, "Mode"=>690203, "LabelingMethod"=>691012, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("Gradient"=>1, "DisplayValues"=>1, "Rounded"=>1, "DisplayPos"=>LABEL_POS_INSIDE);
$myPicture->drawBarChart($Config);

$Config = array("R"=>166, "G"=>166, "B"=>166, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "Caption"=>"Threshold");
$myPicture->drawThreshold(0,$Config);

 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;
}
public function drawPersPerformance()
{
  $this->pers = new Perspective($this->db, $this->pers_id, $this->bsc_id, $this->start_date);
  $name = $this->pers->GetName();

include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pPie.class");
include_once("libs/pchart/class/pImage.class");

  $temp = $this->pers->GetIndicators();
  if(count($temp)<1)
  {

  }
  else
  {
  $array_performance=array();
  $array_name=array();
  $sum="0";
  $i="0";
  foreach($temp as $indicator)
  {

      $array_performance[$i] = round($indicator->GetPerformance());
      $sum = $sum+$array_performance[$i];
      $array_name[$i] = $indicator->GetName()." - ".round($indicator->GetPerformance())."%";
      $i++;
  }
/* Create and populate the pData object */
$MyData = new pData();
$MyData->addPoints($array_performance,"ScoreA");
$MyData->setSerieDescription("ScoreA","Application A");

/* Define the absissa serie */
$MyData->addPoints($array_name,"Labels");
$MyData->setAbscissa("Labels");

/* Create the pChart object */
$myPicture = new pImage(600,300,$MyData);




/* Add a border to the picture */
$myPicture->drawRectangle(0,0,599,299,array("R"=>214, "G"=>214, "B"=>214));

/* Write the picture title */
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/MankSans.ttf","FontSize"=>14));
$myPicture->drawText(14,25,"Effizienz von ".$this->pers->GetName()." am ".$this->start_date."",array("R"=>0,"G"=>0,"B"=>0));

/* Set the default font properties */
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>12,"R"=>80,"G"=>80,"B"=>80));

/* Create the pPie object */
$PieChart = new pPie($myPicture,$MyData);

/* Define the slice color */
$PieChart->setSliceColor(0,array("R"=>143,"G"=>197,"B"=>0));
$PieChart->setSliceColor(1,array("R"=>97,"G"=>177,"B"=>63));
$PieChart->setSliceColor(2,array("R"=>97,"G"=>113,"B"=>63));


/* Draw an AA pie chart */
$PieChart->draw3DPie(300,160,array("DrawLabels"=>TRUE,"Border"=>FALSE, "Radius"=>120, "WriteValues"=>FALSE));

/* Enable shadow computing */
$myPicture->setShadow(TRUE,array("X"=>3,"Y"=>3,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));


/* Write the legend */
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/MankSans.ttf","FontSize"=>11));
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>20));
$myPicture->drawText(140,250,"Gesamt Perspektiv Effizienz: ".round($this->pers->GetPerformance())."%",array("R"=>0,"G"=>0,"B"=>0,"Align"=>TEXT_ALIGN_TOPMIDDLE));

  $File = null;
  $File = "temp/".time()."".rand(5, 500).".png";
//Render the picture (choose the best way) */
$myPicture->Render($File);
  return $File;
  }
}
public function drawPersPerformance_timespan($enddate)
{
  $this->end_date=$enddate;
//umwandeln in unix-zeitstempel um die differentz (timespan) zu ermitteln
$datetime1 = strtotime($this->start_date);
$datetime2 = strtotime($this->end_date);
$this->timespan=($datetime2-$datetime1)/86400;
//umwandeln in DateTime, um Tage Kalndergetreu addieren zu k�nnen
$datetime1 = new DateTime($this->start_date);
$bsc = null;
$array_Performance=array();
$array_name=array();
$array_time=array();
$name=null;
for ($i = 0; $i<=$this->timespan; $i++)
{
        $j="0";
         $pers = new Perspective($this->db, $this->pers_id, $this->bsc_id, $datetime1->format('d.m.Y'));
         $name = $pers->GetName();
        $temp = $pers->GetIndicators();
          if(count($temp)<1)
  {

  }
  else
  {
        foreach($temp as $indicator)
        {

        $array_Performance[$j][$i]= round($indicator->GetPerformance());
        $array_name[$j] = $indicator->GetName();
        $j++;
        }
  }
$array_time[$i]=$datetime1->format('d.m.Y');
        //Erhöhung um einen Tag
	$datetime1->add(new DateInterval('P1D'));
}

include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

  if(count($array_Performance)<1)
  {

  }
  else
  {
$myData = new pData();
        for ($i = 0; $i<count($array_name); $i++)
        {
        $myData->addPoints($array_Performance[$i],$array_name[$i]);
        $myData->setSerieDescription($array_name[$i],$array_name[$i]);
        $myData->setSerieOnAxis($array_name[$i],0);
        }

$myData->addPoints($array_time,"Tage");
$myData->setAbscissa("Tage");

$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Effizienz");
$myData->setAxisUnit(0,"%");

$myPicture = new pImage(700,300,$myData);
$Settings = array("R"=>232, "G"=>232, "B"=>232, "Dash"=>1, "DashR"=>252, "DashG"=>252, "DashB"=>252);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,699,299,array("R"=>0,"G"=>0,"B"=>0));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>56, "G"=>56, "B"=>56);
$myPicture->drawText(205,25,"Effizienz von ".$name." zwischen $this->start_date und $this->end_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,675,250);
$myPicture->setFontProperties(array("R"=>0,"G"=>0,"B"=>0,"FontName"=>"libs/pchart/fonts/MankSans.ttf","FontSize"=>9));

$Settings = array("Pos"=>690101, "Mode"=>690201, "LabelingMethod"=>691011, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("DisplayValues"=>TRUE);
$myPicture->drawSplineChart($Config);

$Config = array("R"=>0, "G"=>0, "B"=>0, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "WriteCaption"=>1, "Caption"=>"Threshold", "DrawBox"=>1);
$myPicture->drawThreshold(0,$Config);

$Config = array("FontR"=>0, "FontG"=>0, "FontB"=>0, "FontName"=>"libs/pchart/fonts/MankSans.ttf", "FontSize"=>8, "Margin"=>6, "Alpha"=>30, "BoxSize"=>5, "Style"=>690800, "Mode"=>690902);
$myPicture->drawLegend(47,286,$Config);

  $File = null;
  $File = "temp/".time()."".rand(5, 500).".png";
  $myPicture->Render($File);
  return $File;
  }
}

public function drawPersWeight()
{
$this->pers = new Perspective($this->db, $this->pers_id, $this->bsc_id, $this->start_date);
$name = $this->pers->GetName();

include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

// Dataset definition
$MyData = new pData;

  $temp = $this->pers->GetIndicators();
            if(count($temp)<1)
  {

  }
  else
  {
  $array_weight=array();
  $array_name=array();
  $sum="0";
  $i="0";
  $average2=array();
  $average=null;
  $array_werte=array();
  foreach($temp as $perspektive)
  {
      $array_name[$i] = $perspektive->GetName();
$array_werte[$i] = round($perspektive->GetWeight());

$i++;
}
/* Create and populate the pData object */
$MyData->addPoints($array_werte,"Gewichtung");
$MyData->setAxisName(0,"Gewichtung");
$MyData->addPoints($array_name,"Indikatoren");
$MyData->setSerieDescription("Indikatoren","Indikatoren");
$MyData->setAbscissa("Indikatoren");

/* Create the pChart object */
$myPicture = new pImage(600,350,$MyData);
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/MankSans.ttf","FontSize"=>9));

/* Draw the chart scale */
$myPicture->setGraphArea(150,30,580,330);
$myPicture->drawScale(array("CycleBackground"=>TRUE,"DrawSubTicks"=>TRUE,"GridR"=>0,"GridG"=>0,"GridB"=>0,"GridAlpha"=>10,"Pos"=>SCALE_POS_TOPBOTTOM)); //

/* Turn on shadow computing */
$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

/* Draw the chart */
$myPicture->drawBarChart(array("DisplayPos"=>LABEL_POS_INSIDE,"DisplayValues"=>TRUE,"Rounded"=>TRUE,"Surrounding"=>30));


  $File = "temp/".time()."".rand(5, 500).".png";
  $myPicture->Render($File);
  return $File;
  }

}
public function drawPersWeight_timespan($enddate)
{
$this->end_date=$enddate;
//umwandeln in unix-zeitstempel um die differentz (timespan) zu ermitteln
$datetime1 = strtotime($this->start_date);
$datetime2 = strtotime($this->end_date);
$this->timespan=($datetime2-$datetime1)/86400;
//umwandeln in DateTime, um Tage Kalndergetreu addieren zu k�nnen
$datetime1 = new DateTime($this->start_date);
$bsc = null;
$array_gewichtung=array();
$array_name=array();
for ($i = 0; $i<=$this->timespan; $i++)
{
        $j="0";
        $this->pers = new Perspective($this->db, $this->pers_id, $this->bsc_id, $datetime1->format('d.m.Y'));
        $temp = $this->pers->GetIndicators();
                  if(count($temp)<1)
  {

  }
  else
  {
        foreach($temp as $perspektive)
        {

        $array_gewichtung[$j][$i]= $perspektive->GetWeight();
        $array_name[$j] = $perspektive->GetName();
        $j++;
        }
  }
        //Erhöhung um einen Tag
	$datetime1->add(new DateInterval('P1D'));
}

$name = $this->pers->GetName();
// Standard inclusions
 include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

          if(count($array_gewichtung)<1)
  {

  }
  else
  {
$myData = new pData();

        for ($i = 0; $i<count($array_name); $i++)
        {
        $myData->addPoints($array_gewichtung[$i],$array_name[$i]);
        $myData->setSerieDescription($array_name[$i],$array_name[$i]);
        $myData->setSerieOnAxis($array_name[$i],0);
        }


$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Gewichtung");
$myData->setAxisUnit(0,"");

$myPicture = new pImage(600,300,$myData);
$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,599,299,array("R"=>214, "G"=>214, "B"=>214));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>138, "G"=>138, "B"=>138);

$myPicture->drawText(200,25,"Gewichtung von ".$this->pers->GetName()." zwischen $this->start_date und $this->end_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,575,260);
$myPicture->setFontProperties(array("R"=>161,"G"=>161,"B"=>161,"FontName"=>"libs/pchart/fonts/Bedizen.ttf","FontSize"=>8));

$Settings = array("Pos"=>690101, "Mode"=>690203, "LabelingMethod"=>691012, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("Gradient"=>1, "DisplayValues"=>1, "Rounded"=>1);
$myPicture->drawBarChart($Config);

$Config = array("R"=>166, "G"=>166, "B"=>166, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "Caption"=>"Threshold");
$myPicture->drawThreshold(0,$Config);
$myPicture->drawLegend(50,280,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));
 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;
  }
}

public function drawPersRadar()
{
$this->pers = new Perspective($this->db, $this->pers_id, $this->bsc_id, $this->start_date);
$name = $this->pers->GetName();

// Standard inclusions
include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pRadar.class");
include_once("libs/pchart/class/pImage.class");

  $temp = $this->pers->GetIndicators();
            if(count($temp)<1)
  {

  }
  else
  {
  $array_performance=array();
  $array_name=array();

  $i="0";
  foreach($temp as $perspektive)
  {
      $array_performance[$i] = round($perspektive->GetPerformance());
      $array_name[$i] = $perspektive->GetName()." - w(".$perspektive->GetWeight().")";
      $i++;
  }

/* Prepare some nice data & axis config */
$MyData = new pData();
$MyData->addPoints($array_performance,"ScoreA");
$MyData->setSerieDescription("ScoreA",$this->pers->GetName());

/* Create the X serie */
$MyData->addPoints($array_name,"Labels");
$MyData->setAbscissa("Labels");

/* Create the pChart object */
$myPicture = new pImage(400,300,$MyData);

/* Do some cosmetics */

$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

/* Draw the top bar */

$myPicture->drawRectangle(0,0,399,229,array("R"=>255,"G"=>255,"B"=>255));
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Silkscreen.ttf","FontSize"=>8));
$myPicture->drawText(10,13,"pRadar - Draw radar charts",array("R"=>255,"G"=>255,"B"=>255));

/* Define general drawing parameters */
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/MankSans.ttf","FontSize"=>10,"R"=>80,"G"=>80,"B"=>80));
$myPicture->setShadow(TRUE,array("X"=>2,"Y"=>2,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));

/* Create the radar object */
$SplitChart = new pRadar();

/* Draw the 1st radar chart */
$myPicture->setGraphArea(10,25,340,295);
$Options = array("Layout"=>RADAR_LAYOUT_STAR,"BackgroundGradient"=>array("StartR"=>255,"StartG"=>255,"StartB"=>255,"StartAlpha"=>50,"EndR"=>32,"EndG"=>109,"EndB"=>174,"EndAlpha"=>30));
$SplitChart->drawRadar($myPicture,$MyData,$Options);


/* Write down the legend */
$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/MankSans.ttf","FontSize"=>8));
$myPicture->drawLegend(40,40,array("Style"=>LEGEND_BOX,"Mode"=>LEGEND_HORIZONTAL));

  $File = "temp/".time()."".rand(5, 500).".png";
  $myPicture->Render($File);
  return $File;
  }
}




public function drawIndiWert()
{
$this->indi = new indicator($this->db, $this->indi_id, $this->pers_id, $this->start_date);
include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

$myData = new pData();

  $pers_progress = $this->indi->GetValue();

$myData->addPoints(array(round($pers_progress)),"Serie1");
$myData->setSerieDescription($this->indi->GetName(),"Serie 1");
$myData->setSerieOnAxis("Serie1",0);


$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Werte");
$myData->setAxisUnit(0,"");

$myPicture = new pImage(400,300,$myData);
$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,399,299,array("R"=>214, "G"=>214, "B"=>214));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>138, "G"=>138, "B"=>138);

$myPicture->drawText(200,25,"Wert von ".$this->indi->GetName()." am $this->start_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,375,260);
$myPicture->setFontProperties(array("R"=>161,"G"=>161,"B"=>161,"FontName"=>"libs/pchart/fonts/Bedizen.ttf","FontSize"=>8));

$Settings = array("Pos"=>690101, "Mode"=>690203, "LabelingMethod"=>691012, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("Gradient"=>1, "DisplayValues"=>1, "Rounded"=>1);
$myPicture->drawBarChart($Config);

$Config = array("R"=>166, "G"=>166, "B"=>166, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "Caption"=>"Threshold");
$myPicture->drawThreshold(0,$Config);

 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;
}
public function drawIndiWert_timespan($enddate)
{
$this->end_date=$enddate;

//umwandeln in unix-zeitstempel um die differentz (timespan) zu ermitteln
$datetime1 = strtotime($this->start_date);
$datetime2 = strtotime($this->end_date);
$this->timespan=($datetime2-$datetime1)/86400;
//umwandeln in DateTime, um Tage Kalndergetreu addieren zu k�nnen
$datetime1 = new DateTime($this->start_date);
$wertearray = array();
$indiname=null;
$zeitarray=array();
for ($i = 0; $i<=$this->timespan; $i++)
{

        $this->indi = new indicator($this->db, $this->indi_id, $this->pers_id, $datetime1->format('d.m.Y'));
        $wertearray[$i]=$this->indi->GetValue();
        $zeitarray[$i]=$datetime1->format('d.m.Y');
        $indiname=$this->indi->GetName();
        //Erhöhung um einen Tag
	$datetime1->add(new DateInterval('P1D'));
}

include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

$myData = new pData();

$myData->addPoints($wertearray,"Werte");
$myData->setSerieDescription($this->indi->GetName(),"Serie 1");
$myData->setSerieOnAxis("Werte",0);

$myData->addPoints($zeitarray,"Tage");
$myData->setAbscissa("Tage");

$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Werte");
$myData->setAxisUnit(0,$this->indi->GetUnit());

$myPicture = new pImage(600,300,$myData);
$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,599,299,array("R"=>214, "G"=>214, "B"=>214));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>138, "G"=>138, "B"=>138);

$myPicture->drawText(200,25,"Werte von ".$this->indi->GetName()." vom $this->start_date bis $this->end_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,575,260);
$myPicture->setFontProperties(array("R"=>161,"G"=>161,"B"=>161,"FontName"=>"libs/pchart/fonts/Bedizen.ttf","FontSize"=>8));

$Settings = array("Pos"=>690101, "Mode"=>690203, "LabelingMethod"=>691012, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("Gradient"=>1, "DisplayValues"=>1, "Rounded"=>1, "DisplayPos"=>LABEL_POS_INSIDE);
$myPicture->drawBarChart($Config);

$Config = array("R"=>166, "G"=>166, "B"=>166, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "Caption"=>"Threshold");
$myPicture->drawThreshold(0,$Config);

 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;
}
public function drawIndiPerf()
{
$this->indi = new indicator($this->db, $this->indi_id, $this->pers_id, $this->start_date);

  $pers_progress = $this->indi->GetPerformance();
 include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

$myData = new pData();

  $pers_progress = $this->indi->GetValue();

$myData->addPoints(array(round($pers_progress)),"Serie1");
$myData->setSerieDescription($this->indi->GetName(),"Serie 1");
$myData->setSerieOnAxis("Serie1",0);


$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Werte");
$myData->setAxisUnit(0,"");

$myPicture = new pImage(400,300,$myData);
$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,399,299,array("R"=>214, "G"=>214, "B"=>214));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>138, "G"=>138, "B"=>138);

$myPicture->drawText(200,25,"Performance von ".$this->indi->GetName()." am $this->start_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,375,260);
$myPicture->setFontProperties(array("R"=>161,"G"=>161,"B"=>161,"FontName"=>"libs/pchart/fonts/Bedizen.ttf","FontSize"=>8));

$Settings = array("Pos"=>690101, "Mode"=>690203, "LabelingMethod"=>691012, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("Gradient"=>1, "DisplayValues"=>1, "Rounded"=>1);
$myPicture->drawBarChart($Config);

$Config = array("R"=>166, "G"=>166, "B"=>166, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "Caption"=>"Threshold");
$myPicture->drawThreshold(0,$Config);

 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;
}
public function drawIndiPerf_timespan($enddate)
{
$this->end_date=$enddate;

//umwandeln in unix-zeitstempel um die differentz (timespan) zu ermitteln
$datetime1 = strtotime($this->start_date);
$datetime2 = strtotime($this->end_date);

$this->timespan=($datetime2-$datetime1)/86400;
//umwandeln in DateTime, um Tage Kalndergetreu addieren zu k�nnen
$datetime1 = new DateTime($this->start_date);
$wertearray = array();
$indiname=null;

for ($i = 0; $i<=$this->timespan; $i++)
{

        $this->indi = new indicator($this->db, $this->indi_id, $this->pers_id, $datetime1->format('d.m.Y'));
        $wertearray[$i]=round($this->indi->GetPerformance());
        $zeitarray[$i]=$datetime1->format('d.m.Y');
        $indiname=$this->indi->GetName();
        //Erhöhung um einen Tag
	$datetime1->add(new DateInterval('P1D'));
}
include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

$myData = new pData();

$myData->addPoints($wertearray,"Werte");
$myData->setSerieDescription($this->indi->GetName(),"Serie 1");
$myData->setSerieOnAxis("Werte",0);

$myData->addPoints($zeitarray,"Tage");
$myData->setAbscissa("Tage");

$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Performance");
$myData->setAxisUnit(0,"%");

$myPicture = new pImage(600,300,$myData);
$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,599,299,array("R"=>214, "G"=>214, "B"=>214));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>138, "G"=>138, "B"=>138);

$myPicture->drawText(200,25,"Performance von ".$this->indi->GetName()." vom $this->start_date bis $this->end_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,575,260);
$myPicture->setFontProperties(array("R"=>161,"G"=>161,"B"=>161,"FontName"=>"libs/pchart/fonts/Bedizen.ttf","FontSize"=>8));

$Settings = array("Pos"=>690101, "Mode"=>690203, "LabelingMethod"=>691012, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("Gradient"=>1, "DisplayValues"=>1, "Rounded"=>1, "DisplayPos"=>LABEL_POS_INSIDE);
$myPicture->drawBarChart($Config);

$Config = array("R"=>166, "G"=>166, "B"=>166, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "Caption"=>"Threshold");
$myPicture->drawThreshold(0,$Config);

 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;
}

public function drawIndiWeight()
{
$this->indi = new indicator($this->db, $this->indi_id, $this->pers_id, $this->start_date);
$pers_progress = $this->indi->GetWeight();
include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

$myData = new pData();

$myData->addPoints(array(round($pers_progress)),"Serie1");
$myData->setSerieDescription($this->indi->GetName(),"Serie 1");
$myData->setSerieOnAxis("Serie1",0);


$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Gewichtung");
$myData->setAxisUnit(0,"");

$myPicture = new pImage(400,300,$myData);
$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,399,299,array("R"=>214, "G"=>214, "B"=>214));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>138, "G"=>138, "B"=>138);

$myPicture->drawText(200,25,"Gewichtung von ".$this->indi->GetName()." am $this->start_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,375,260);
$myPicture->setFontProperties(array("R"=>161,"G"=>161,"B"=>161,"FontName"=>"libs/pchart/fonts/Bedizen.ttf","FontSize"=>8));

$Settings = array("Pos"=>690101, "Mode"=>690203, "LabelingMethod"=>691012, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("Gradient"=>1, "DisplayValues"=>1, "Rounded"=>1);
$myPicture->drawBarChart($Config);

$Config = array("R"=>166, "G"=>166, "B"=>166, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "Caption"=>"Threshold");
$myPicture->drawThreshold(0,$Config);

 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;
}
public function drawIndiWeight_timespan($enddate)
{
$this->end_date=$enddate;

//umwandeln in unix-zeitstempel um die differentz (timespan) zu ermitteln
$datetime1 = strtotime($this->start_date);
$datetime2 = strtotime($this->end_date);

$this->timespan=($datetime2-$datetime1)/86400;
//umwandeln in DateTime, um Tage Kalndergetreu addieren zu k�nnen
$datetime1 = new DateTime($this->start_date);
$wertearray = array();
$indiname=null;
$zeitarray=array();
for ($i = 0; $i<=$this->timespan; $i++)
{

        $this->indi = new indicator($this->db, $this->indi_id, $this->pers_id, $datetime1->format('d.m.Y'));
        $wertearray[$i]=$this->indi->GetWeight();
        $zeitarray[$i]=$datetime1->format('d.m.Y');
        $indiname=$this->indi->GetName();
        //Erhöhung um einen Tag
	$datetime1->add(new DateInterval('P1D'));
}
include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

$myData = new pData();

$myData->addPoints($wertearray,"Gewichtung");
$myData->setSerieDescription($this->indi->GetName(),"Serie 1");
$myData->setSerieOnAxis("Gewichtung",0);

$myData->addPoints($zeitarray,"Tage");
$myData->setAbscissa("Tage");

$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Gewichtung");
$myData->setAxisUnit(0,"");

$myPicture = new pImage(600,300,$myData);
$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,599,299,array("R"=>214, "G"=>214, "B"=>214));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>138, "G"=>138, "B"=>138);

$myPicture->drawText(200,25,"Gewichtung von ".$this->indi->GetName()." vom $this->start_date bis $this->end_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,575,260);
$myPicture->setFontProperties(array("R"=>161,"G"=>161,"B"=>161,"FontName"=>"libs/pchart/fonts/Bedizen.ttf","FontSize"=>8));

$Settings = array("Pos"=>690101, "Mode"=>690203, "LabelingMethod"=>691012, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("Gradient"=>1, "DisplayValues"=>1, "Rounded"=>1, "DisplayPos"=>LABEL_POS_INSIDE);
$myPicture->drawBarChart($Config);

$Config = array("R"=>166, "G"=>166, "B"=>166, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "Caption"=>"Threshold");
$myPicture->drawThreshold(0,$Config);

 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;
}
public function drawIndiPrBar()
{
$this->indi = new indicator($this->db, $this->indi_id, $this->pers_id, $this->start_date);
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");
 /* Create the pChart object */
 $myPicture = new pImage(420,40);
 
 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0, "G"=>0, "B"=>0));

 /* Write the picture title */
 $myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Silkscreen.ttf", "FontSize"=>6));

 /* Set the font & shadow options */
 $myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf", "FontSize"=>10));
 $myPicture->setShadow(TRUE,array("X"=>1, "Y"=>1, "R"=>0, "G"=>0, "B"=>0, "Alpha"=>20));

if($this->indi->GetProgress()<0)
{
 $progress="1";
}
else
{
    $progress=round($this->indi->GetProgress());
}
 /* Draw a progress bar */
 $progressOptions = array("Width"=>400, "R"=>134, "G"=>209, "B"=>27, "Surrounding"=>20, "BoxBorderR"=>0, "BoxBorderG"=>0, "BoxBorderB"=>0, "BoxBackR"=>255, "BoxBackG"=>255, "BoxBackB"=>255, "RFade"=>206, "GFade"=>133, "BFade"=>30, "ShowLabel"=>TRUE, "LabelPos"=>LABEL_POS_CENTER);
 $myPicture->drawProgress(10,10,$progress,$progressOptions);

 
 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;
}

public function drawIndiFort()
{
$this->indi = new indicator($this->db, $this->indi_id, $this->pers_id, $this->start_date);
$pers_progress = $this->indi->GetProgress();
include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

$myData = new pData();

$myData->addPoints(array(round($pers_progress)),"Serie1");
$myData->setSerieDescription($this->indi->GetName(),"Serie 1");
$myData->setSerieOnAxis("Serie1",0);


$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Fortschritt");
$myData->setAxisUnit(0,"%");

$myPicture = new pImage(400,300,$myData);
$Settings = array("R"=>235, "G"=>235, "B"=>235, "Dash"=>1, "DashR"=>255, "DashG"=>255, "DashB"=>255);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,399,299,array("R"=>214, "G"=>214, "B"=>214));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>138, "G"=>138, "B"=>138);

$myPicture->drawText(200,25,"Fortschritt von ".$this->indi->GetName()." am $this->start_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,375,260);
$myPicture->setFontProperties(array("R"=>161,"G"=>161,"B"=>161,"FontName"=>"libs/pchart/fonts/Bedizen.ttf","FontSize"=>8));

$Settings = array("Pos"=>690101, "Mode"=>690203, "LabelingMethod"=>691012, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("Gradient"=>1, "DisplayValues"=>1, "Rounded"=>1);
$myPicture->drawBarChart($Config);

$Config = array("R"=>166, "G"=>166, "B"=>166, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "Caption"=>"Threshold");
$myPicture->drawThreshold(0,$Config);

 $File = "temp/".time()."".rand(5, 500).".png";
 $myPicture->Render($File);
 return $File;
}





public function drawIndiFort_timespan($enddate)
{

  $this->end_date=$enddate;
//umwandeln in unix-zeitstempel um die differentz (timespan) zu ermitteln
$datetime1 = strtotime($this->start_date);
$datetime2 = strtotime($this->end_date);
$this->timespan=($datetime2-$datetime1)/86400;
//umwandeln in DateTime, um Tage Kalndergetreu addieren zu k�nnen
$datetime1 = new DateTime($this->start_date);
$bsc = null;
$array_Performance=array();
$array_name= null;
$array_time=array();
$name=null;
for ($i = 0; $i<=$this->timespan; $i++)
{

         $pers = new indicator($this->db, $this->indi_id, $this->pers_id, $datetime1->format('d.m.Y'));
         $name = $pers->GetName();


        $array_Performance[$i]= round($pers->GetProgress());

$array_time[$i]=$datetime1->format('d.m.Y');
        //Erhöhung um einen Tag
	$datetime1->add(new DateInterval('P1D'));
}

include_once("libs/pchart/class/pData.class");
include_once("libs/pchart/class/pDraw.class");
include_once("libs/pchart/class/pImage.class");

$myData = new pData();
        $myData->addPoints($array_Performance,$name);
        $myData->setSerieDescription($name,$name);
        $myData->setSerieOnAxis($name,0);


$myData->addPoints($array_time,"Tage");
$myData->setAbscissa("Tage");

$myData->setAxisPosition(0,AXIS_POSITION_LEFT);
$myData->setAxisName(0,"Fortschritt");
$myData->setAxisUnit(0,"%");

$myPicture = new pImage(700,300,$myData);
$Settings = array("R"=>232, "G"=>232, "B"=>232, "Dash"=>1, "DashR"=>252, "DashG"=>252, "DashB"=>252);
$myPicture->drawFilledRectangle(0,0,$g_width,$g_height,$Settings);

$myPicture->drawRectangle(0,0,699,299,array("R"=>0,"G"=>0,"B"=>0));

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>20));

$myPicture->setFontProperties(array("FontName"=>"libs/pchart/fonts/Forgotte.ttf","FontSize"=>14));
$TextSettings = array("Align"=>690405, "R"=>56, "G"=>56, "B"=>56);
$myPicture->drawText(205,25,"Fortschritt von ".$name." zwischen $this->start_date und $this->end_date",$TextSettings);

$myPicture->setShadow(FALSE);
$myPicture->setGraphArea(50,50,675,250);
$myPicture->setFontProperties(array("R"=>0,"G"=>0,"B"=>0,"FontName"=>"libs/pchart/fonts/MankSans.ttf","FontSize"=>9));

$Settings = array("Pos"=>690101, "Mode"=>690201, "LabelingMethod"=>691011, "GridR"=>255, "GridG"=>255, "GridB"=>255, "GridAlpha"=>50, "TickR"=>0, "TickG"=>0, "TickB"=>0, "TickAlpha"=>50, "LabelRotation"=>0, "CycleBackground"=>1, "DrawXLines"=>1, "DrawSubTicks"=>1, "SubTickR"=>255, "SubTickG"=>0, "SubTickB"=>0, "SubTickAlpha"=>50, "DrawYLines"=>ALL);
$myPicture->drawScale($Settings);

$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>50,"G"=>50,"B"=>50,"Alpha"=>10));

$Config = array("DisplayValues"=>TRUE);
$myPicture->drawSplineChart($Config);

$Config = array("R"=>0, "G"=>0, "B"=>0, "Alpha"=>50, "AxisID"=>0, "Tick"=>2, "WriteCaption"=>1, "Caption"=>"Threshold", "DrawBox"=>1);
$myPicture->drawThreshold(0,$Config);

$Config = array("FontR"=>0, "FontG"=>0, "FontB"=>0, "FontName"=>"libs/pchart/fonts/MankSans.ttf", "FontSize"=>8, "Margin"=>6, "Alpha"=>30, "BoxSize"=>5, "Style"=>690800, "Mode"=>690902);
$myPicture->drawLegend(47,286,$Config);

  $File = null;
  $File = "temp/".time()."".rand(5, 500).".png";
  $myPicture->Render($File);
  return $File;

}
}
?>