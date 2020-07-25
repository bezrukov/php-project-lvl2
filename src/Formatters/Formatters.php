<?php

declare(strict_types=1);

const FORMAT_JSON = 'json';
const FORMAT_PLAIN = 'plain';
const FORMAT_PRETTY = 'pretty';

const STATUS_REMOVED = 'removed';
const STATUS_ADDED = 'added';
const STATUS_CHANGED = 'changed';
const STATUS_NOT_CHANGED = 'not_changed';

function getFormatters($format)
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
