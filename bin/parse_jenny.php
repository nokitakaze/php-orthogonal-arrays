#!/usr/bin/env php
<?php

    function generate_mutations(array $existed, $depth, $count_per_iteration) {
        if ($depth == 0) {
            return $existed;
        }
        if (empty($existed)) {
            for ($i = 2; $i < $count_per_iteration; $i++) {
                $existed[] = [$i];
            }
        } else {
            $a = [];
            foreach ($existed as $exist) {
                $minimal = null;
                foreach ($exist as $value) {
                    if (is_null($minimal)) {
                        $minimal = $value;
                    } else {
                        $minimal = min($minimal, $value);
                    }
                }
                for ($i = 2; $i <= min($count_per_iteration, $minimal); $i++) {
                    $t = $exist;
                    $t[] = $i;
                    $a[] = $t;
                }
            }

            $existed = $a;
        }

        return generate_mutations($existed, $depth - 1, $count_per_iteration);
    }

    function generate_complex($count, $count_per_iteration) {
        $mutations = generate_mutations([], $count, $count_per_iteration);
        $output_buf = [];

        foreach ($mutations as $mutation) {
            echo ".";
            // @todo перебираем пермутации для jenny, там нестабильные результаты для одной и той же геометрии
            $exec = sprintf('J:\\delme\\jenny -n2 %s', implode(' ', $mutation));
            unset($output);
            exec($exec, $output);

            $pairwise_lines = [];
            foreach ($output as $line) {
                $values = [];
                foreach (explode(' ', $line) as $l) {
                    if (!empty($l) and preg_match('_^([0-9]+)([a-zA-Z])$_', trim($l), $a)) {
                        $values[intval($a[1]) - 1] = $a[2];
                    }
                }
                ksort($values);
                $pairwise_lines[] = implode('', $values);
            }
            {
                $need_line = str_repeat('a', strlen($pairwise_lines[0]));
                if (!in_array($need_line, $pairwise_lines)) {
                    for ($ord = ord('b'); $ord <= ord('z'); $ord++) {
                        $found_line = str_repeat(chr($ord), strlen($pairwise_lines[0]));
                        if (in_array($found_line, $pairwise_lines)) {
                            $c = chr($ord);
                            $pairwise_lines = array_map(function ($s) use ($c) {
                                return str_replace([
                                    'a', $c, '!',
                                ], [
                                    '!', 'a', $c,
                                ], $s);
                            }, $pairwise_lines);
                            break;
                        }
                    }
                }
            }
            usort($pairwise_lines, function ($s1, $s2) {
                for ($i = 0; $i < strlen($s1); $i++) {
                    $c1 = substr($s1, $i, 1);
                    $c2 = substr($s2, $i, 1);
                    if ($c1 < $c2) {
                        return -1;
                    } elseif ($c1 > $c2) {
                        return 1;
                    }
                }

                return 0;
            });

            $hash = implode('-', $mutation);
            $output_buf[] = sprintf("                '%s' => '%s',",
                $hash, implode(',', $pairwise_lines));
        }

        return implode("\n", $output_buf);
    }

    $strings = [];
    for ($count = 3; $count <= 7; $count++) {
        $count_per_iteration = 10;

        echo "\ncount = {$count}\t";
        /** @noinspection PhpUnhandledExceptionInspection */
        $string = generate_complex($count, $count_per_iteration);
        $strings[] = sprintf("%d => [\n%s\n            ],\n", $count, $string);
    }
    for ($count = 8; $count <= 10; $count++) {
        $count_per_iteration = 4;

        echo "\ncount = {$count}\t";
        /** @noinspection PhpUnhandledExceptionInspection */
        $string = generate_complex($count, $count_per_iteration);
        $strings[] = sprintf("%d => [\n%s\n            ],\n", $count, $string);
    }

    file_put_contents(__DIR__.'/../src/ArraysDB.php', '<?php

    namespace NokitaKaze\OrthogonalArrays;

    abstract class ArraysDB {
        private static $_db = [
            '.implode("\n            ", $strings).'
        ];

        /**
         * @param integer[] $geometry
         *
         * @return integer[][]|null
         * @throws OrthogonalArraysException
         */
        public static function get_array($geometry) {
            $count = count($geometry);
            if (!isset(self::$_db[$count])) {
                return null;
            }

            $hash = implode(\'-\', $geometry);
            if (!isset(self::$_db[$count][$hash])) {
                return null;
            }

            $indexes = explode(\',\', self::$_db[$count][$hash]);
            $pairs = [];
            foreach ($indexes as $s) {
                $value = [];
                while (!empty($s)) {
                    $c = substr($s, 0, 1);
                    $s = substr($s, 1);
                    $ord = ord($c);
                    if (($ord >= ord(\'a\')) and ($ord <= ord(\'z\'))) {
                        $value[] = $ord - ord(\'a\');
                    } elseif (($ord >= ord(\'A\')) and ($ord <= ord(\'Z\'))) {
                        $value[] = $ord - ord(\'A\') + 26;
                    } else {
                        throw new OrthogonalArraysException();
                    }
                }
                $pairs[] = $value;
            }

            return $pairs;
        }

    }

?>');

    echo "\n\ndone\n";
?>