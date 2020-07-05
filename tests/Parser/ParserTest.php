<?php

declare(strict_types=1);

namespace Parser;

use PHPUnit\Framework\TestCase;

use function Differ\Parser\parseJson;
use function Differ\Parser\parseYaml;

class ParserTest extends TestCase
{
    /**
     * @dataProvider jsonDataProvider
     *
     * @param $content
     * @param $expected
     */
    public function testParseJson($content, $expected): void
    {
        $result = parseJson($content);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array[]
     */
    public function jsonDataProvider(): array
    {
        return [
            [
                '',
                [],
            ],
            [
                '{"data":"test"}',
                [
                    "data" => "test",
                ],
            ],
            [
                '{"host": "hexlet.io","timeout": 50,"proxy": "123.234.53.22"}',
                [
                    'host'    => 'hexlet.io',
                    'timeout' => 50,
                    'proxy'   => '123.234.53.22',
                ],
            ],
        ];
    }

    /**
     * @dataProvider yamlDataProvider
     *
     * @param $content
     * @param $expected
     */
    public function testParseYaml($content, $expected): void
    {
        $result = parseYaml($content);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array[]
     */
    public function yamlDataProvider(): array
    {
        return [
            [
                '',
                [],
            ],
            [
                'test: data',
                [
                    'test' => 'data',
                ],
            ],
            [
                "host: hexlet.io\ntimeout: 50\nproxy: 123.234.53.22",
                [
                    'host'    => 'hexlet.io',
                    'timeout' => 50,
                    'proxy'   => '123.234.53.22',
                ]
            ],
        ];
    }
}