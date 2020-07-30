<?php

declare(strict_types=1);

namespace Differ\parser;

use Symfony\Component\Yaml\Yaml;

const FILE_FORMAT_JSON = 'json';
const FILE_FORMAT_YAML = 'yaml';

/**
 * @param string $format
 * @return \Closure
 * @throws \Exception
 */
function getParser(string $format): \Closure
{
    if ($format === FILE_FORMAT_JSON) {
        return static function ($content) {
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        };
    }

    if ($format === FILE_FORMAT_YAML) {
        return static function ($content) {
            return Yaml::parse($content, Yaml::PARSE_OBJECT);
        };
    }

    throw new \Exception("Format: {$format} is not support");
}
