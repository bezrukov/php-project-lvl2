<?php

namespace Differ\Differ;

use function Differ\Parser\getParser;
use function Funct\Collection\last;

const STATUS_REMOVED = 'removed';
const STATUS_ADDED = 'added';
const STATUS_CHANGED = 'changed';
const STATUS_NOT_CHANGED = 'not_changed';

function getFileContent(string $pathToFile)
{
    return file_get_contents($pathToFile);
}

function parseFileFormat(string $path)
{
    $fileName = (string) last(explode('/', $path));
    return (string) last(explode('.', $fileName));
}

function parseFileContent(string $content, $format): array
{
    $parser = getParser($format);

    return $parser($content);
}

function makeDiff(array $firstData, array $secondData): array
{
    $keys = array_unique(array_merge(array_keys($firstData), array_keys($secondData)));

    return array_reduce(
        $keys,
        static function ($acc, $key) use ($firstData, $secondData) {
            $originData = $firstData[$key] ?? null;
            $mutatedData = $secondData[$key] ?? null;

            if (empty($originData) || empty($mutatedData)) {
                $acc[$key] = [
                    'status' => empty($originData) ? STATUS_ADDED : STATUS_REMOVED,
                    'value'  => empty($originData) ? $mutatedData : $originData,
                    'key'    => $key,
                ];
                return $acc;
            }

            if ($mutatedData === $originData) {
                $acc[$key] = [
                    'status' => STATUS_NOT_CHANGED,
                    'value'  => $originData,
                    'key'    => $key,
                ];

                return $acc;
            }

            if (is_array($originData) && is_array($mutatedData)) {
                $acc[$key] = [
                    'children' => makeDiff($originData, $mutatedData),
                    'key'      => $key,
                    'status'   => STATUS_NOT_CHANGED,
                ];

                return $acc;
            }

            $acc[$key] = [
                'status'   => STATUS_CHANGED,
                'value'    => $mutatedData,
                'oldValue' => $originData,
                'key'      => $key,
            ];

            return $acc;
        },
        []
    );
}

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

function getFormat(array $diff, $deep = ''): string
{
    $view = ['{'];

    $view = array_reduce(
        $diff,
        static function ($acc, $elem) use ($deep) {
            if (!empty($elem['children'])) {
                $acc[] = sprintf($deep . "   %s: %s", $elem['key'], getFormat($elem['children'], $deep . '  '));
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

function genDiff(string $pathToFile1, string $pathToFile2, string $format = ''): string
{
    $data1 = parseFileContent(getFileContent($pathToFile1), parseFileFormat($pathToFile1));
    $data2 = parseFileContent(getFileContent($pathToFile2), parseFileFormat($pathToFile2));

    $diff = makeDiff($data1, $data2);

    return getFormat($diff);
}
