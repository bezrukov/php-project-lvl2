<?php

declare(strict_types=1);

namespace Differ\Formatters\pretty;

use const Differ\Formatters\TYPE_ADDED;
use const Differ\Formatters\TYPE_CHANGED;
use const Differ\Formatters\TYPE_COMPLEXITY;
use const Differ\Formatters\TYPE_NOT_CHANGED;
use const Differ\Formatters\TYPE_REMOVED;

function formatArrayValue(array $data, $deep = ''): string
{
    $view = array_reduce(
        array_keys($data),
        static function ($acc, $key) use ($data, $deep) {
            $acc[] = "{$deep}    {$key}: {$data[$key]}";

            return $acc;
        },
        ['{']
    );
    $view[] = $deep . '}';

    return implode("\n", $view);
}

const INDENTATION = '    ';

function formatValueToSting($value, $deep): string
{
    $valueType = gettype($value);
    switch ($valueType) {
        case 'array':
            return formatArrayValue($value, $deep . INDENTATION);
        case 'boolean':
            return json_encode($value);
        default:
            return $value;
    }
}

function formatToString($key, $value, $deep, $prefix)
{
    $value = formatValueToSting($value, $deep);

    return sprintf("{$deep}  {$prefix} {$key}: {$value}");
}

function formatToPretty(array $data, $deep = ''): string
{
    $view = array_map(
        static function ($elem) use ($deep) {
            switch ($elem['type']) {
                case TYPE_NOT_CHANGED:
                    return formatToString($elem['key'], $elem['value'], $deep, ' ');
                case TYPE_REMOVED:
                    return formatToString($elem['key'], $elem['value'], $deep, '-');
                case TYPE_ADDED:
                    return formatToString($elem['key'], $elem['value'], $deep, '+');
                case TYPE_CHANGED:
                    $group = [
                        formatToString($elem['key'], $elem['value'], $deep, '+'),
                        formatToString($elem['key'], $elem['oldValue'], $deep, '-'),
                    ];

                    return implode("\n", $group);
                case TYPE_COMPLEXITY:
                    return formatToString(
                        $elem['key'],
                        formatToPretty($elem['children'], $deep . INDENTATION),
                        $deep,
                        ' '
                    );
                default:
                    throw new \Exception("Not valid type: {$elem['type']}");
            }
        },
        $data
    );

    $joined = implode("\n", $view);

    return "{\n{$joined}\n{$deep}}";
}
