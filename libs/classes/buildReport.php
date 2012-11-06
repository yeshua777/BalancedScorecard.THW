<?php
/**
 * 
 * Dies Klasse erstellt für jeden Tag, der relevant für den Report ist ein BSC-Objekt.
 * Alle diese Objekte werden in einem Array gespeichert, und können von anderen Klassen aufgerufen werden.
 * Im weiteren werden alle für einen Report relevanten Daten der unterschiedlichen BSCs (von verschiedenen Tagen)
 * in ein Array geschrieben.
 * 
 * @author Christian Sauer
 *
 */
class buildReport
{
	/**
	 * 
	 * beinhaltet alle BSC-Objekte (für jeden Tag eins)
	 * @var ArrayObject
	 */
	public $bscs = array();
	/**
	 * 
	 * beinhaltet alle für einen Report relevanten Daten
	 * @var ArrayObject
	 */
	public $bscArray = array();
	public $start_date = null;
	public $end_date = null;
	public $timespan = null;
	public $beginning = null;
	
	public $db = null;
	public $bsc_id = null;

	public function __construct($db, $bsc_id, $start_Datum, $end_Datum)
	{
		$this->db = $db;
		$this->bsc_id = $bsc_id;
		
		//datum abfangen, und als String festlegen
		$this->start_date = $start_Datum;
		$this->end_date = $end_Datum;
		//umwandeln in unix-zeitstempel um die differentz (timespan) zu ermitteln
		$datetime1 = strtotime($start_Datum);
		$datetime2 = strtotime($end_Datum);
		$this->timespan=($datetime2-$datetime1)/86400;
		//umwandeln in DateTime, um Tage Kalndergetreu addieren zu kï¿½nnen
		$datetime1 = new DateTime($start_Datum);
		$bsc = null;
		for ($i = 0; $i<=$this->timespan; $i++)
		{
			$bsc = new Scorecard($db, $bsc_id ,$datetime1->format('d.m.Y'));
			$this->bscs[$i] = $bsc;	
			$datetime1->add(new DateInterval('P1D'));
			
		}
		
		
		$this->beginning = $bsc->GetBeginning();
		$this->bscArray['info']['sc'][0]['start_perf'] = reset($this->bscs)->GetPerformance();
		$this->bscArray['info']['sc'][0]['end_perf'] = end($this->bscs)->GetPerformance();
		$this->bscArray['info']['sc'][0]['dynamic'] = end($this->bscs)->GetPerformance()-reset($this->bscs)->GetPerformance();
		

		
		$diagram = new Diagramm($db, $start_Datum, $bsc_id);
		$this->bscArray['info']['sc'][0]['RadarDia'] = $diagram->drawBscRadar();
		$this->bscArray['info']['sc'][0]['PerformanceLinie'] = $diagram->drawBscPerformance_timespan($end_Datum);
		$this->bscArray['info']['sc'][0]['PerformancePie'] = $diagram->drawBscPerformance();
//		$this->bscArray['info']['sc'][0]['GewichtungDia'] = $diagram->drawBscWeight_timespan($end_Datum);	
		

		
		$perspectives = array();
		$indicators = array();		
		foreach ($this->bscs as $sc_key => $bsc)
		{
			$this->bscArray['sc_data'][$sc_key]['Name'] = $bsc->GetName();
			$this->bscArray['sc_data'][$sc_key]['Datum'] = $bsc->GetDate();
			$this->bscArray['sc_data'][$sc_key]['Performance'] = $bsc->GetPerformance();
			
			$perspectives = $bsc->GetPerspectives();
			foreach ($perspectives as $pers_key => $perspective)
			{
				$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['Name'] = $perspective->GetName();
				$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['Datum'] = $perspective->GetDate();
				$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['Gewichtung'] = $perspective->GetWeight();
				$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['Performance'] = $perspective->GetPerformance();
				$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['ID'] = $perspective->GetIdentifier();
							
				$indicators = $perspective->GetIndicators();
				
					foreach ($indicators as $ind_key => $indicator)
					{
						
						$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['ind_data'][$ind_key]['Name'] = $indicator->GetName();
						$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['ind_data'][$ind_key]['Datum'] = $indicator->GetDate();
						$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['ind_data'][$ind_key]['Gewichtung'] = $indicator->GetWeight();
						$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['ind_data'][$ind_key]['Min'] = $indicator->GetMin();
						$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['ind_data'][$ind_key]['Max'] = $indicator->GetMax();
						$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['ind_data'][$ind_key]['Performance'] = $indicator->GetValue();
						if($indicator->GetUnit())
						{
							$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['ind_data'][$ind_key]['Einheit'] = $indicator->GetUnit();
						}
						else
						{
							$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['ind_data'][$ind_key]['Einheit'] = 'NA';
						}					
						
						$maximize = $indicator->GetMaximize();
						if($maximize == 1)
						{
							$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['ind_data'][$ind_key]['Richtung'] = 'max';
						}
						else if($maximize == 0)
						{
							$this->bscArray['sc_data'][$sc_key]['pers_data'][$pers_key]['ind_data'][$ind_key]['Richtung'] = 'min';
						}
					
					
					
				

				}
				for($i = 0; $i<count($indicators);$i++)
				{
					//indikator-info
					$this->bscArray['info']['pers'][$pers_key]['ind'][$i]['start_perf'] = $this->bscArray['sc_data'][0]['pers_data'][$pers_key]['ind_data'][$i]['Performance'];
					$this->bscArray['info']['pers'][$pers_key]['ind'][$i]['end_perf'] = $this->bscArray['sc_data'][count($this->bscs)-1]['pers_data'][$pers_key]['ind_data'][$i]['Performance'];
					$this->bscArray['info']['pers'][$pers_key]['ind'][$i]['dynamic'] = $this->bscArray['info']['pers'][$pers_key]['ind'][$i]['end_perf'] - $this->bscArray['info']['pers'][$pers_key]['ind'][$i]['start_perf'];
				
				
				
				//	$diagram = new Diagramm($this->db, $this->start_date, $this->bsc_id, $perspective->GetIdentifier(), $indicators[$i]->GetIdentifier());
				//	$this->bscArray['info']['pers'][$pers_key]['ind'][$i]['IndiValue'] = $diagram->drawIndiWert(); //"BID_".$this->bsc_id."PerID_".$perspective->GetIdentifier()."_IID_".$indicators[$i]->GetIdentifier()."";
				//	$this->bscArray['info']['pers'][$pers_key]['ind'][$i]['PerformanceLinie']; 
				//	$this->bscArray['info']['pers'][$pers_key]['ind'][$i]['GewichtungDia']; 
						
				
				}
			}
				
		}
		
		for($i = 0; $i<count($perspectives);$i++)
		{
			$this->bscArray['info']['pers'][$i]['start_perf'] = $this->bscArray['sc_data'][0]['pers_data'][$i]['Performance'];
			$this->bscArray['info']['pers'][$i]['end_perf']  =$this->bscArray['sc_data'][count($this->bscs)-1]['pers_data'][$i]['Performance'];
			$this->bscArray['info']['pers'][$i]['dynamic'] = ($this->bscArray['info']['pers'][$i]['end_perf']) - ($this->bscArray['info']['pers'][$i]['start_perf']);

			
			$diagram = new Diagramm($db, $this->start_date, $bsc_id, $perspectives[$i]->GetIdentifier());
			$this->bscArray['info']['pers'][$i]['RadarDia'] = $diagram->drawPersRadar();
			$this->bscArray['info']['pers'][$i]['PerformanceLinie'] = $diagram->drawPersPerformance_timespan($end_Datum);
			$this->bscArray['info']['pers'][$i]['PerformancePie'] = $diagram->drawPersPerformance();
		//	$this->bscArray['info']['pers'][$i]['GewichtungDia'] = $diagram->drawPersWeight();
			

			//Dieser Teil wurde aus perfomance-Gründen auskommentiert
			
//			$indis = $perspectives[$i]->GetIndicators();
//			for($j = 0; $j<count($indis);$j++)
//			{
//				$diagram = new Diagramm($this->db, $this->start_date, $this->bsc_id, $perspectives[$i]->GetIdentifier(), $indis[$j]->GetIdentifier());
//				$this->bscArray['info']['pers'][$i]['ind'][$j]['IndiValue'] = $diagram->drawIndiWert();
//				$this->bscArray['info']['pers'][$i]['ind'][$j]['IndiPerf'] = $diagram->drawIndiPerf();
//				$this->bscArray['info']['pers'][$i]['ind'][$j]['IndiGew'] = $diagram->drawIndiWeight();
//				$this->bscArray['info']['pers'][$i]['ind'][$j]['IndiSpezial'] = $diagram->drawIndiPrBar();
//			}
		}
	}
	
	
	public function returnBSCs()
	{
		return $this->bscs;
	}
}

	
?>
