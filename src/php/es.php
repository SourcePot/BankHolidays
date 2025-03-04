<?php
/*
* @author Carsten Wallenhauer <admin@datapool.info>
* @copyright 2023 to today Carsten Wallenhauer
* @license https://opensource.org/license/mit/ MIT
*/
declare(strict_types=1);

namespace SourcePot\BankHolidays;

class es{
    
    private const COUNTRY='Spain';
    private const EASTER_CALENDAR=CAL_EASTER_DEFAULT;
    private const REGIONS=['Andalusia','Aragon','Asturias','Balearic Islands','Basque Country','Canary Islands','Cantabria','Castile and León','Castilla-La Mancha','Extremadura','Galicia','La Rioja','Madrid','Murcia','Navarre','Valencia','Ceuta','Melilla'];
    private const TIMEZONES=['Spain'=>'Europe/Madrid','Canary Islands'=>'Europe/London'];

    private $events=['New Years’ Day'=>['date'=>'-01-01','country'=>self::COUNTRY,'regions'=>self::REGIONS],
                    'Epiphany'=>['date'=>'-01-06','country'=>self::COUNTRY,'regions'=>self::REGIONS],
                    'Andalusia Day'=>['date'=>'-02-28','country'=>self::COUNTRY,'regions'=>['Andalusia']],
                    'Day of the Balearic Islands'=>['date'=>'-03-01','country'=>self::COUNTRY,'regions'=>['Balearic Island']],
                    'Fifth Of March'=>['date'=>'-03-05','country'=>self::COUNTRY,'regions'=>['Zaragoza']],
                    'St. Joseph’s Day'=>['date'=>'-03-19','country'=>self::COUNTRY,'regions'=>['Murcia','Valencia']],
                    'Holy Thursday'=>['method'=>'addHolyThursday','country'=>self::COUNTRY,'regions'=>self::REGIONS],
                    'Good Friday'=>['method'=>'addGoodFriday','country'=>self::COUNTRY,'regions'=>self::REGIONS],
                    'Easter Monday'=>['method'=>'addEasterMonday','country'=>self::COUNTRY,'regions'=>['Balearic Islands','Basque Country','Catalonia','La Rioja','Navarre','Valencia']],
                    'Day of Aragon'=>['date'=>'-04-23','country'=>self::COUNTRY,'regions'=>['Aragon']],
                    'Day of Castile and Léon'=>['date'=>'-04-23','country'=>self::COUNTRY,'regions'=>['Castile and León']],
                    'Day of Madrid'=>['date'=>'-05-02','country'=>self::COUNTRY,'regions'=>['Madrid']],
                    'Feast Day Of St Isidore'=>['date'=>'-05-15','country'=>self::COUNTRY,'regions'=>['Madrid']],
                    'Galician Literature Day'=>['date'=>'-05-17','country'=>self::COUNTRY,'regions'=>['Galicia']],
                    'Day of the Canary Islands'=>['date'=>'-05-30','country'=>self::COUNTRY,'regions'=>['Canary Islands']],
                    'Day of Castilla-La Mancha'=>['date'=>'-05-31','country'=>self::COUNTRY,'regions'=>['Castilla-La Mancha']],
                    'Whitsunday'=>['method'=>'addWhitsunday','country'=>self::COUNTRY,'regions'=>self::REGIONS],
                    'Whitmonday'=>['method'=>'addWhitmonday','country'=>self::COUNTRY,'regions'=>['Catalonia']],
                    'Day of Murcia'=>['date'=>'-06-09','country'=>self::COUNTRY,'regions'=>['Murcia']],
                    'Day of La Rioja'=>['date'=>'-06-09','country'=>self::COUNTRY,'regions'=>['La Rioja']],
                    'San Antonio'=>['date'=>'-06-13','country'=>self::COUNTRY,'regions'=>['Ceuta']],
                    'Corpus Christi'=>['method'=>'addCorpusChristi','country'=>self::COUNTRY,'regions'=>['Castilla-La Mancha']],
                    'Labour Day'=>['date'=>'-05-01','country'=>self::COUNTRY,'regions'=>self::REGIONS],
                    'St John’s Day'=>['date'=>'-06-24','country'=>self::COUNTRY,'regions'=>['Catalonia']],
                    'Eid al-Adha'=>['method'=>'addEidalAdha','country'=>self::COUNTRY,'regions'=>['Ceuta','Melilla']],
                    'St James’ Day'=>['date'=>'-06-24','country'=>self::COUNTRY,'regions'=>['Basque Country','Navarre']],
                    'Day of Galicia'=>['date'=>'-07-25','country'=>self::COUNTRY,'regions'=>['Galicia']],
                    'Cantabrian Institutions Day'=>['date'=>'-07-28','country'=>self::COUNTRY,'regions'=>['Cantabria']],
                    'Santa Maria de Africa'=>['date'=>'-08-05','country'=>self::COUNTRY,'regions'=>['Ceuta']],
                    'The Day of Cantabria'=>['date'=>'-08-14','country'=>self::COUNTRY,'regions'=>['Cantabria']],
                    'Assumption Day'=>['date'=>'-08-15','country'=>self::COUNTRY,'regions'=>self::REGIONS],
                    'Day of Ceuta'=>['date'=>'-09-02','country'=>self::COUNTRY,'regions'=>['Ceuta']],
                    'Day of Asturias'=>['date'=>'-09-08','country'=>self::COUNTRY,'regions'=>['Asturias']],
                    'Day of Extremadura'=>['date'=>'-09-08','country'=>self::COUNTRY,'regions'=>['Extremadura']],
                    'Day of Catalonia'=>['date'=>'-09-11','country'=>self::COUNTRY,'regions'=>['Catalonia']],
                    'Day of the Bien Aparecida'=>['date'=>'-09-15','country'=>self::COUNTRY,'regions'=>['Cantabria']],
                    'Day of Melilla'=>['date'=>'-09-17','country'=>self::COUNTRY,'regions'=>['Melilla']],
                    'Day of Valencia'=>['date'=>'-10-09','country'=>self::COUNTRY,'regions'=>['Valencia']],
                    'Spain’s National Day'=>['date'=>'-10-12','country'=>self::COUNTRY,'regions'=>self::REGIONS],
                    'All Saints’ Day'=>['date'=>'-11-01','country'=>self::COUNTRY,'regions'=>self::REGIONS],
                    'San Francisco Javier'=>['date'=>'-12-03','country'=>self::COUNTRY,'regions'=>['Navarre']],
                    'Spanish Constitution Day'=>['date'=>'-12-06','country'=>self::COUNTRY,'regions'=>self::REGIONS],
                    'Immaculate Conception'=>['date'=>'-12-08','country'=>self::COUNTRY,'regions'=>self::REGIONS],
                    'Saint Stephen’s Day'=>['date'=>'-12-26','country'=>self::COUNTRY,'regions'=>['Catalonia']],
                    'Christmas Day'=>['date'=>'-12-25','country'=>self::COUNTRY,'regions'=>self::REGIONS],
                    'Christmas holiday'=>['date'=>'-12-26','country'=>self::COUNTRY,'regions'=>self::REGIONS],
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
                if (empty($event)){continue;}
            } else {
                throw new \Exception('Event definition error.'); 
            }
            $event['Start timezone']=$timezoneObj->getName();
            $event['End timezone']=$timezoneObj->getName();
            $event['Type']=$defArr['type']??'Bank holiday';
            yield $event;
        }
    }

    private function addEidalAdha(array $event):array
    {
        $year=intval($this->relevantYear);
        if ($year>1970){
            $lunarYearSec=30617315.712;
            $currentEidalAdha=strtotime("1970-02-16 12:00:00");
            do{
                $currentEidalAdha+=$lunarYearSec;
                $currentEidalAdhaTimeStamp=intval($currentEidalAdha);
                $currentEidalAdhaYear=intval(date('Y',$currentEidalAdhaTimeStamp));
                
            } while($currentEidalAdhaYear<$year);
            $event['Start']=date('Y-m-d',$currentEidalAdhaTimeStamp).' 00:00:00';
            $event['End']=date('Y-m-d',$currentEidalAdhaTimeStamp).' 23:59:59';           
        } else {
            $event=[];            
        }
        return $event;
    }

    private function addGoodFriday(array $event):array
    {
        $dtObj=clone $this->easterSundayDtObj;
        $date=$dtObj->modify('previous friday')->format('Y-m-d');
        $event['Start']=$date.' 00:00:00';
        $event['End']=$date.' 23:59:59';
        return $event;
    }

    private function addHolyThursday(array $event):array
    {
        $dtObj=clone $this->easterSundayDtObj;
        $date=$dtObj->modify('previous thursday')->format('Y-m-d');
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



}
?>