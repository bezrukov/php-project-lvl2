<?php

declare(strict_types=1);

function getArrayView(array $data, $deep = ' '): string
{
    $view = array_reduce(
        array_keys($data),
        static function ($acc, $key) use ($data, $deep) {
            $acc[] = $deep . sprintf("\t%s: %s", $key, $data[$key]);

            return $acc;
        },
        ['{']
    );
    $view[] = $deep . '   }';

    return implode("\n", $view);
}

function formatToPretty(array $data, $deep = ''): string
{
    $view = ['{'];

    $view = array_reduce(
        $data,
        static function ($acc, $elem) use ($deep) {
            if (!empty($elem['children'])) {
                $acc[] = sprintf($deep . "   %s: %s", $elem['key'], formatToPretty($elem['children'], $deep . '  '));
                return $acc;
            }

            $value = is_array($elem['value'])
                ? getArrayView($elem['value'], $deep)
                : $elem['value'];

            if ($elem['status'] === STATUS_NOT_CHANGED) {
                $acc[] = sprintf($deep . "   %s: %s", $elem['key'], $value);

                return $acc;
            }

            if ($elem['status'] === STATUS_REMOVED) {
                $acc[] = sprintf($deep . " - %s: %s", $elem['key'], $value);

                return $acc;
            }

            if ($elem['status'] === STATUS_ADDED) {
                $acc[] = sprintf($deep . " + %s: %s", $elem['key'], $value);

                return $acc;
            }

            if ($elem['status'] === STATUS_CHANGED) {
                $acc[] = sprintf($deep . " - %s: %s", $elem['key'], $elem['oldValue']);
                $acc[] = sprintf($deep . " + %s: %s", $elem['key'], $elem['value']);

                return $acc;
            }

            return $acc;
        },
        $view
    );

    $view[] = $deep . '}';

    return implode("\n", $view);
}
