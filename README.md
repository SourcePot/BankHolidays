# Bank Holidays

The repository contains the PHP class `SourcePot\BankHolidays\holidays` which provides holidays specific to a selected country and regionas event-array. Country-specific bank holidays are derived from different sources, e.g. for the UK from *Gov.uk*, for Germany the days are calculated based on rules. 

Depending on the country, a goverment internet resource is used (such as *Gov.uk*) or dates are derived from rules (such as for Germany).

You can install the www-project using composer: `composer create-project sourcepot/bankholidays {target dir}`
Just set your www-root directory to `{target dir}/src/www/`.

## Code samples

The following code dample creates an instance of the holiday object for the year 2025 and country Germany. 

```
namespace SourcePot\BankHolidays;
	
require_once('../php/holidays.php');

$holidayObj = new holidays(2025,'de');

foreach($holidayObj->getHolidays('Bavaria') as $event){
    var_dump($event);
}

```

## Test web page

File `./www/index.php` can be used to evaluate the respository.

$bankHolidaysUK will then return an associative array with the bank holidays of the past, current and next year (see the example below).
The keys of the associative array are: *array({country}=>array({eventId},...),...)* The sub-keys *{Event}* and *{Location/Destination}* are compatible with the Content of a Datapool calendar entry.
*{eventId}* can be used as *EntryId* of a Datapool calendar entry.

![Web page screenshot](./assets/uk-sample-result.png)