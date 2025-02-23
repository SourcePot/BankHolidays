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

    private $baseUri='https://www.gov.uk';
    
    private $events=array('England and Wales'=>'/bank-holidays/england-and-wales.ics',
                          'Scottland'=>'/bank-holidays/scotland.ics',
                          'Northern Ireland'=>'/bank-holidays/northern-ireland.ics',
                         );
    private $headers=[];
    
    private $client=FALSE;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['base_uri'=>$this->baseUri]);
    }
    
    public function getHeaders():array
    {
        return $this->headers;
    }
    
    public function getCountries():array
    {
        return array_keys($this->events);
    }
	
    public function getBankHolidays():array
    {
        $keys=array('start'=>'DTSTART;VALUE=DATE:','end'=>'DTEND;VALUE=DATE:','summary'=>'SUMMARY:','uid'=>'UID:');
        $eventsArr=[];
        foreach($this->events as $country=>$eventUri){
            $eventsArr[$country]=[];
            // get ics calendar
            try{
                $response=$this->client->request('GET',$eventUri);
            } catch (\Exception $e){
                $eventsArr[$country]['error']=trim(strip_tags($e->getMessage()));
                continue;
            }            
            $this->headers[$country]=$response->getHeaders();
            $icsHeaders=$this->header2arr($this->headers[$country]);
            $icsString=$response->getBody()->getContents();
            // parse ics string
            if (empty($icsString)){
                $eventsArr[$country]['error']=(isset($eventsArr[$country]['error']))?'|'.$eventUri.'empty response':$eventUri.'empty response';
            } else if ($icsHeaders['Content-Type']==='text/calendar'){
                $events=explode('END:VEVENT',$icsString);
                array_pop($events);
                foreach($events as $eventIndex=>$eventStr){
                    $event=[];
                    $eventLines=explode("\n",$eventStr);
                    foreach($eventLines as $eventLineIndex=>$eventLine){
                        foreach($keys as $key=>$needle){
                            if (strpos($eventLine,$needle)===FALSE){continue;}
                            $event[$key]=trim(str_replace($needle,'',$eventLine));
                        }
                    }
                    if (!empty($event['start']) && !empty($event['end']) && !empty($event['summary']) && !empty($event['uid'])){
                        $id=md5($event['summary'].' '.substr($event['start'],0,4).' UK');
                        $eventsArr[$country][$id]['Event']=array('Description'=>$event['summary'].' (UK)',
                                                                'Type'=>'Bankholiday',
                                                                'Start'=>substr($event['start'],0,4).'-'.$event['start'][4].$event['start'][5].'-'.$event['start'][6].$event['start'][7].' 00:00:00',
                                                                'Start timezone'=>'Europe/London',
                                                                'End'=>substr($event['end'],0,4).'-'.$event['end'][4].$event['end'][5].'-'.$event['end'][6].$event['end'][7].' 00:00:00',
                                                                'End timezone'=>'Europe/London',
                                                                'Recurrence'=>'+0 day',
                                                                'Recurrence times'=>0,
                                                                'Recurrence id'=>$id,
                                                                'source'=>$this->baseUri.$eventUri,
                                                                'uid'=>$event['uid'],
                                                                );
                        $eventsArr[$country][$id]['Location/Destination']=array('Country'=>$country);
                    } else {
                        $msg=$eventUri.' failed to parse event "'.$eventIndex.'" defined by: '.$eventStr;
                        $eventsArr[$country]['warning']=(isset($eventsArr[$country]['warning']))?$eventsArr[$country]['warning'].'|'.$msg:$msg;
                    }
                }
            } else {
                $msg=$eventUri.' Content-Type is "'.$icsHeaders['Content-Type'].'" but should be "text/calendar"';
                $eventsArr[$country]['error']=(isset($eventsArr[$country]['error']))?$eventsArr[$country]['error'].'|'.$msg:$msg;
            }
            if (!empty($eventsArr[$country]['error'])){
                $eventsArr[$country]['headers']=$this->headers[$country];
            }
        }
        return $eventsArr;
    }
    
    private function header2arr(array $headers):array
    {
        $arr=[];
        foreach($headers as $key=>$header){
            foreach($header as $index=>$value){
                $values=explode(';',$value);
                foreach($values as $subIndex=>$keyValue){
                    $tmpComps=explode('=',$keyValue);
                    if (count($tmpComps)===2){
                        $arr[$key.'|'.$tmpComps[0]]=$tmpComps[1];
                    } else {
                        if (isset($arr[$key])){
                            if (!is_array($arr[$key])){
                                $arr[$key]=array(0=>$arr[$key]);
                            }
                            $arr[$key][]=$keyValue;
                        } else {
                            $arr[$key]=$keyValue;
                        }
                    }
                }
            }
        }
        return $arr;
    }
}
?>