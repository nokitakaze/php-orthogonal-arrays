# Orthogonal arrays generation

## Current status
### General
[![Build Status](https://secure.travis-ci.org/nokitakaze/php-orthogonal-arrays.png?branch=master)](http://travis-ci.org/nokitakaze/php-orthogonal-arrays)
![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nokitakaze/php-orthogonal-arrays/badges/quality-score.png?b=master)
![Code Coverage](https://scrutinizer-ci.com/g/nokitakaze/php-orthogonal-arrays/badges/coverage.png?b=master)
<!-- [![Latest stable version](https://img.shields.io/packagist/v/nokitakaze/orthogonal_arrays.svg?style=flat-square)](https://packagist.org/packages/nokitakaze/orthogonal_arrays) -->

## Usage
At first
```bash
composer require nokitakaze/orthogonal_arrays
```

And then
```php
$output = \NokitaKaze\OrthogonalArrays\Arrays::generateN2_values([
	['human', 'cat'],
	['boy', 'girl'],
	[true, false],
]);
foreach ($output as $line) {
	echo implode(', ', $line).";\n";
}
/* Output:
human, boy, 1;
human, girl, 1;
human, girl, ;
cat, boy, ;
cat, girl, 1;
*/

$output = \NokitaKaze\OrthogonalArrays\Arrays::generateN2_values([
	['female', 'male'],
	['catgirl'],
	[null, 10, 100500],
]);
foreach ($output as $line) {
	echo implode(', ', $line).";\n";
}
/* Output:
female, catgirl, ;
male, catgirl, ;
female, catgirl, 10;
male, catgirl, 10;
female, catgirl, 100500;
male, catgirl, 100500;
*/

$output = \NokitaKaze\OrthogonalArrays\Arrays::squeeze([
	['USA', 'SpaceX'],
	['USA', 'NASA'],
	['Russia', 'Roscosmos'],
	['Poland', null],
]);
foreach ($output as $line) {
	echo implode(', ', $line).";\n";
}
/* Output:
USA, SpaceX;
Russia, SpaceX;
Poland, SpaceX;
USA, NASA;
Russia, NASA;
Poland, NASA;
USA, Roscosmos;
Russia, Roscosmos;
Poland, Roscosmos;
USA, ;
Russia, ;
Poland, ;
*/
```