<?php

namespace Differ\Differ;

function getFileContent(string $pathToFile)
{
    return file_get_contents($pathToFile);
}

function parseFileContent($content, $format = 'json'): array
{
    try {
        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
        return [];
    }
}

function getViewData($firstData, $secondData): string
{
    $includedKeys = array_keys(array_intersect_key($firstData, $secondData));

    $removedKeys = array_filter(
        array_keys($firstData),
        function ($key) use ($includedKeys) {
            return !in_array($key, $includedKeys, true);
        }
    );

    $addedKys = array_filter(
        array_keys($secondData),
        function ($key) use ($includedKeys) {
            return !in_array($key, $includedKeys, true);
        }
    );

    return array_reduce(
        array_unique(array_merge(array_keys($firstData), array_keys($secondData))),
        static function ($acc, $key) use ($removedKeys, $addedKys, $firstData, $secondData) {
            if (in_array($key, $removedKeys, true)) {
                return sprintf($acc . " - %s: %s\n", $key, $firstData[$key]);
            }

            if (in_array($key, $addedKys, true)) {
                return sprintf($acc . " + %s: %s\n", $key, $secondData[$key]);
            }

            if ($firstData[$key] === $secondData[$key]) {
                return sprintf($acc . "   %s: %s\n", $key, $firstData[$key]);
            }

            $acc = sprintf($acc . " - %s: %s\n", $key, $firstData[$key]);
            $acc = sprintf($acc . " + %s: %s\n", $key, $secondData[$key]);

            return $acc;
        },
        ''
    );
}

function genDiff(string $pathToFile1, string $pathToFile2): string
{
    $data1 = parseFileContent(getFileContent($pathToFile1));
    $data2 = parseFileContent(getFileContent($pathToFile2));

    return getViewData($data1, $data2);
}
