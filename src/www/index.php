<?php
/*  Test page
* @author Carsten Wallenhauer <admin@datapool.info>
* @copyright 2023 to today Carsten Wallenhauer
* @license https://opensource.org/license/mit/ MIT
*/
	
declare(strict_types=1);
	
namespace SourcePot\BankHolidays;
	
mb_internal_encoding("UTF-8");

require_once('../php/main.php');

$availableCountries=main::getAvailableCountries();
if (empty($_POST['country-code'])){$countryCode='de';} else {$countryCode=$_POST['country-code'];}
if (empty($_POST['region'])){$region='Bavaria';} else {$region=$_POST['region'];}

$html='<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml" lang="en"><head><meta charset="utf-8"><title>Holidays</title><link type="text/css" rel="stylesheet" href="index.css"/></head>';
$html.='<body><form name="892d183ba51083fc2a0b3d4d6453e20b" id="892d183ba51083fc2a0b3d4d6453e20b" method="post" enctype="multipart/form-data">';
$html.='<h1>Evaluation Page for the Bank holiday-Package</h1>';
$html.='<div class="control">';
$html.='<select name="country-code" id="country-code">';
foreach($availableCountries as $code=>$name){
    $selected=($code===$countryCode)?' selected':'';
    $html.='<option value="'.$code.'"'.$selected.'>'.$name.'</option>';
}
$html.='</select>';
$html.='<input type="submit" name="set" id="set" style="margin:0.25em;" value="Set"/></div>';
$html.='</div>';
$html.='</form>';


$bankHolidayObj=new main(intval(date('Y')),$countryCode);

$regions=main::getAvailableRegions($countryCode);
$html.=$bankHolidayObj->value2html($regions,'Available regions in '.$availableCountries[$countryCode]);

$selectedRegion=$regions[array_rand($regions,1)];
foreach($bankHolidayObj->datapoolHolidays($selectedRegion) as $event){
    $html.=$bankHolidayObj->value2html($event,'Event "'.$event['Name'].'" in '.$selectedRegion);
}


$html.='</body></html>';
echo $html;

?>