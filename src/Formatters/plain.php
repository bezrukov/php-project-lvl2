<?php

declare(strict_types=1);

namespace Differ\Formatters\plain;

use const Differ\Formatters\TYPE_ADDED;
use const Differ\Formatters\TYPE_CHANGED;
use const Differ\Formatters\TYPE_COMPLEXITY;
use const Differ\Formatters\TYPE_NOT_CHANGED;
use const Differ\Formatters\TYPE_REMOVED;

function formatValueToSting($value): string
{
    $valueType = gettype($value);

    switch ($valueType) {
        case 'array':
            return 'complex value';
        case 'boolean':
            return json_encode($value);
        default:
            return $value;
    }
}

function formatToPlain(array $data, $ancestry = ''): string
{
    $view = array_map(static function ($item) use ($ancestry) {
        $value = formatValueToSting($item['value'] ?? '');
        $oldValue = formatValueToSting($item['oldValue'] ?? '');
        $key = $item['key'];

        switch ($item['type']) {
            case TYPE_COMPLEXITY:
                return formatToPlain($item['children'], "{$ancestry}{$key}.");
            case TYPE_NOT_CHANGED:
                return "Property '{$ancestry}{$key}' wasn't changed";
            case TYPE_ADDED:
                return "Property '{$ancestry}{$key}' was added with value: '$value'";
            case TYPE_REMOVED:
                return "Property '{$ancestry}{$key}' was removed";
            case TYPE_CHANGED:
                return "Property '{$ancestry}{$key}' was changed. From '{$oldValue}' to '{$value}'";
            default:
                throw new \Exception("Not valid type: {$item['type']}");
        }
    }, $data);

    return implode("\n", $view);
}
