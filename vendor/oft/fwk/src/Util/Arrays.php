<?php
/**
 * Copyright (C) 2015 Orange
 *
 * This software is confidential and proprietary information of Orange.
 * You shall not disclose such Confidential Information and shall use it only
 * in accordance with the terms of the agreement you entered into.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 *
 * If you are Orange employee you shall use this software in accordance with
 * the Orange Source Charter (http://opensource.itn.ftgroup/index.php/Orange_Source).
 */

namespace Oft\Util;

class Arrays
{

    /**
     * Fusionne le tableau 2 dans le tableau 1
     *
     * @param array $array1 Premier tableau
     * @param array $array2 Second tableau
     * @throws \RuntimeException
     * @return array
     */
    public static function mergeConfig(array $array1, array $array2)
    {
        foreach ($array2 as $key => $value) {
            if (!is_string($key)) {
                return array_merge($array1, $array2); // Assume an indexed array
            }

            if (!\array_key_exists($key, $array1)) {
                $array1[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                if (!is_array($array1[$key])) {
                    throw new \RuntimeException("Unable to merge an array with a non array");
                }

                $array1[$key] = self::mergeConfig($array1[$key], $value);
                continue;
            }

            $array1[$key] = $value;
        }

        return $array1;
    }

}
