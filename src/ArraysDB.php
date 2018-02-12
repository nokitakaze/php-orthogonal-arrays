<?php

    namespace NokitaKaze\OrthogonalArrays;

    abstract class ArraysDB {
        private static $_db = null;

        private static function init_db() {
            if (!is_null(self::$_db)) {
                return;
            }

            self::$_db = unserialize(file_get_contents(__DIR__.'/precomputed.dat'));
        }

        /**
         * @param integer[] $geometry
         *
         * @return integer[][]|null
         * @throws OrthogonalArraysException
         */
        public static function get_array($geometry) {
            self::init_db();
            $count = count($geometry);
            if (!isset(self::$_db[$count])) {
                return null;
            }

            $hash = implode('-', $geometry);
            if (!isset(self::$_db[$count][$hash])) {
                return null;
            }

            $indexes = explode(',', self::$_db[$count][$hash]);
            $pairs = [];
            foreach ($indexes as $s) {
                $value = [];
                while (!empty($s)) {
                    $c = substr($s, 0, 1);
                    $s = substr($s, 1);
                    $ord = ord($c);
                    if (($ord >= ord('a')) and ($ord <= ord('z'))) {
                        $value[] = $ord - ord('a');
                    } elseif (($ord >= ord('A')) and ($ord <= ord('Z'))) {
                        $value[] = $ord - ord('A') + 26;
                    } else {
                        throw new OrthogonalArraysException();
                    }
                }
                $pairs[] = $value;
            }

            return $pairs;
        }

    }

?>