<?php
/*
* This file is part of the Datapool CMS package.
* @package Datapool
* @author Carsten Wallenhauer <admin@datapool.info>
* @copyright 2023 to today Carsten Wallenhauer
* @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-v3
*/
declare(strict_types=1);

namespace SourcePot\Bankholidays;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Stream\Stream;

class de{
    
    const TIMEZONE='Europe/Berlin';
    
    private $allStates=array('Baden-Württemberg','Bavaria','Berlin','Brandenburg','Bremen','Hamburg','Hesse','Lower Saxony','Mecklenburg-Vorpommern','North Rhine-Westphalia','Rhineland-Palatinate','Saarland','Saxony','Saxony-Anhalt','Schleswig-Holstein','Thuringia');

    private $events=array('New Years’ Day'=>array('date'=>'-01-01'),
                          'Three Kings Day'=>array('date'=>'-01-06'),
                          "Women's Day"=>array('date'=>'-03-08'),
                          'Good Friday'=>array('method'=>'addGoodFriday'),
                          'Easter Sunday'=>array('method'=>'addEasterSunday'),
                          'Easter Monday'=>array('method'=>'addEasterMonday'),
                          'Labour Day'=>array('date'=>'-05-01'),
                          'Ascension Day'=>array('method'=>'addAscensionDay'),
                          'Whitsunday'=>array('method'=>'addWhitsunday'),
                          'Whitmonday'=>array('method'=>'addWhitmonday'),
                          'Corpus Christi'=>array('method'=>'addCorpusChristi','states'=>array('Baden-Württemberg','Bavaria','Hesse','North Rhine-Westphalia','Rhineland-Palatinate','Saarland')),
                          'Assumption Day'=>array('date'=>'-08-15','states'=>array('Bavaria')),
                          "World Children's Day"=>array('date'=>'-09-20'),
                          'German Unification Day'=>array('date'=>'-10-03'),
                          'Reformation Day'=>array('date'=>'-10-31'),
                          'All Saints’ Day'=>array('date'=>'-11-01'),
                          'Day of Repentance & Prayer'=>array('method'=>'addDayOfRepentance'),
                          'Holy Night'=>array('start'=>'-12-24 12:00:00','end'=>'-12-24 23:59:59'),
                          'Christmas Day'=>array('date'=>'-12-25'),
                          'Boxing Day'=>array('date'=>'-12-26'),
                          "Saint Sylvester's Day"=>array('start'=>'-12-31 12:00:00','end'=>'-12-31 23:59:59'),
                          );

    private $easterSundayDtObjs=array();

    private $frelevantYears=array();
    
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
        return array('Germany');
    }

    public function getBankHolidays():array
    {
        $eventsArr=array();
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
                    $eventsArr['Germany']['warning']=(isset($eventsArr['Germany']['warning']))?$eventsArr['Germany']['warning'].'|'.$msg:$msg;
                } else {
                    $id=md5($description.' '.$year);
                    $eventsArr['Germany'][$id]['Event']=array('Description'=>$description,
                                                            'Type'=>'Bankholiday DE',
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
                    $eventsArr['Germany'][$id]['Location/Destination']=array('Country'=>'Germany');
                    if (isset($eventDef['states'])){
                        $eventsArr['Germany'][$id]['Location/Destination']=array('States'=>$eventDef['states']);
                    }
                }
                
            }
        }
        return $eventsArr;
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

    private function addEasterSunday(array $eventDef, string $year):array
    {
        $date=$this->easterSundayDtObjs[$year]->format('Y-m-d');
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

    private function addAscensionDay(array $eventDef, string $year):array
    {
        $easterSundayObj=$this->easterSundayDtObjs[$year];
        $dtObj=clone $easterSundayObj;
        $date=$dtObj->modify('+39 days')->format('Y-m-d');
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

    private function addDayOfRepentance(array $eventDef, string $year):array
    {
        $timeZoneObj=new \DateTimeZone(self::TIMEZONE);
        $dtObj=new \DateTime('@'.strtotime($year.'-11-23'),$timeZoneObj);
        $date=$dtObj->modify('previous wednesday')->format('Y-m-d');
        $eventDef['start']=$date.' 00:00:00';
        $eventDef['end']=$date.' 23:59:59';
        return $eventDef;
    }

}
?>