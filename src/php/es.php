<?php
/*
* @author Carsten Wallenhauer <admin@datapool.info>
* @copyright 2023 to today Carsten Wallenhauer
* @license https://opensource.org/license/mit/ MIT
*/
declare(strict_types=1);

namespace SourcePot\BankHolidays;

class es{
    
    const TIMEZONE='Europe/Madrid';
    const TIMEZONE_CanaryIslands='Europe/London';
    
    private $allStates=array('Andalusia','Aragon','Asturias','Balearic Islands','Basque Country','Canary Islands',
                             'Cantabria','Castile and León','Castilla-La Mancha','Catalonia','Extremadura','Galicia',
                             'La Rioja','Madrid','Murcia','Navarre','Valencia','Ceuta','Melilla'
                             );

    private $events=array('New Years’ Day'=>array('date'=>'-01-01'),
                          'Epiphany'=>array('date'=>'-01-06'),
                          'Andalusia Day'=>array('date'=>'-02-28','states'=>array('Andalusia')),
                          'Day of the Balearic Islands'=>array('date'=>'-03-01','states'=>array('Balearic Island')),
                          'Fifth Of March'=>array('date'=>'-03-05','states'=>array('Zaragoza')),
                          'St. Joseph’s Day'=>array('date'=>'-03-19','states'=>array('Murcia','Valencia')),
                          'Holy Thursday'=>array('method'=>'addHolyThursday','states'=>array('Andalusia','Aragon','Asturias','Balearic Islands','Basque Country','Canary Islands','Cantabria','Castile and León','Castilla-La Mancha','Extremadura','Galicia','La Rioja','Madrid','Murcia','Navarre','Valencia','Ceuta','Melilla')),
                          'Good Friday'=>array('method'=>'addGoodFriday'),
                          'Easter Monday'=>array('method'=>'addEasterMonday','states'=>array('Balearic Islands','Basque Country','Catalonia','La Rioja','Navarre','Valencia')),
                          'Day of Aragon'=>array('date'=>'-04-23','states'=>array('Aragon')),
                          'Day of Castile and Léon'=>array('date'=>'-04-23','states'=>array('Castile and León')),
                          'Day of Madrid'=>array('date'=>'-05-02','states'=>array('Madrid')),
                          'Feast Day Of St Isidore'=>array('date'=>'-05-15','states'=>array('Madrid')),
                          'Galician Literature Day'=>array('date'=>'-05-17','states'=>array('Galicia')),
                          'Day of the Canary Islands'=>array('date'=>'-05-30','states'=>array('Canary Islands')),
                          'Day of Castilla-La Mancha'=>array('date'=>'-05-31','states'=>array('Castilla-La Mancha')),
                          'Whitsunday'=>array('method'=>'addWhitsunday'),
                          'Whitmonday'=>array('method'=>'addWhitmonday','states'=>array('Catalonia')),
                          'Day of Murcia'=>array('date'=>'-06-09','states'=>array('Murcia')),
                          'Day of La Rioja'=>array('date'=>'-06-09','states'=>array('La Rioja')),
                          'San Antonio'=>array('date'=>'-06-13','states'=>array('Ceuta')),
                          'Corpus Christi'=>array('method'=>'addCorpusChristi','states'=>array('Castilla-La Mancha')),
                          'Labour Day'=>array('date'=>'-05-01'),
                          'St John’s Day'=>array('date'=>'-06-24','states'=>array('Catalonia')),
                          'Eid al-Adha'=>array('method'=>'addEidalAdha','states'=>array('Ceuta','Melilla')),
                          'St James’ Day'=>array('date'=>'-06-24','states'=>array('Basque Country','Navarre')),
                          'Day of Galicia'=>array('date'=>'-07-25','states'=>array('Galicia')),
                          'Cantabrian Institutions Day'=>array('date'=>'-07-28','states'=>array('Cantabria')),
                          'Santa Maria de Africa'=>array('date'=>'-08-05','states'=>array('Ceuta')),
                          'The Day of Cantabria'=>array('date'=>'-08-14','states'=>array('Cantabria')),
                          'Assumption Day'=>array('date'=>'-08-15'),
                          'Day of Ceuta'=>array('date'=>'-09-02','states'=>array('Ceuta')),
                          'Day of Asturias'=>array('date'=>'-09-08','states'=>array('Asturias')),
                          'Day of Extremadura'=>array('date'=>'-09-08','states'=>array('Extremadura')),
                          'Day of Catalonia'=>array('date'=>'-09-11','states'=>array('Catalonia')),
                          'Day of the Bien Aparecida'=>array('date'=>'-09-15','states'=>array('Cantabria')),
                          'Day of Melilla'=>array('date'=>'-09-17','states'=>array('Melilla')),
                          'Day of Valencia'=>array('date'=>'-10-09','states'=>array('Valencia')),
                          'Spain’s National Day'=>array('date'=>'-10-12'),
                          'All Saints’ Day'=>array('date'=>'-11-01'),
                          'San Francisco Javier'=>array('date'=>'-12-03','states'=>array('Navarre')),
                          'Spanish Constitution Day'=>array('date'=>'-12-06'),
                          'Immaculate Conception'=>array('date'=>'-12-08'),
                          'Saint Stephen’s Day'=>array('date'=>'-12-26','states'=>array('Catalonia')),
                          'Christmas Day'=>array('date'=>'-12-25'),
                          'Christmas holiday'=>array('date'=>'-12-26'),
                          );

