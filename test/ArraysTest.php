<?php

    namespace NokitaKaze\OrthogonalArrays\Test;

    use NokitaKaze\OrthogonalArrays\Arrays;
    use PHPUnit\Framework\TestCase;

    class ArraysTest extends TestCase {
        /**
         * @param integer[] $geometry
         *
         * @dataProvider dataGenerateN2test
         * @throws \NokitaKaze\OrthogonalArrays\OrthogonalArraysException
         */
        function testGenerateN2($geometry) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $output = Arrays::generateN2($geometry);
            if (count($geometry) > 2) {
                $value = 1;
                $u = 0;
                foreach ($geometry as $g) {
                    $value *= $g;
                    $u += ($g > 1) ? 1 : 0;
                }
                if ($u > 2) {
                    $this->assertLessThan($value, count($output));
                }
                unset($value, $g, $u);
            }
            $this->generateN2_assert($geometry, $output);
        }

        private function generateN2_assert($geometry, $output) {
            foreach ($output as $line) {
                $this->assertEquals(count($geometry), count($line));
                foreach ($geometry as $index => $value) {
                    $this->assertLessThan($value, $line[$index]);
                }
            }

            for ($i1 = 0; $i1 < count($geometry) - 1; $i1++) {
                for ($i2 = $i1 + 1; $i2 < count($geometry); $i2++) {
                    for ($v1 = 0; $v1 < $geometry[$i1]; $v1++) {
                        for ($v2 = 0; $v2 < $geometry[$i2]; $v2++) {
                            $u = false;
                            foreach ($output as $line) {
                                $v1t = $line[$i1];
                                $v2t = $line[$i2];

                                if (($v1t === $v1) and ($v2t === $v2)) {
                                    $u = true;
                                    break;
                                }
                            }

                            $this->assertTrue($u, sprintf('Output array does not contain [%d]=%d;[%d]=%d',
                                $i1, $v1, $i2, $v2
                            ));
                        }
                    }
                }
            }
        }

        /** @noinspection PhpDocRedundantThrowsInspection
         * @param integer[] $geometry
         *
         * @dataProvider dataGenerateN2test
         * @throws \NokitaKaze\OrthogonalArrays\OrthogonalArraysException
         * @throws \ReflectionException
         */
        function testDirect_generateN2($geometry) {
            $reflection = new \ReflectionMethod("\\NokitaKaze\\OrthogonalArrays\\Arrays", "direct_generateN2");
            $reflection->setAccessible(true);

            if (count($geometry) > 2) {
                $value = 1;
                $u = 0;
                foreach ($geometry as $g) {
                    $value *= $g;
                    $u += ($g > 1) ? 1 : 0;
                }
                if ($value >= 100) {
                    $this->markTestSkipped("Too depth array");
                }
                $output = $reflection->invoke(null, $geometry, Arrays::MAXIMUM_VARIANT_PER_ITERATION);
                if ($u > 2) {
                    $this->assertLessThan($value, count($output));
                }
                unset($value, $g, $u);
            } else {
                $output = $reflection->invoke(null, $geometry, Arrays::MAXIMUM_VARIANT_PER_ITERATION);
            }
            $this->generateN2_assert($geometry, $output);
        }

        /**
         * @return integer[][][]
         */
        function dataGenerateN2test() {
            return [
                [[1, 1]],
                [[2, 3]],
                [[3, 3]],
                [[1, 1, 1]],
                [[1, 1, 2]],
                [[1, 2, 1]],
                [[2, 1, 1]],
                [[1, 2, 2]],
                [[2, 2, 2]],
                [[2, 3, 2]],
                [[3, 2, 2]],
                [[3, 3, 3]],
                [[4, 3, 3]],
                [[5, 3, 3]],
                [[6, 3, 3]],
                [[7, 3, 3]],
                [[5, 5, 5, 5, 5]],
            ];
        }

        /**
         * @param array[] $input
         *
         * @dataProvider dataSqueeze
         * @throws \NokitaKaze\OrthogonalArrays\OrthogonalArraysException
         * @throws \ReflectionException
         */
        function testSqueeze($input) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $reflection = new \ReflectionMethod("\\NokitaKaze\\OrthogonalArrays\\Arrays", "get_unique_values_item");
            $reflection->setAccessible(true);

            /** @noinspection PhpUnhandledExceptionInspection */
            $reflection_c = new \ReflectionMethod("\\NokitaKaze\\OrthogonalArrays\\Arrays", "compare_value");
            $reflection_c->setAccessible(true);

            {
                $uniques = [];
                /** @noinspection PhpUnusedLocalVariableInspection */
                foreach ($input[0] as $line) {
                    $uniques[] = [];
                }
                foreach ($input as $line) {
                    foreach ($line as $index => $value) {
                        $uniques[$index][] = $value;
                    }
                }
                $uniques = array_map(function ($input) use ($reflection) {
                    return $reflection->invoke(null, $input);
                }, $uniques);
                unset($line, $reflection, $index, $value);
            }

            /** @noinspection PhpUnhandledExceptionInspection */
            $output = Arrays::squeeze($input);
            $output_indexed = [];
            foreach ($output as $line) {
                $this->assertEquals(count($input[0]), count($line));

                $values = [];
                foreach ($line as $index => $value) {
                    $u = true;
                    foreach ($uniques[$index] as $i => $unique_value) {
                        if ($reflection_c->invoke(null, $value, $unique_value)) {
                            $values[] = $i;
                            $u = false;
                            break;
                        }
                    }
                    $this->assertFalse($u, 'Code flow exception');
                }
                $output_indexed[] = $values;
            }
            unset($values, $line, $value, $u, $i, $index);

            for ($i1 = 0; $i1 < count($uniques) - 1; $i1++) {
                for ($i2 = $i1 + 1; $i2 < count($uniques); $i2++) {
                    for ($v1 = 0; $v1 < count($uniques[$i1]); $v1++) {
                        for ($v2 = 0; $v2 < count($uniques[$i2]); $v2++) {
                            $u = false;
                            foreach ($output as $line) {
                                $v1t = $line[$i1];
                                $v2t = $line[$i2];

                                if (($v1t === $uniques[$i1][$v1]) and ($v2t === $uniques[$i2][$v2])) {
                                    $u = true;
                                    break;
                                }
                            }

                            $this->assertTrue($u, sprintf('Output array does not contain [%d]=%s;[%d]=%s',
                                $i1, $uniques[$i1][$v1], $i2, $uniques[$i2][$v2]
                            ));
                        }
                    }
                }
            }
        }

        function dataSqueeze() {
            return [
                [[['Apple', 'smartphone'], ['Android', 'tablet'], ['Apple', 'tablet'],]],
                [[['pig', true], ['monkey', false], ['human', true],]],
                [[['pig', true], [null, true], ['human', true],]],
                [[[1, true], [1, true], [1, true],]],
            ];
        }

        function dataGet_unique_values() {
            return [
                [
                    ['Apple', 'Samsung', 'Xiaomi'],
                    ['Apple', 'Samsung', 'Xiaomi'],
                ],
                [
                    ['Apple', 'Samsung', 'Xiaomi', 'Samsung'],
                    ['Apple', 'Samsung', 'Xiaomi'],
                ],
                [
                    [1, 2, 3, 1, 2, 3, 1, 2, 3],
                    [1, 2, 3],
                ],
                [
                    [true, false, null],
                    [true, false, null],
                ],
                [
                    ["", null, "", null, "", ""],
                    ["", null],
                ],
                [
                    [1, 2, 3, null, 1, 2, 3],
                    [1, 2, 3, null],
                ],

            ];
        }

        /**
         * @param array $input
         * @param array $expected
         *
         * @dataProvider dataGet_unique_values
         * @throws \ReflectionException
         */
        function testGet_unique_values_item(array $input, array $expected) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $reflection = new \ReflectionMethod("\\NokitaKaze\\OrthogonalArrays\\Arrays", "get_unique_values_item");
            $reflection->setAccessible(true);
            $output = $reflection->invoke(null, $input);

            sort($output);
            sort($expected);
            $this->assertEquals(count($expected), count($output));
            foreach ($expected as $index => $expected_value) {
                if (is_null($expected_value)) {
                    $this->assertNull($output[$index]);
                } elseif ($expected_value === true) {
                    $this->assertTrue($output[$index]);
                } elseif ($expected_value === false) {
                    $this->assertFalse($output[$index]);
                } else {
                    $this->assertEquals($expected_value, $output[$index]);
                }
            }
        }

    }

?>