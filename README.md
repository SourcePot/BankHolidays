# Bank Holidays

The repository contains the PHP class `SourcePot\BankHolidays\holidays` which provides country and regions specific holidays as event-arrays. The country-specific bank holidays are derived from different sources such as the goverment (for the UK from *Gov.uk*) or calculated from rules. 

Depending on the country, dates are derived from rules (such as for Germany) or a goverment internet resource is used (such as *Gov.uk*).

You can install the www-project using composer: `composer create-project sourcepot/bankholidays {target dir}`
Just set your www-root directory to `{target dir}/src/www/`.

>[!NOTE]
>The UK bank holidays cover only a couple of years into the past and future0.

## Code samples

The following code sample creates an instance of the holiday object for the year *2025* and country *Germany*. Method `SourcePot\BankHolidays\holidays→getHolidays('Bavaria')` is a holiday iterator, returning an holiday event with every iteration.

```
namespace SourcePot\BankHolidays;
	
require_once('../php/holidays.php');

$year=2025;
$country='de';

$holidayObj = new holidays($year,$country);

foreach($holidayObj->getHolidays('Bavaria') as $event){
    var_dump($event);
}
```

## Test web page

File `./www/index.php` can be used to evaluate the respository.

![Web page screenshot](./assets/uk-sample-result.png)