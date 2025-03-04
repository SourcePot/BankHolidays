<?php
/*
* @author Carsten Wallenhauer <admin@datapool.info>
* @copyright 2023 to today Carsten Wallenhauer
* @license https://opensource.org/license/mit/ MIT
*/
declare(strict_types=1);

namespace SourcePot\BankHolidays;

class holidays{

    private $holidayObj;
    
    public function __construct(int|NULL $relevantYear=NULL, string $countryCode='DE')
    {
        $this->holidayObj=$this->getObj($relevantYear,$countryCode);
    }

    static private function getObj(int|NULL $relevantYear=NULL, string $countryCode='DE')
    {
        $countryCode=strtolower($countryCode);
        $classFile=$countryCode.'.php';
        require_once($classFile);
        $class='\SourcePot\BankHolidays\\'.$countryCode;
        if (class_exists($class)){
            return new $class($relevantYear);
        } else {
            throw new \Exception('Class for country code "'.$countryCode.'" missing.');
        }
    }

    static public function getAvailableCountries():array
    {
        $result=[];
        $files=scandir(__DIR__);
        foreach($files as $file){
            preg_match('/(^[a-z]{2})\.php/',$file,$match);
            if (!empty($match[0])){
                require_once($match[0]);
                $class='\SourcePot\BankHolidays\\'.$match[1];
                $result[$match[1]]=$class::getCountry();
            }
        }
        return $result;
    }

    static public function getAvailableRegions(string $countryCode='DE'):array
    {
        $holidayObj=self::getObj(intval(date('Y')),$countryCode);
        return $holidayObj::getRegions();
    }
    
    public function getHolidays(string $region):\Iterator
    {
        return $this->holidayObj->bankHolidays($region);
    }

    public function datapoolHolidays(string $region, array $entryTemplate=[], string $dbDateTimeZoneName='UTC'):\Iterator
    {
        $dbDateTimeZone=new \DateTimeZone($dbDateTimeZoneName);
        $expiresObj=new \DateTime('now');
        $expiresObj->add(new \DateInterval('P1Y'));
        $expiresObj->setTimezone($dbDateTimeZone);
        // init entry template
        $entryTemplate['Source']=$entryTemplate['Source']??'calendar';
        $entryTemplate['Read']=$entryTemplate['Read']??'ALL_R';
        $entryTemplate['Write']=$entryTemplate['Write']??'ADMIN_R';
        $entryTemplate['Expires']=$entryTemplate['Expires']??($expiresObj->format('Y-m-d H:i:s'));
        $entryTemplate['Timezone']=$dbDateTimeZoneName;
        foreach($this->holidayObj->bankHolidays($region) as $event){
            // normalize timezone
            $startObj=new \DateTime($event['Start'],new \DateTimeZone($event['Start timezone']));
            $startObj->setTimezone($dbDateTimeZone);
            $endObj=new \DateTime($event['End'],new \DateTimeZone($event['End timezone']));
            $endObj->setTimezone($dbDateTimeZone);
            // crete entry
            $entry=$entryTemplate;
            $entry['EntryId']=md5(serialize($event));
            $entry['Start']=$startObj->format('Y-m-d H:i:s');
            $entry['End']=$endObj->format('Y-m-d H:i:s');
            $entry['Name']=$event['Name'];
            unset($event['Name']);
            $entry['Group']=$event['Type'].'s';
            $entry['Folder']=$this->holidayObj::class;
            $entry['Content']['Location/Destination']=['Town'=>$event['Region'],'Country'=>$event['Country'],];
            unset($event['Region']);
            unset($event['Country']);
            $entry['Content']['Event']=$event;
            $entry['Content']['Event']['Description']=$entry['Name'].' ('.$region.')';
            yield $entry;
        }  
    }

    public function value2html($val,string $caption='Caption'):string
    {   
        if (!is_array($val)){
            $val=['value'=>$val];
        }
        $html='<table>';
        $html.='<caption>'.$caption.'</caption>';
        foreach($val as $key=>$value){
            if (is_bool($value)){
                $value=($value)?'TRUE':'FALSE';
                $html.='<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';
            } else if (is_array($value)){

            } else {
                $html.='<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';
            }
        }
        $html.='</table>';        
        return $html;
    }

}
?>