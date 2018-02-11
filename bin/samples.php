#!/usr/bin/env php
<?php
    require_once __DIR__.'/../vendor/autoload.php';

    $output = \NokitaKaze\OrthogonalArrays\Arrays::generateN2_values([
        ['human', 'cat'],
        ['boy', 'girl'],
        [true, false],
    ]);
    foreach ($output as $line) {
        echo implode(', ', $line).";\n";
    }

    echo "\n\n";
    $output = \NokitaKaze\OrthogonalArrays\Arrays::generateN2_values([
        ['female', 'male'],
        ['catgirl'],
        [null, 10, 100500],
    ]);
    foreach ($output as $line) {
        echo implode(', ', $line).";\n";
    }

    echo "\n\n";
    $output = \NokitaKaze\OrthogonalArrays\Arrays::squeeze([
        ['USA', 'SpaceX'],
        ['USA', 'NASA'],
        ['Russia', 'Roscosmos'],
        ['Poland', null],
    ]);
    foreach ($output as $line) {
        echo implode(', ', $line).";\n";
    }

?>