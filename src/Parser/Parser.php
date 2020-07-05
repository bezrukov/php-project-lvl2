<?php

declare(strict_types=1);

namespace Differ\Parser;

use Symfony\Component\Yaml\Yaml;

const JSON_FORMAT = 'json';
const YAML_FORMAT = 'yaml';

/**
 * @param string $content
 * @return array
 */
function parseJson(string $content): array
{
    try {
        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
        return [];
    }
}

/**
 * @param string $content
 * @return array
 */
function parseYaml(string $content): array
{
    return (array) Yaml::parse($content, Yaml::PARSE_OBJECT_FOR_MAP);
}

function getParser(string $format)
{
    if ($format === JSON_FORMAT) {
        return static function ($content) {
            return parseJson($content);
        };
    }

    if ($format === YAML_FORMAT) {
        return static function ($content) {
            return parseYaml($content);
        };
    }

    return static function () {
        return [];
    };
}
