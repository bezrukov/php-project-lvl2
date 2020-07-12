<?php

declare(strict_types=1);

namespace Differ;

use PHPUnit\Framework\TestCase;

use function Differ\Differ\getView;
use function Differ\Differ\makeDiff;
use function Differ\Differ\parseFileContent;

use const Differ\Differ\STATUS_ADDED;
use const Differ\Differ\STATUS_CHANGED;
use const Differ\Differ\STATUS_NOT_CHANGED;
use const Differ\Differ\STATUS_REMOVED;

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
     * @dataProvider getViewDataProvider
     *
     * @param $expected
     * @param $data
     */
    public function testGetViewData($expected, $data): void
    {
        $this->assertEquals($expected, getView($data));
    }

    public function getViewDataProvider(): array
    {
        return [
            [
                <<<'EOT'
{
   host: hexlet.io
 - timeout: 50
 + proxy: 123.234.53.22
}
EOT,
                [
                    'host'    => [
                        'key'    => 'host',
                        'value'  => 'hexlet.io',
                        'status' => STATUS_NOT_CHANGED,
                    ],
                    'timeout' => [
                        'key'    => 'timeout',
                        'value'  => 50,
                        'status' => STATUS_REMOVED,
                    ],
                    'proxy'   => [
                        'key'    => 'proxy',
                        'value'  => '123.234.53.22',
                        'status' => STATUS_ADDED,
                    ],
                ],
            ],
            [
                <<<'EOT'
{
 - host: hexlet.io
 + host: hexlet.com
 - timeout: 50
 + proxy: 123.234.53.22
}
EOT,
                [
                    'host'    => [
                        'key'      => 'host',
                        'value'    => 'hexlet.com',
                        'status'   => STATUS_CHANGED,
                        'oldValue' => 'hexlet.io',
                    ],
                    'timeout' => [
                        'key'    => 'timeout',
                        'value'  => 50,
                        'status' => STATUS_REMOVED,
                    ],
                    'proxy'   => [
                        'key'    => 'proxy',
                        'value'  => '123.234.53.22',
                        'status' => STATUS_ADDED,
                    ],
                ],
            ],
            [
                <<<'EOT'
{
 - host: hexlet.io
 + host: hexlet.com
 - timeout: 50
   common: {
   - setting1: Value 1
   + setting2: 300
   + setting6: {
  	key: value
     }
  }
}
EOT,
                [
                    'host'    => [
                        'key'      => 'host',
                        'value'    => 'hexlet.com',
                        'status'   => STATUS_CHANGED,
                        'oldValue' => 'hexlet.io',
                    ],
                    'timeout' => [
                        'key'    => 'timeout',
                        'value'  => 50,
                        'status' => STATUS_REMOVED,
                    ],
                    'common' => [
                        'key' => 'common',
                        'status' => STATUS_NOT_CHANGED,
                        'children' => [
                            'setting1' => [
                                'key' => 'setting1',
                                'value' => 'Value 1',
                                'status' => STATUS_REMOVED
                            ],
                            'setting2' => [
                                'key' => 'setting2',
                                'value' => 300,
                                'status' => STATUS_ADDED
                            ],
                            'setting6' => [
                                'key' => 'setting6',
                                'value' => [
                                    'key' => 'value',
                                ],
                                'status' => STATUS_ADDED,
                            ],
                        ],
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider getMakeASTProvider
     *
     * @param array $firstData
     * @param array $secondData
     * @param array $expected
     */
    public function testMakeDiff(array $firstData, array $secondData, array $expected): void
    {
        $this->assertEquals($expected, makeDiff($firstData, $secondData));
    }

    public function getMakeASTProvider(): array
    {
        return [
            'empty arguments'          => [
                [],
                [],
                [],
            ],
            'simple struct'            => [
                [
                    'host'    => 'hexlet.io',
                    'timeout' => 50,
                ],
                [
                    'host'    => 'hexlet.io',
                    'timeout' => 60,
                    'proxy'   => '123.234.53.22',
                ],
                [
                    'host'    => [
                        'key'    => 'host',
                        'status' => STATUS_NOT_CHANGED,
                        'value'  => 'hexlet.io',
                    ],
                    'timeout' => [
                        'key'      => 'timeout',
                        'status'   => STATUS_CHANGED,
                        'value'    => 60,
                        'oldValue' => 50,
                    ],
                    'proxy'   => [
                        'key'    => 'proxy',
                        'status' => STATUS_ADDED,
                        'value'  => '123.234.53.22',
                    ],
                ],
            ],
            'children without changes' => [
                [
                    'host'   => 'hexlet.io',
                    'group1' => [
                        'baz' => 'bas',
                        'foo' => 'bar',
                    ],
                ],
                [
                    'host' => 'hexlet.io',
                ],
                [
                    'host'   => [
                        'key'    => 'host',
                        'value'  => 'hexlet.io',
                        'status' => STATUS_NOT_CHANGED,
                    ],
                    'group1' => [
                        'key'    => 'group1',
                        'status' => STATUS_REMOVED,
                        'value'  => [
                            'baz' => 'bas',
                            'foo' => 'bar',
                        ],
                    ],
                ],
            ],
            'struct with deep include' => [
                [
                    'host'   => 'hexlet.io',
                    'group1' => [
                        'baz'      => 'bas',
                        'foo'      => 'bar',
                        'setting2' => 200,
                    ],
                ],
                [
                    'host'   => 'hexlet.io',
                    'group1' => [
                        'baz'      => 'bas1',
                        'foo'      => 'bar1',
                        'setting3' => true,
                    ],
                ],
                [
                    'host'   => [
                        'key'    => 'host',
                        'value'  => 'hexlet.io',
                        'status' => STATUS_NOT_CHANGED,
                    ],
                    'group1' => [
                        'key'      => 'group1',
                        'status'   => STATUS_NOT_CHANGED,
                        'children' => [
                            'setting2' => [
                                'key'    => 'setting2',
                                'status' => STATUS_REMOVED,
                                'value'  => 200,
                            ],
                            'baz'      => [
                                'key'      => 'baz',
                                'status'   => STATUS_CHANGED,
                                'value'    => 'bas1',
                                'oldValue' => 'bas',
                            ],
                            'foo'      => [
                                'key'      => 'foo',
                                'status'   => STATUS_CHANGED,
                                'value'    => 'bar1',
                                'oldValue' => 'bar',
                            ],
                            'setting3' => [
                                'key'    => 'setting3',
                                'status' => STATUS_ADDED,
                                'value'  => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
