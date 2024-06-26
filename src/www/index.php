<?php
/*  Test page
* @author Carsten Wallenhauer <admin@datapool.info>
* @copyright 2023 to today Carsten Wallenhauer
* @license https://opensource.org/license/mit/ MIT
*/
	
declare(strict_types=1);
	
namespace SourcePot\BankHolidays;
	
mb_internal_encoding("UTF-8");

require_once('../../vendor/autoload.php');

require_once('../php/uk.php');
$uk=new uk();
//$bankHolidays=$uk->getBankHolidays();

require_once('../php/de.php');
$de=new de();
//$bankHolidays=$de->getBankHolidays();

require_once('../php/es.php');
$es=new es();
$bankHolidays=$es->getBankHolidays();

var_dump($bankHolidays);
?>