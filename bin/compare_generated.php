#!/usr/bin/env php
<?php
    require_once __DIR__.'/../vendor/autoload.php';

    /** @noinspection PhpUnhandledExceptionInspection */
    $reflection = new \ReflectionMethod("\\NokitaKaze\\OrthogonalArrays\\Arrays", "direct_generateN2");
    $reflection->setAccessible(true);

    $geometries = [
        [3, 2, 2],
        [3, 3, 2],
        [3, 3, 3],
        [4, 3, 3],
        [2, 2, 2, 2],
        [3, 2, 2, 2],
        [3, 3, 3, 3],
        [4, 4, 4, 4],
        [4, 2, 2, 2],
        [4, 3, 2, 2],
        [4, 3, 3, 2],
        [4, 3, 3, 3],
        [4, 4, 3, 2],
        [5, 4, 2, 2],
        [5, 4, 3, 2],
        [5, 4, 4, 2],
        [7, 7],
        [7, 7, 7],
    ];

    foreach ($geometries as $geometry) {
        echo '['.implode(", ", $geometry)."]\t";
        $count_i = null;
        $min = null;
        foreach ([
                     4, 100, 125, 150, 175, 200, 500,// 1000, 10000,
                 ] as $wide) {
            $output = $reflection->invoke(null, $geometry, $wide);
            $count = count($output);
            if (is_null($count_i)) {
                $count_i = $count;
                $min = $count;
            } elseif ($count != $count_i) {
                echo ($count < $min) ? '<' : (($count == $min) ? '=' : '>');
                $min = min($min, $count);
            } else {
                echo '.';
            }
        }
        echo "\t{$min}\n";
    }

    echo "\n\ndone\n";
?>