<?php

if (!function_exists('addId')) {
    function addId($data)
    {
        array_walk($data, function (&$v, $k) {
            $v['id'] = $k;
            if (isset($v['skills'])) {
                $array = [];
                array_walk($v['skills'], function ($iv, $ik) use ($k, &$array, $v) {
                    $keyName = $k . $ik;
                    $array[$keyName] = ['id' => $keyName, 'characterId' => $v['name'], ...$iv];
                });
                $v['skills'] = $array;
            }
        });

        return $data;
    }
}
