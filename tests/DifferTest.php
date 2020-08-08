<?php

declare(strict_types=1);

namespace Differ\Tests;

use PHPUnit\Framework\TestCase;

use function Differ\differ\genDiff;

use const Differ\Formatters\FORMAT_JSON;
use const Differ\Formatters\FORMAT_PLAIN;
use const Differ\Formatters\FORMAT_PRETTY;

class DifferTest extends TestCase
{
    /**
     * @dataProvider getDifferProvider
     *
     * @param string $firstFileName
     * @param string $secondFileName
     * @param string $expectedFileName
     * @param string $format
     */
    public function testDiffer(
        string $firstFileName,
        string $secondFileName,
        string $expectedFileName,
        string $format = FORMAT_PRETTY
    ): void {

        $fixturePath = __DIR__ . "/fixtures/";
        $expectedContent = file_get_contents("{$fixturePath}{$expectedFileName}");
        $result = genDiff("{$fixturePath}{$firstFileName}", "{$fixturePath}{$secondFileName}", $format);

        self::assertEquals($expectedContent, $result);
    }

    public function getDifferProvider(): array
    {
        return [
            'json file and pretty format' => [
                'before.json',
                'after.json',
                'expect_pretty',
            ],
            'json file and json format' => [
                'before.json',
                'after.json',
                'expect_json.json',
                FORMAT_JSON,
            ],
            'json file and plain format' => [
                'before.json',
                'after.json',
                'expect_plain',
                FORMAT_PLAIN,
            ],
            'yaml file and pretty format' => [
                'before.yaml',
                'after.yaml',
                'expect_pretty',
            ],
            'yaml file and plain format' => [
                'before.yaml',
                'after.yaml',
                'expect_plain',
                FORMAT_PLAIN,
            ]
        ];
    }
}
