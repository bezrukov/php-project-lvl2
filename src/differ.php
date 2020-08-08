<?php

namespace Differ\differ;

use function Differ\Formatters\getFormatter;
use function Differ\parser\parseContent;

use const Differ\Formatters\FORMAT_PRETTY;
use const Differ\Formatters\STATUS_ADDED;
use const Differ\Formatters\STATUS_CHANGED;
use const Differ\Formatters\STATUS_COMPLEXITY;
use const Differ\Formatters\STATUS_NOT_CHANGED;
use const Differ\Formatters\STATUS_REMOVED;

/**
 * @param string $pathToFile
 * @return string
 */
function getFileContent(string $pathToFile): string
{
    return file_get_contents($pathToFile);
}

/**
 * @param string $path
 * @return string
 * @throws \Exception
 */
function parseFileFormat(string $path): string
{
    $pathExt = strtolower(pathinfo(realpath($path), PATHINFO_EXTENSION));

    if (empty($pathExt)) {
        throw new \Exception("Empty extension from file");
    }

    return $pathExt;
}

function makeDiff(array $firstData, array $secondData): array
{
    $keys = array_unique(array_merge(array_keys($firstData), array_keys($secondData)));

    return array_reduce(
        $keys,
        static function ($acc, $key) use ($firstData, $secondData) {
            $oldValue = $firstData[$key] ?? null;
            $newValue = $secondData[$key] ?? null;

            if ($newValue === $oldValue) {
                $acc[] = [
                    'status' => STATUS_NOT_CHANGED,
                    'value'  => $oldValue,
                    'key'    => $key,
                ];

                return $acc;
            }

            if (empty($oldValue)) {
                $acc[] = [
                    'status' => STATUS_ADDED,
                    'value'  => $newValue,
                    'key'    => $key,
                ];
                return $acc;
            }

            if (empty($newValue)) {
                $acc[] = [
                    'status' => STATUS_REMOVED,
                    'value'  => $oldValue,
                    'key'    => $key,
                ];
                return $acc;
            }

            if (is_array($oldValue) && is_array($newValue)) {
                $acc[] = [
                    'children' => makeDiff($oldValue, $newValue),
                    'key'      => $key,
                    'status'   => STATUS_COMPLEXITY,
                ];

                return $acc;
            }

            $acc[] = [
                'status'   => STATUS_CHANGED,
                'value'    => $newValue,
                'oldValue' => $oldValue,
                'key'      => $key,
            ];

            return $acc;
        },
        []
    );
}

function genDiff(string $firstFilePath, string $secondFilePath, string $format = FORMAT_PRETTY): string
{
    $data1 = parseContent(getFileContent($firstFilePath), parseFileFormat($firstFilePath));
    $data2 = parseContent(getFileContent($secondFilePath), parseFileFormat($secondFilePath));

    $diff = makeDiff($data1, $data2);
    $getFormat = getFormatter($format);

    return $getFormat($diff);
}
