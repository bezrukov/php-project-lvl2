<?php

namespace Differ\Differ;

use function Differ\Parser\getParser;
use function Funct\Collection\last;

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

function genDiff(string $pathToFile1, string $pathToFile2, string $format = FORMAT_PRETTY): string
{
    $data1 = parseFileContent(getFileContent($pathToFile1), parseFileFormat($pathToFile1));
    $data2 = parseFileContent(getFileContent($pathToFile2), parseFileFormat($pathToFile2));

    $diff = makeDiff($data1, $data2);
    $formatter = getFormatters($format);

    return $formatter($diff);
}
