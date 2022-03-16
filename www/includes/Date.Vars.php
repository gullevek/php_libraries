<?php

/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2005/07/19
* SHORT DESCRIPTION:
* preset date variables
* HISTORY:
*********************************************************************/

declare(strict_types=1);

/******
All moved to CoreLibs\Combined\DateTime
DAY_SHORT
DAY_LONG
MONTH_LONG
MONTH_SHORT
*******/
trigger_error(
	'Date.VArs.php is deprecated. '
		. 'Use CoreLibs\Combined\DateTime:: with upper case old variable name',
	E_USER_DEPRECATED
);

$day_short = [
	1 => 'Mon',
	2 => 'Tue',
	3 => 'Wed',
	4 => 'Thu',
	5 => 'Fri',
	6 => 'Sat',
	7 => 'Sun'
];

$day_long = [
	1 => 'Monday',
	2 => 'Tuesday',
	3 => 'Wednesday',
	4 => 'Thursday',
	5 => 'Friday',
	6 => 'Saturday',
	7 => 'Sunday'
];

// months
$month_long = [
	1 => 'January',
	2 => 'February',
	3 => 'March',
	4 => 'April',
	5 => 'May',
	6 => 'June',
	7 => 'July',
	8 => 'August',
	9 => 'September',
	10 => 'October',
	11 => 'November',
	12 => 'December'
];

$month_short = [
	1 => 'Jan',
	2 => 'Feb',
	3 => 'Mar',
	4 => 'Apr',
	5 => 'May',
	6 => 'Jun',
	7 => 'Jul',
	8 => 'Aug',
	9 => 'Sep',
	10 => 'Oct',
	11 => 'Nov',
	12 => 'Dec'
];

// __END__
