<?php
/*
* @author Carsten Wallenhauer <admin@datapool.info>
* @copyright 2023 to today Carsten Wallenhauer
* @license https://opensource.org/license/mit/ MIT
*/
	
declare(strict_types=1);
	
namespace SourcePot\Bankholidays;
	
mb_internal_encoding("UTF-8");

require_once('../../vendor/autoload.php');
require_once('../php/uk.php');
require_once('../php/de.php');

$uk=new uk();
var_dump($uk->getBankHolidays());

/*
$de=new de();
var_dump($de->getBankHolidays());
*/

?>