    private $easterSundayDtObjs=[];

    private $frelevantYears=[];
    
    public function __construct($relevantYears=FALSE)
    {
        $currentYear=intval(date('Y'));
        if (is_array($relevantYears)){
            $this->relevantYears=$relevantYears;
        } else {
            $this->relevantYears=[($currentYear-1),$currentYear,($currentYear+1)];
        }
    }

    public function getCountries():array
    {
        return array('Spain');
    }

    public function getBankHolidays():array
    {
        $eventsArr=[];
        foreach($this->relevantYears as $relevantYear){
            $year=str_pad(strval($relevantYear),4,"0",STR_PAD_LEFT);
            // get relevant year easter DateTime
            $easterTimestamp=easter_date(intval($year),CAL_EASTER_DEFAULT);
            $timeZoneObj=new \DateTimeZone(self::TIMEZONE);
            $this->easterSundayDtObjs[$year]=new \DateTime('@'.$easterTimestamp,$timeZoneObj);
            $this->easterSundayDtObjs[$year]->modify('next sunday');
            // get events
            foreach($this->events as $description=>$eventDef){
                if (!isset($eventDef['states'])){
                    $eventDef['states']=$this->allStates;
                }
                if (!empty($eventDef['date'])){
                    if ($eventDef['date'][0]=='-'){
                        $eventDef['date']=$year.$eventDef['date'];
                    }
                    $eventDef['start']=$eventDef['date'].' 00:00:00';
                    $eventDef['end']=$eventDef['date'].' 23:59:59';
                } else if (!empty($eventDef['method'])){
                    $method=$eventDef['method'];
                    $eventDef=$this->$method($eventDef,$year);
                }
                if (empty($eventDef['start']) || empty($eventDef['end'])){
                    $msg='No valid "start" and/or "end" date found for '.$description.' '.$year;
                    $eventsArr['Spain']['warning']=(isset($eventsArr['Spain']['warning']))?$eventsArr['Spain']['warning'].'|'.$msg:$msg;
                } else {
                    $canaryIslandKey=array_search('Canary Islands',$eventDef['states']);
                    if ($canaryIslandKey===FALSE){
                        $hasCanaryIsland=FALSE;
                    } else {
                        unset($eventDef['states'][$canaryIslandKey]);
                        $hasCanaryIsland=TRUE;
                    }
                    // CET & CEST
                    if ($eventDef['states']){
                        $id=md5($description.' '.$year.' ES');
                        $eventsArr['Spain'][$id]['Event']=array('Description'=>$description.' (ES)',
                                                                'Type'=>'Bankholiday',
                                                                'Start'=>$eventDef['start'],
                                                                'Start timezone'=>self::TIMEZONE,
                                                                'End'=>$eventDef['end'],
                                                                'End timezone'=>self::TIMEZONE,
                                                                'Recurrence'=>'+0 day',
                                                                'Recurrence times'=>0,
                                                                'Recurrence id'=>$id,
                                                                'source'=>__CLASS__,
                                                                'uid'=>$id,
                                                                );
                        $eventsArr['Spain'][$id]['Location/Destination']=array('Country'=>'Spain');
                        if (isset($eventDef['states'])){
                            $eventsArr['Spain'][$id]['Location/Destination']=array('States'=>$eventDef['states']);
                        }
                    }
                    // WET & WEST | Canary Islands
                    if ($hasCanaryIsland===TRUE){
                        $ciId=md5($description.' '.$year.' ES CI');
                        $eventsArr['Spain'][$ciId]=$eventsArr['Spain'][$id];
                        $eventsArr['Spain'][$ciId]['Event']['Description']=$description.' (ES) CI';
                        $eventsArr['Spain'][$ciId]['Event']['Start timezone']='Atlantic/Canary';
                        $eventsArr['Spain'][$ciId]['Event']['End timezone']='Atlantic/Canary';
                        $eventsArr['Spain'][$ciId]['Location/Destination']=array('States'=>array('Canary Islands'));
                    }
                }
                
            }
        }
        return $eventsArr;
    }

