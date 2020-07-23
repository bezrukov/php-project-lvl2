<?php

declare(strict_types=1);

function formatToPlain(array $data, $deep = ''): string
{
    $view = array_reduce(
        $data,
        static function ($acc, $elem) use ($deep) {
            if (!empty($elem['children'])) {
                $acc[] = formatToPlain($elem['children'], sprintf('%s%s.', $deep, $elem['key']));
                return $acc;
            }

            $value = is_array($elem['value'])
                ? 'complex value'
                : $elem['value'];

            if ($elem['status'] === STATUS_NOT_CHANGED) {
                $acc[] = sprintf('Property \'%s%s\' wasn\'t changed', $deep, $elem['key']);

                return $acc;
            }

            if ($elem['status'] === STATUS_REMOVED) {
                $acc[] = sprintf('Property \'%s%s\' was removed', $deep, $elem['key']);

                return $acc;
            }

            if ($elem['status'] === STATUS_ADDED) {
                $acc[] = sprintf('Property \'%s%s\' was added with value: \'%s\'', $deep, $elem['key'], $value);
                return $acc;
            }

            if ($elem['status'] === STATUS_CHANGED) {
                $acc[] = sprintf(
                    'Property \'%s%s\' was changed. From \'%s\' to \'%s\'',
                    $deep,
                    $elem['key'],
                    $elem['oldValue'],
                    $elem['value']
                );
                return $acc;
            }

            return $acc;
        },
        []
    );

    return implode("\n", $view);
}
