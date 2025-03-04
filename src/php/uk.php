<?php
/*
* @author Carsten Wallenhauer <admin@datapool.info>
* @copyright 2023 to today Carsten Wallenhauer
* @license https://opensource.org/license/mit/ MIT
*/
declare(strict_types=1);

namespace SourcePot\BankHolidays;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class uk{

    private const URL_PREFIX='https://www.gov.uk';
    private const URL_SUFFIXES=['England and Wales'=>'/bank-holidays/england-and-wales.ics','Scottland'=>'/bank-holidays/scotland.ics','Northern Ireland'=>'/bank-holidays/northern-ireland.ics',];

    private const COUNTRY='United Kingdom';
    private const REGIONS=['England and Wales','Scottland','Northern Ireland'];
    private const TIMEZONES=['United Kingdom'=>'Europe/London'];

    private $relevantYear;
    
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
        if (isset(self::URL_SUFFIXES[$region])){
            $url=self::URL_PREFIX.self::URL_SUFFIXES[$region];
            foreach($this->eventsFromGovUk($url,$region) as $event){
                yield $event;
            }
        } else {
            throw new \Exception('Unknown region "'.$region.'"');
        }
    }

    private function eventsFromGovUk(string $url, string $region):\Iterator
    {
        $ics=@file_get_contents($url);
        if (empty($ics)){return FALSE;}
        $eventCunks=explode('BEGIN:VEVENT',$ics);
        $calHeader=array_shift($eventCunks);
        $eventTemplate=['Country'=>self::COUNTRY,'Region'=>$region,'Type'=>'Bank holiday'];
        foreach($eventCunks as $eventChunk){
            // ics chunk to array
            $eventChunkArr=explode('END:VEVENT',$eventChunk);
            $eventChunk=array_shift($eventChunkArr);
            $eventArr=$this->ics2arr($eventChunk);
            // create event from ics-array
            if (strpos($eventArr['DTSTART'],$this->relevantYear)!==0 && strpos($eventArr['DTEND'],$this->relevantYear)!==0){continue;}
            $event=$eventTemplate;
            $event['Name']=$eventArr['SUMMARY'];
            $event['Start']=$eventArr['DTSTART'];
            $event['Start timezone']=$this->getRegionTimezone($region);
            $event['End']=$eventArr['DTEND'];
            $event['End timezone']=$this->getRegionTimezone($region);
            yield $event;
        }
    }

    private function ics2arr(string $ics):array
    {
        $result=[];
        $lines=explode("\n",$ics);
        foreach($lines as $line){
            $dividerPos=strpos($line,':');
            if ($dividerPos===FALSE){continue;}
            $line=trim($line);
            $key=substr($line,0,$dividerPos);
            $keyArr=explode(';',$key);
            $key=array_shift($keyArr);
            $type=strval(array_shift($keyArr));
            $value=substr($line,$dividerPos+1);
            if (stripos($type,'DATE')!==FALSE){
                $result[$key]=$value[0].$value[1].$value[2].$value[3].'-'.$value[4].$value[5].'-'.$value[6].$value[7].' 00:00:00';
            } else if ($key==='DTSTAMP'){
                $dateTime=new \DateTime($value);
                $result[$key]=$dateTime->format('Y-m-d H:i:s');
            } else {
                $result[$key]=$value;
            }
        }
        return $result;
    }


}
?>