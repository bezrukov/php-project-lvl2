<?php

declare(strict_types=1);

namespace Differ\Formatters\plain;

use const Differ\Formatters\STATUS_ADDED;
use const Differ\Formatters\STATUS_CHANGED;
use const Differ\Formatters\STATUS_COMPLEXITY;
use const Differ\Formatters\STATUS_NOT_CHANGED;
use const Differ\Formatters\STATUS_REMOVED;

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

            switch ($elem['status']) {
                case STATUS_COMPLEXITY:
                    $acc[] = formatToPlain($elem['children'], "{$deep}{$elem['key']}.");

                    return $acc;
                case STATUS_NOT_CHANGED:
                    $acc[] = "Property '{$deep}{$elem['key']}' wasn't changed";

                    return $acc;
                case STATUS_ADDED:
                    $acc[] = "Property '{$deep}{$elem['key']}' was added with value: '$value'";

                    return $acc;
                case STATUS_REMOVED:
                    $acc[] = "Property '{$deep}{$elem['key']}' was removed";

                    return $acc;
                case STATUS_CHANGED:
                    $acc[] = "Property '{$deep}{$elem['key']}' was changed."
                        . " From '{$oldValue}' to '{$value}'";

                    return $acc;
                default:
                    throw new \Exception("Not valid type: {$elem['status']}");
            }
        },
        []
    );

    return implode("\n", $view);
}
