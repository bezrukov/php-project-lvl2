<?php

declare(strict_types=1);

namespace Differ;

use PHPUnit\Framework\TestCase;

class DifferTest extends TestCase
{
    /**
     * @dataProvider getDifferProvider
     *
     * @param string $firstFile
     * @param string $secondFile
     * @param string $expected
     * @param string $format
     */
    public function testDiffer(
        string $firstFile,
        string $secondFile,
        string $expected,
        string $format = FORMAT_PRETTY
    ): void {

        $fixturePath = __DIR__ . "/fixtures/";
        $expectedContent = file_get_contents("{$fixturePath}{$expected}");
        $result = genDiff("{$fixturePath}{$firstFile}", "{$fixturePath}{$secondFile}", $format);

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
