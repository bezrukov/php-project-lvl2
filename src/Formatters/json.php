<?php

declare(strict_types=1);

namespace Differ\Formatters\json;

function formatToJson(array $data): string
{
    return json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
}
