<?php
namespace Bga\Games\DontLetItDie;

use Exception;

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
                    $array[$keyName] = [
                        ...$array[$keyName],
                        'parentId' => $v['id'],
                        'parentName' => array_key_exists('name', $v) ? $v['name'] : null,
                    ];
                });
                $v['skills'] = $array;
            }
            if (array_key_exists('track', $v)) {
                array_walk($v['track'], function (&$v, $k) {
                    $v['id'] = $k;
                });
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
    function uuidv4()
    {
        $data = random_bytes(16);

        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // set version to 0100
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    function notifyTextButton(array $obj): string
    {
        $name = $obj['name'];
        $dataId = $obj['dataId'];
        $dataType = $obj['dataType'];
        if (!array_search($dataType, ['character', 'item', 'hindrance', 'unlock', 'day-event', 'night-event', 'card'])) {
            throw new Exception('Bad dataType');
        }
        return "<span class=\"dlid__log-button\" data-id=\"$dataId\" data-type=\"$dataType\">$name</span>";
    }
    function notifyButtons($arr): string
    {
        return join(
            '',
            array_map(function ($obj) {
                return notifyTextButton($obj);
            }, $arr)
        );
    }
    function buildInsertQuery(string $table, array $data)
    {
        $keys = [];
        $values = [];
        foreach ($data as $key => $value) {
            array_push($keys, "`{$key}`");
            array_push($values, "'{$value}'");
        }
        $values = implode(',', $values);
        return "INSERT INTO `$table` VALUES ({$values})";
    }
}
