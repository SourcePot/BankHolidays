<?php
/*
* @author Carsten Wallenhauer <admin@datapool.info>
* @copyright 2023 to today Carsten Wallenhauer
* @license https://opensource.org/license/mit/ MIT
*/
declare(strict_types=1);

namespace SourcePot\BankHolidays;

class de{
    
    private const COUNTRY='Germany';
    private const EASTER_CALENDAR=CAL_EASTER_DEFAULT;
    private const REGIONS=['Augsburg','Baden-Württemberg','Bavaria','Berlin','Brandenburg','Bremen','Hamburg','Hesse','Lower Saxony','Mecklenburg-Vorpommern','North Rhine-Westphalia','Rhineland-Palatinate','Saarland','Saxony','Saxony-Anhalt','Schleswig-Holstein','Thuringia'];
    private const TIMEZONES=['Germany'=>'Europe/Berlin'];

    private $events=['New Years’ Day'=>['date'=>'-01-01','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    'Three Kings Day'=>['date'=>'-01-06','country'=>self::COUNTRY,'regions'=>['Augsburg','Baden-Württemberg','Bavaria','Saxony-Anhalt'],],
                    "Women's Day"=>['date'=>'-03-08','country'=>self::COUNTRY,'regions'=>self::REGIONS,'type'=>'Commemorative day'],
                    'Good Friday'=>['method'=>'addGoodFriday','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    'Easter Sunday'=>['method'=>'addEasterSunday','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    'Easter Monday'=>['method'=>'addEasterMonday','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    'Labour Day'=>['date'=>'-05-01','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    'Ascension Day'=>['method'=>'addAscensionDay','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    'Whitsunday'=>['method'=>'addWhitsunday','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    'Whitmonday'=>['method'=>'addWhitmonday','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    'Corpus Christi'=>['method'=>'addCorpusChristi','country'=>self::COUNTRY,'regions'=>['Augsburg','Baden-Württemberg','Bavaria','Hesse','North Rhine-Westphalia','Rhineland-Palatinate','Saarland'],],
                    'Hohes Friedensfest'=>['date'=>'-08-08','country'=>self::COUNTRY,'regions'=>['Augsburg'],],
                    'Assumption Day'=>['date'=>'-08-15','country'=>self::COUNTRY,'regions'=>['Augsburg','Bavaria'],],
                    "World Children's Day"=>['date'=>'-09-20','country'=>self::COUNTRY,'regions'=>self::REGIONS,'type'=>'Commemorative day'],
                    'German Unification Day'=>['date'=>'-10-03','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    'Reformation Day'=>['date'=>'-10-31','country'=>self::COUNTRY,'regions'=>['Brandenburg','Bremen','Hamburg','Mecklenburg-Vorpommern','Lower Saxony','Saxony','Saxony-Anhalt','Schleswig-Holstein','Thuringia'],],
                    'All Saints’ Day'=>['date'=>'-11-01','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    'Day of Repentance & Prayer'=>['method'=>'addDayOfRepentance','country'=>self::COUNTRY,'regions'=>['Saxony'],],
                    'Holy Night'=>['start'=>'-12-24 12:00:00','end'=>'-12-24 23:59:59','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    'Christmas Day'=>['date'=>'-12-25','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    'Boxing Day'=>['date'=>'-12-26','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    "Saint Sylvester’s Day"=>['start'=>'-12-31 12:00:00','end'=>'-12-31 23:59:59','country'=>self::COUNTRY,'regions'=>self::REGIONS,],
                    ];

    private $relevantYear;
    private $easterSundayDtObj;
    
    public function __construct(int|NULL $relevantYear=NULL)
    {
        $relevantYear=$relevantYear??intval(date('Y'));
        $this->relevantYear=str_pad(strval($relevantYear),4,"0",STR_PAD_LEFT);
    }

    static public function getCountry():string
    {
        return self::COUNTRY;
    }

    static public function getRegions():array
    {
        return self::REGIONS;
    }

    public function getRegionTimezone(string|NULL $region=self::COUNTRY):string
    {
        if (isset(self::TIMEZONES[$region])){
            return self::TIMEZONES[$region];
        } else if (in_array($region,self::REGIONS)){
            return self::TIMEZONES[self::COUNTRY];
        } else {
            throw new \Exception('Unknown region "'.$region.'"');
        }
    }

    public function bankHolidays(string $region):\Iterator
    {
        $timezoneObj=new \DateTimeZone(self::getRegionTimezone($region));
        // get relevant year easter DateTime
        $easterTimestamp=easter_date(intval($this->relevantYear),self::EASTER_CALENDAR);
        $this->easterSundayDtObj=new \DateTime('@'.$easterTimestamp);
        $this->easterSundayDtObj->setTimezone($timezoneObj);
        $this->easterSundayDtObj->modify('next sunday');
        // get events
        $eventTemplate=['Country'=>self::COUNTRY,'Region'=>$region];
        foreach($this->events as $name=>$defArr){
            if (!in_array($region,$defArr['regions'])){continue;}
            $event=$eventTemplate;
            $event['Name']=$name;
            if (!empty($defArr['start']) && !empty($defArr['end'])){
                $event['Start']=$this->relevantYear.$defArr['start'];
                $event['End']=$this->relevantYear.$defArr['end'];
            } else if (!empty($defArr['date'])){
                if ($defArr['date'][0]=='-'){
                    $defArr['date']=$this->relevantYear.$defArr['date'];
                }
                $event['Start']=$defArr['date'].' 00:00:00';
                $event['End']=$defArr['date'].' 23:59:59';
            } else if (!empty($defArr['method'])){
                $method=$defArr['method'];
                $event=$this->$method($event,$timezoneObj);
            } else {
                throw new \Exception('Event definition error.'); 
            }
            $event['Start timezone']=$timezoneObj->getName();
            $event['End timezone']=$timezoneObj->getName();
            $event['Type']=$defArr['type']??'Bank holiday';
            yield $event;
        }
    }

    private function addGoodFriday(array $event):array
    {
        $dtObj=clone $this->easterSundayDtObj;
        $date=$dtObj->modify('previous friday')->format('Y-m-d');
        $event['Start']=$date.' 00:00:00';
        $event['End']=$date.' 23:59:59';
        return $event;
    }

    private function addEasterSunday(array $event):array
    {
        $date=$this->easterSundayDtObj->format('Y-m-d');
        $event['Start']=$date.' 00:00:00';
        $event['End']=$date.' 23:59:59';
        return $event;
    }

    private function addEasterMonday(array $event):array
    {
        $dtObj=clone $this->easterSundayDtObj;
        $date=$dtObj->modify('next monday')->format('Y-m-d');
        $event['Start']=$date.' 00:00:00';
        $event['End']=$date.' 23:59:59';
        return $event;
    }

    private function addAscensionDay(array $event):array
    {
        $dtObj=clone $this->easterSundayDtObj;
        $date=$dtObj->modify('+39 days')->format('Y-m-d');
        $event['Start']=$date.' 00:00:00';
        $event['End']=$date.' 23:59:59';
        return $event;
    }

    private function addWhitsunday(array $event):array
    {
        $dtObj=clone $this->easterSundayDtObj;
        $date=$dtObj->modify('+49 days')->format('Y-m-d');
        $event['Start']=$date.' 00:00:00';
        $event['End']=$date.' 23:59:59';
        return $event;
    }

    private function addWhitmonday(array $event):array
    {
        $dtObj=clone $this->easterSundayDtObj;
        $date=$dtObj->modify('+50 days')->format('Y-m-d');
        $event['Start']=$date.' 00:00:00';
        $event['End']=$date.' 23:59:59';
        return $event;
    }

    private function addCorpusChristi(array $event):array
    {
        $dtObj=clone $this->easterSundayDtObj;
        $date=$dtObj->modify('+60 days')->format('Y-m-d');
        $event['Start']=$date.' 00:00:00';
        $event['End']=$date.' 23:59:59';
        return $event;
    }

    private function addDayOfRepentance(array $event, \DateTimeZone $timezoneObj):array
    {
        $dtObj=new \DateTime($this->relevantYear.'-11-23 12:00:00',$timezoneObj);
        $date=$dtObj->modify('previous wednesday')->format('Y-m-d');
        $event['Start']=$date.' 00:00:00';
        $event['End']=$date.' 23:59:59';
        return $event;
    }
    
}
?>