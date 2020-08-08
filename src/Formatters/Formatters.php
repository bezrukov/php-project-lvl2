<?php

declare(strict_types=1);

namespace Differ\Formatters;

use function Differ\Formatters\json\formatToJson;
use function Differ\Formatters\plain\formatToPlain;
use function Differ\Formatters\pretty\formatToPretty;

const FORMAT_JSON = 'json';
const FORMAT_PLAIN = 'plain';
const FORMAT_PRETTY = 'pretty';

const TYPE_REMOVED = 'removed';
const TYPE_ADDED = 'added';
const TYPE_CHANGED = 'changed';
const TYPE_NOT_CHANGED = 'not_changed';
const TYPE_COMPLEXITY = 'complexity';

function getFormatter($format)
{
    if ($format === FORMAT_JSON) {
        return static function ($content) {
            return formatToJson($content);
        };
    }

    if ($format === FORMAT_PLAIN) {
        return static function ($content) {
            return formatToPlain($content);
        };
    }

    return static function ($content) {
        return formatToPretty($content);
    };
}