    private function addEidalAdha(array $eventDef, string $year):array
    {
        $year=intval($year);
        if ($year>1970){
            $lunarYearSec=30617315.712;
            $currentEidalAdha=strtotime("1970-02-16 12:00:00");
            do{
                $currentEidalAdha+=$lunarYearSec;
                $currentEidalAdhaTimeStamp=intval($currentEidalAdha);
                $currentEidalAdhaYear=intval(date('Y',$currentEidalAdhaTimeStamp));
                
            } while($currentEidalAdhaYear<$year);
            $eventDef['start']=date('Y-m-d',$currentEidalAdhaTimeStamp).' 00:00:00';
            $eventDef['end']=date('Y-m-d',$currentEidalAdhaTimeStamp).' 23:59:59';           
        } else if ($year===1970){
            $eventDef['start']=date('Y-m-d',$currentEidalAdhaTimeStamp).' 00:00:00';
            $eventDef['end']=date('Y-m-d',$currentEidalAdhaTimeStamp).' 23:59:59';               
        }
        return $eventDef;
    }

    private function addGoodFriday(array $eventDef, string $year):array
    {
        $easterSundayObj=$this->easterSundayDtObjs[$year];
        $dtObj=clone $easterSundayObj;
        $date=$dtObj->modify('previous friday')->format('Y-m-d');
        $eventDef['start']=$date.' 00:00:00';
        $eventDef['end']=$date.' 23:59:59';
        return $eventDef;
    }

    private function addHolyThursday(array $eventDef, string $year):array
    {
        $easterSundayObj=$this->easterSundayDtObjs[$year];
        $dtObj=clone $easterSundayObj;
        $date=$dtObj->modify('previous thursday')->format('Y-m-d');
        $eventDef['start']=$date.' 00:00:00';
        $eventDef['end']=$date.' 23:59:59';
        return $eventDef;
    }

    private function addEasterMonday(array $eventDef, string $year):array
    {
        $easterSundayObj=$this->easterSundayDtObjs[$year];
        $dtObj=clone $easterSundayObj;
        $date=$dtObj->modify('next monday')->format('Y-m-d');
        $eventDef['start']=$date.' 00:00:00';
        $eventDef['end']=$date.' 23:59:59';
        return $eventDef;
    }

    private function addWhitsunday(array $eventDef, string $year):array
    {
        $easterSundayObj=$this->easterSundayDtObjs[$year];
        $dtObj=clone $easterSundayObj;
        $date=$dtObj->modify('+49 days')->format('Y-m-d');
        $eventDef['start']=$date.' 00:00:00';
        $eventDef['end']=$date.' 23:59:59';
        return $eventDef;
    }

    private function addWhitmonday(array $eventDef, string $year):array
    {
        $easterSundayObj=$this->easterSundayDtObjs[$year];
        $dtObj=clone $easterSundayObj;
        $date=$dtObj->modify('+50 days')->format('Y-m-d');
        $eventDef['start']=$date.' 00:00:00';
        $eventDef['end']=$date.' 23:59:59';
        return $eventDef;
    }

    private function addCorpusChristi(array $eventDef, string $year):array
    {
        $easterSundayObj=$this->easterSundayDtObjs[$year];
        $dtObj=clone $easterSundayObj;
        $date=$dtObj->modify('+60 days')->format('Y-m-d');
        $eventDef['start']=$date.' 00:00:00';
        $eventDef['end']=$date.' 23:59:59';
        return $eventDef;
    }



}
?>