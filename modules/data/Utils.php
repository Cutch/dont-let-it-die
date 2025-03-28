<?php

if (!function_exists('addId')) {
    function clamp($current, $min, $max)
    {
        return max($min, min($max, $current));
    }
    function addId($data)
    {
        array_walk($data, function (&$v, $k) {
            $v['id'] = $k;
            if (array_key_exists('skills', $v)) {
                $array = [];
                array_walk($v['skills'], function ($iv, $ik) use ($k, &$array, $v) {
                    $keyName = $k . $ik;
                    if ($v['type'] == 'character') {
                        $array[$keyName] = ['id' => $keyName, 'characterId' => $v['name'], ...$iv];
                    } elseif ($v['type'] == 'deck') {
                        $array[$keyName] = ['id' => $keyName, 'cardId' => $v['id'], ...$iv];
                    } else {
                        $array[$keyName] = ['id' => $keyName, ...$iv];
                    }
                });
                $v['skills'] = $array;
            }
        });

        return $data;
    }
    function array_merge_count(...$arrays)
    {
        $build = [];
        foreach ($arrays as $array) {
            foreach ($array as $k => $v) {
                if (array_key_exists($k, $build)) {
                    $build[$k] += $v;
                } else {
                    $build[$k] = $v;
                }
            }
        }
        return $build;
    }
}
