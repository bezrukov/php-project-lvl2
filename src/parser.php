<?php

declare(strict_types=1);

namespace Differ\parser;

use Symfony\Component\Yaml\Yaml;

const FILE_FORMAT_JSON = 'json';
const FILE_FORMAT_YAML = 'yaml';

/**
 * @param string $format
 * @param string $content
 * @return array
 * @throws \Exception
 */
function parseContent(string $content, string $format): array
{
    if ($format === FILE_FORMAT_JSON) {
        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    if ($format === FILE_FORMAT_YAML) {
        return Yaml::parse($content, Yaml::PARSE_OBJECT);
    }

    throw new \Exception("Format: {$format} is not support");
}
