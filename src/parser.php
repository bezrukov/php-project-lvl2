<?php

declare(strict_types=1);

namespace Differ;

use Symfony\Component\Yaml\Yaml;

const FILE_FORMAT_JSON = 'json';
const FILE_FORMAT_YAML = 'yaml';

/**
 * @param string $content
 * @return array
 * @throws \JsonException
 */
function parseJson(string $content): array
{
    return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
}

/**
 * @param string $content
 * @return array
 */
function parseYaml(string $content): array
{
    return Yaml::parse($content, Yaml::PARSE_OBJECT);
}

/**
 * @param string $format
 * @return \Closure
 * @throws \Exception
 */
function getParser(string $format): \Closure
{
    if ($format === FILE_FORMAT_JSON) {
        return static function ($content) {
            return parseJson($content);
        };
    }

    if ($format === FILE_FORMAT_YAML) {
        return static function ($content) {
            return parseYaml($content);
        };
    }

    throw new \Exception("Format: {$format} is not support");
}
