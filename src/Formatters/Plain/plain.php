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

function formatToPlain(array $data, $deep = ''): string
{
    $view = array_reduce(
        $data,
        static function ($acc, $elem) use ($deep) {
            $value = isset($elem['value']) ? formatValueToSting($elem['value']) : null;
            $oldValue = isset($elem['oldValue']) ? formatValueToSting($elem['oldValue']) : null;

            switch ($elem['type']) {
                case TYPE_COMPLEXITY:
                    $acc[] = formatToPlain($elem['children'], "{$deep}{$elem['key']}.");

                    return $acc;
                case TYPE_NOT_CHANGED:
                    $acc[] = "Property '{$deep}{$elem['key']}' wasn't changed";

                    return $acc;
                case TYPE_ADDED:
                    $acc[] = "Property '{$deep}{$elem['key']}' was added with value: '$value'";

                    return $acc;
                case TYPE_REMOVED:
                    $acc[] = "Property '{$deep}{$elem['key']}' was removed";

                    return $acc;
                case TYPE_CHANGED:
                    $acc[] = "Property '{$deep}{$elem['key']}' was changed."
                        . " From '{$oldValue}' to '{$value}'";

                    return $acc;
                default:
                    throw new \Exception("Not valid type: {$elem['type']}");
            }
        },
        []
    );

    return implode("\n", $view);
}
