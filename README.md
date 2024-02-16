# Bank Holidays

There is one PHP class per country. These classes contain the method getBankHolidays() which returns an array with the bank holidays.
If no argumenmt is provided method getBankHolidays() returns at least the bank holidays of the current year.

## How to use the PHP classes?

For bank holidays of the United Kongdom you can instantiate class uk as follows:
`require_once('../../vendor/autoload.php');`
`require_once('../php/uk.php');`
`$uk=new uk();`
`$bankHolidaysUK=$uk->getBankHolidays();`
