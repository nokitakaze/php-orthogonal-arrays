#!/usr/bin/env php
<?php
    require_once __DIR__.'/../vendor/autoload.php';

    /** @noinspection PhpUnhandledExceptionInspection */
    $reflection = new \ReflectionMethod("\\NokitaKaze\\OrthogonalArrays\\Arrays", "direct_generateN2");
    $reflection->setAccessible(true);

    for ($i = 2; $i < 30; $i++) {
        $geometry = [$i, 2, 2];
        $a = microtime(true);
        $reflection->invoke(null, $geometry, 10000);
        $b = microtime(true);

        $ts = $b - $a;
        if ($ts < 5) {
            $max_count = ceil((20 - $ts) / $ts);
            $a = microtime(true);
            for ($j = 0; $j < $max_count; $j++) {
                $reflection->invoke(null, $geometry, 10000);
            }
            $b = microtime(true);
            $ts += $b - $a;
            $ts /= ($max_count + 1);
        } else {
            $max_count = 0;
        }

        echo "{$i}\t{$ts} sec\t{$max_count} additional\n";
    }

    echo "\n\ndone\n";
?>