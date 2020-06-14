<?php

declare(strict_types=1);

namespace Differ;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\getViewData;
use function Differ\Differ\parseFileContent;

class DifferTest extends TestCase
{
    /**
     * @dataProvider getFilesContentProvider
     * @param $content
     * @param $expected
     */
    public function testParseFileContent($content, $expected): void
    {
        $this->assertEquals($expected, parseFileContent($content));
    }

    public function getFilesContentProvider(): array
    {
        return [
            [
                '',
                []
            ],
            [
                '{"data":"test"}',
                [
                    "data" => "test",
                ]
            ],
            [
                '{"host": "hexlet.io","timeout": 50,"proxy": "123.234.53.22"}',
                [
                    'host' => 'hexlet.io',
                    'timeout' => 50,
                    'proxy' => '123.234.53.22',
                ]
            ]
        ];
    }

    /**
     * @dataProvider getViewDataProvider
     *
     * @param $expected
     * @param $first
     * @param $second
     */
    public function testGetViewData($expected, $first, $second)
    {
        $this->assertEquals($expected, getViewData($first, $second));
    }

    public function getViewDataProvider(): array
    {
        return [
            [
                "   host: hexlet.io\n - timeout: 50\n + proxy: 123.234.53.22\n",
                [
                    'host' => 'hexlet.io',
                    'timeout' => 50,
                ],
                [
                    'host' => 'hexlet.io',
                    'proxy' => '123.234.53.22',
                ],
            ],
            [
                " - host: hexlet.io\n + host: hexlet.com\n - timeout: 50\n + proxy: 123.234.53.22\n",
                [
                    'host' => 'hexlet.io',
                    'timeout' => 50,
                ],
                [
                    'host' => 'hexlet.com',
                    'proxy' => '123.234.53.22',
                ],
            ]
        ];
    }
}
