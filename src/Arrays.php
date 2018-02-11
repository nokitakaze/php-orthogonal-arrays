<?php

    namespace NokitaKaze\OrthogonalArrays;

    /**
     * Class Arrays
     * @package NokitaKaze\OrthogonalArrays
     */
    abstract class Arrays {
        const MAXIMUM_VARIANT_PER_ITERATION = 200;

        /**
         * @param integer[] $input
         * @param integer   $iteration_size_limit
         *
         * @return integer[][]
         * @throws OrthogonalArraysException
         * @throws \InvalidArgumentException
         */
        public static function generateN2(array $input, $iteration_size_limit = self::MAXIMUM_VARIANT_PER_ITERATION) {
            foreach ($input as $value) {
                if (!is_int($value)) {
                    throw new \InvalidArgumentException("Input array must be int[]");
                }
            }
            {
                $input2 = $input;
                arsort($input2);

                $keys = array_keys($input2);
                $geometry = array_values($input2);
                unset($input2);
            }
            $additional_keys = [];
            while (!empty($geometry)) {
                $last_count = $geometry[count($geometry) - 1];
                if ($last_count == 1) {
                    $additional_keys[] = $keys[count($geometry) - 1];
                    unset($geometry[count($geometry) - 1]);
                } else {
                    break;
                }
            }
            $additional_keys = array_reverse($additional_keys);
            $db_value = [];
            if (count($geometry) == 1) {
                $db_value = [];
                for ($i = 0; $i < $geometry[0]; $i++) {
                    $db_value[] = [$i];
                }
                unset($a, $i);
            } elseif (count($geometry) == 2) {
                $db_value = [];
                for ($i = 0; $i < $geometry[0]; $i++) {
                    for ($j = 0; $j < $geometry[1]; $j++) {
                        $db_value[] = [$i, $j];
                    }
                }
                unset($a, $i);
            } elseif (!empty($geometry)) {
                /** @noinspection PhpUndefinedClassInspection */
                $db_value = ArraysDB::get_array($geometry);
                if (is_null($db_value)) {
                    $db_value = self::direct_generateN2($geometry, $iteration_size_limit);
                }
            }
            $additional_keys_count = count($additional_keys);
            for ($i = 0; $i < $additional_keys_count; $i++) {
                if (empty($db_value)) {
                    $db_value = [[0]];
                } else {
                    foreach ($db_value as &$value) {
                        $value[] = 0;
                    }
                    unset($value);
                }
            }
            unset($additional_keys_count);

            $real_values = null;
            foreach ($keys as $i => $o) {
                if ($i !== $o) {
                    $real_values = array_map(function ($line) use ($keys) {
                        $new_line = [];
                        foreach ($keys as $old_value => $new_value) {
                            $new_line[$new_value] = $line[$old_value];
                        }
                        ksort($new_line);

                        return $new_line;
                    }, $db_value);
                    break;
                }
            }

            return !is_null($real_values) ? $real_values : $db_value;
        }

        /**
         * @param integer[] $geometry
         * @param integer   $iteration_size_limit
         *
         * @return integer[][]
         * @throws OrthogonalArraysException
         * @doc https://habrahabr.ru/post/187882/
         *
         * Ведёт себя неоптимально на размере в 2, там оптимально просто перебрать
         */
        protected static function direct_generateN2(array $geometry, $iteration_size_limit) {
            $full_mutation_left = self::generate_all_permutations_iteration([], $geometry);

            $output = [];
            while (!empty($full_mutation_left)) {
                /**
                 * @var integer $max_line_select Количество линий, которые добавятся к массиву
                 */
                {
                    $mutation_count = 0;
                    $mutation_count_i = 1;
                    $max_line_select = 1;
                    $full_mutation_left_count = count($full_mutation_left);
                    for ($i = 1; $i < $full_mutation_left_count; $i++) {
                        $mutation_count_i = $mutation_count_i * ($full_mutation_left_count + 1 - $i) / $i;
                        $mutation_count += $mutation_count_i;
                        if ($mutation_count > $iteration_size_limit) {
                            break;
                        } else {
                            $max_line_select = $i;
                        }
                    }
                    unset($i, $mutation_count, $mutation_count_i, $full_mutation_left_count);
                }

                $max_line_select = max(min($max_line_select, count($full_mutation_left) - 1), 1);

                $sets = [];

                for ($select_line_count = 1; $select_line_count <= $max_line_select; $select_line_count++) {
                    $all_lines = self::select_all_lines_permutations($full_mutation_left, $select_line_count);
                    $best_set = null;
                    $best_left = null;

                    foreach ($all_lines as $single_lines_set) {
                        $temporary = array_merge($output, $single_lines_set);
                        $full_mutation_left_this = self::remove_useless_lines($temporary, $full_mutation_left);
                        if (is_null($best_left)) {
                            $best_set = $single_lines_set;
                            $best_left = $full_mutation_left_this;
                        } elseif (count($best_left) + count($best_set) >
                                  count($full_mutation_left_this) + count($single_lines_set)) {
                            $best_set = $single_lines_set;
                            $best_left = $full_mutation_left_this;
                        }
                    }

                    $sets[$select_line_count] = [$best_set, $best_left];
                }
                unset($best_left, $best_set, $select_line_count, $all_lines,
                    $max_line_select, $temporary, $single_lines_set);
                usort($sets, function ($set1, $set2) {
                    $v1 = count($set1[0]) + count($set1[1]);
                    $v2 = count($set2[0]) + count($set2[1]);
                    if ($v1 < $v2) {
                        return -1;
                    } elseif ($v1 > $v2) {
                        return 1;
                    } else {
                        return (count($set1[0]) > count($set1[1])) ? -1 : 1;
                    }
                });
                list($best_set, $best_left) = $sets[array_keys($sets)[0]];

                if (is_null($best_set)) {
                    // @codeCoverageIgnoreStart
                    throw new OrthogonalArraysException("Code flow Exception");
                    // @codeCoverageIgnoreEnd
                }
                foreach ($best_set as $line) {
                    $output[] = $line;
                }
                $full_mutation_left = $best_left;
            }

            usort($output, function ($a, $b) {
                $a_count = count($a);
                for ($i = 0; $i < $a_count; $i++) {
                    if ($a[$i] < $b[$i]) {
                        return -1;
                    } elseif ($a[$i] > $b[$i]) {
                        return 1;
                    }
                }

                return 0;
            });

            return $output;
        }

        /**
         * @param integer[][] $existed
         * @param integer[]   $geometry
         *
         * @return integer[][]
         */
        protected static function generate_all_permutations_iteration(
            array $existed,
            array $geometry
        ) {
            if (empty($geometry)) {
                return $existed;
            }
            $index = array_shift($geometry);
            if (empty($existed)) {
                for ($i = 0; $i < $index; $i++) {
                    $existed[] = [$i];
                }
            } else {
                $a = [];
                for ($i = 0; $i < $index; $i++) {
                    foreach ($existed as $exist) {
                        $exist[] = $i;
                        $a[] = $exist;
                    }
                }
                $existed = $a;
            }

            return self::generate_all_permutations_iteration($existed, $geometry);
        }

        /**
         * @param array   $lines
         * @param integer $select_line_count
         *
         * @return array[][]
         */
        protected static function select_all_lines_permutations(
            $lines,
            $select_line_count
        ) {
            $full_exist = [];
            self::select_all_lines_permutations_iteration($lines, $select_line_count, 0, [], $full_exist);

            return $full_exist;
        }

        /**
         * @param array   $lines
         * @param integer $select_line_count
         * @param integer $min_index
         * @param array   $exist
         * @param array   $full_exist
         */
        protected static function select_all_lines_permutations_iteration(
            $lines,
            $select_line_count,
            $min_index = 0,
            array $exist = [],
            array &$full_exist = []
        ) {
            if ($select_line_count == 0) {
                $full_exist[] = $exist;

                return;
            }
            $lines_count = count($lines);
            for ($i = $min_index; $i < $lines_count; $i++) {
                $this_exist = $exist;
                $this_exist[] = $lines[$i];

                self::select_all_lines_permutations_iteration($lines, $select_line_count - 1,
                    $i + 1, $this_exist, $full_exist);
            }
        }

        /**
         * @param array[] $set
         * @param array[] $origin_array
         *
         * @return array[]
         */
        protected static function remove_useless_lines(array $set, array $origin_array) {
            if (empty($set)) {
                // @codeCoverageIgnoreStart
                return $origin_array;
                // @codeCoverageIgnoreEnd
            }
            $row_count = count($set[0]);
            $indexes = [];
            // @todo если я захочу делать бесконечную степень свободы, я должен копать отсюда
            for ($i = 0; $i < $row_count - 1; $i++) {
                for ($j = $i + 1; $j < $row_count; $j++) {
                    $indexes[] = [$i, $j];
                }
            }

            $existed_pairs = [];
            foreach ($indexes as $index_id => $index) {
                $this_set = [];
                foreach ($set as $value) {
                    // @todo бесконечные степени свободы
                    $this_set[] = [$value[$index[0]], $value[$index[1]]];
                }
                $filtered = [];

                $this_set_count = count($this_set);
                for ($i = 0; $i < $this_set_count; $i++) {
                    $u = true;
                    for ($j = 0; $j < $i; $j++) {
                        if (self::compare_chunk($this_set[$i], $this_set[$j])) {
                            $u = false;
                            break;
                        }
                    }

                    if ($u) {
                        $filtered[] = $this_set[$i];
                    }
                }

                $existed_pairs[] = $filtered;
            }

            $left = [];
            foreach ($origin_array as $origin_line) {
                $u = false;
                foreach ($indexes as $index_id => $index) {
                    $this_set = [$origin_line[$index[0]], $origin_line[$index[1]]];
                    $u1 = false;
                    foreach ($existed_pairs[$index_id] as $in_index_chunk) {
                        if (self::compare_chunk($in_index_chunk, $this_set)) {
                            $u1 = true;
                            break;
                        }
                    }

                    if (!$u1) {
                        $u = true;
                        break;
                    }
                }

                if ($u) {
                    $left[] = $origin_line;
                }
            }

            return $left;
        }

        /**
         * @param array $a
         * @param array $b
         *
         * @return boolean
         */
        protected static function compare_chunk(array $a, array $b) {
            foreach ($a as $num => $value) {
                if (!self::compare_value($value, $b[$num])) {
                    return false;
                }
            }

            return true;
        }

        /**
         * @param mixed $a
         * @param mixed $b
         *
         * @return boolean
         */
        protected static function compare_value($a, $b) {
            if (is_null($a)) {
                return is_null($b);
            } elseif (is_null($b)) {
                return is_null($a);
            } else {
                return ($a === $b);
            }
        }

        /**
         * @param array[] $values
         * @param integer $iteration_size_limit
         *
         * @return array[]
         * @throws OrthogonalArraysException
         * @throws \InvalidArgumentException
         */
        public static function generateN2_values(array $values, $iteration_size_limit = self::MAXIMUM_VARIANT_PER_ITERATION) {
            $geometry = [];
            foreach ($values as $value) {
                $geometry[] = count($value);
            }

            $output_raw = self::generateN2($geometry, $iteration_size_limit);
            $output = [];
            foreach ($output_raw as $line) {
                $output_line = [];
                foreach ($line as $i => $index) {
                    $output_line[] = $values[$i][$index];
                }

                $output[] = $output_line;
            }

            return $output;
        }

        /**
         * @param array[] $values
         * @param integer $iteration_size_limit
         *
         * @return array[]
         * @throws OrthogonalArraysException
         * @throws \InvalidArgumentException
         */
        public static function squeeze(array $values, $iteration_size_limit = self::MAXIMUM_VARIANT_PER_ITERATION) {
            $unique_values = self::get_unique_values($values);

            return self::generateN2_values($unique_values, $iteration_size_limit);
        }

        protected static function get_unique_values_item(array $input) {
            $filtered = [];
            foreach ($input as $value) {
                $u = false;
                foreach ($filtered as $filtered_value) {
                    if (self::compare_value($value, $filtered_value)) {
                        $u = true;
                        break;
                    }
                }

                if (!$u) {
                    $filtered[] = $value;
                }
            }

            return $filtered;
        }

        protected static function get_unique_values(array $values) {
            $unique_values = [];
            $line_width = count($values[0]);
            for ($i = 0; $i < $line_width; $i++) {
                $unique_values[] = [];
            }
            foreach ($values as $value) {
                foreach ($value as $index => $sub_value) {
                    $unique_values[$index][] = $sub_value;
                }
            }
            foreach ($unique_values as &$unique_sub_values) {
                $unique_sub_values = self::get_unique_values_item($unique_sub_values);
            }

            return $unique_values;
        }
    }

?>