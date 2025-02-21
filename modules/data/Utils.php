<?php

if (!function_exists('addId')) {
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
                    } else {
                        $array[$keyName] = ['id' => $keyName, ...$iv];
                    }
                });
                $v['skills'] = $array;
            }
        });

        return $data;
    }
}
