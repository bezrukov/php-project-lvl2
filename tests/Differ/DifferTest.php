<?php

declare(strict_types=1);

namespace Differ;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

use function Differ\Differ\getFormat;
use function Differ\Differ\makeDiff;
use function Differ\Differ\parseFileContent;

use const Differ\Differ\STATUS_ADDED;
use const Differ\Differ\STATUS_CHANGED;
use const Differ\Differ\STATUS_NOT_CHANGED;
use const Differ\Differ\STATUS_REMOVED;
use const Differ\Parser\JSON_FORMAT;

class DifferTest extends TestCase
{
    /**
     * @dataProvider getFilesContentProvider
     * @param $content
     * @param $expected
     */
    public function testParseFileContent($content, $expected): void
    {
        $this->assertEquals($expected, parseFileContent($content, JSON_FORMAT));
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
        $this->assertEquals($expected, getFormat($data));
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

    /**
     * @dataProvider getDifferProvider
     *
     * @param string $firstFilename
     * @param string $secondFilename
     * @param string $firstContent
     * @param string $secondContent
     * @param string $expected
     */
    public function testDiffer(
        string $firstFilename,
        string $secondFilename,
        string $firstContent,
        string $secondContent,
        string $expected
    ): void {
        $root = vfsStream::setup();
        $firstFile = vfsStream::newFile($firstFilename)->at($root);
        $secondFile = vfsStream::newFile($secondFilename)->at($root);

        $firstFile->setContent($firstContent);
        $secondFile->setContent($secondContent);

        $result = Differ\genDiff($root->url() . '/' . $firstFilename, $root->url() . '/' . $secondFilename);

        self::assertEquals($expected, $result);
    }

    public function getDifferProvider(): array
    {
        return [
            [
                'firstName' => 'before.json',
                'secondName' => 'after.json',
                'firstContent' => <<<'EOT'
{
  "host": "hexlet.io",
  "timeout": 50,
  "proxy": "123.234.53.22"
}
EOT,
                'secondContent' => <<<'EOT'
{
  "timeout": 20,
  "verbose": true,
  "host": "hexlet.io"
}
EOT,
                'expected' => <<<'EOT'
{
   host: hexlet.io
 - timeout: 50
 + timeout: 20
 - proxy: 123.234.53.22
 + verbose: 1
}
EOT,
            ],
            'deepDAta' => [
                'firstName' => 'before.json',
                'secondName' => 'after.json',
                'firstContent' => <<<'EOT'
{
  "common": {
    "setting1": "Value 1",
    "setting2": "200",
    "setting3": true,
    "setting6": {
      "key": "value"
    }
  },
  "group1": {
    "baz": "bas",
    "foo": "bar"
  },
  "group2": {
    "abc": "12345"
  }
}
EOT,
                'secondContent' => <<<'EOT'
{
  "common": {
    "setting1": "Value 1",
    "setting3": true,
    "setting4": "blah blah",
    "setting5": {
      "key5": "value5"
    }
  },

  "group1": {
    "foo": "bar",
    "baz": "bars"
  },

  "group3": {
    "fee": "100500"
  }
}
EOT,
                'expected' => <<<'EOT'
{
   common: {
     setting1: Value 1
   - setting2: 200
     setting3: 1
   - setting6: {
  	key: value
     }
   + setting4: blah blah
   + setting5: {
  	key5: value5
     }
  }
   group1: {
   - baz: bas
   + baz: bars
     foo: bar
  }
 - group2: {
	abc: 12345
   }
 + group3: {
	fee: 100500
   }
}
EOT,
            ],
            'test yml' => [
                'before.yaml',
                'after.yaml',
                <<<'EOT'
host: hexlet.io
timeout: 50
proxy: 123.234.53.22
EOT,
                <<<'EOT'
host: hexlet.io
timeout: 20
verbose: true
EOT,
                <<<'EOT'
{
   host: hexlet.io
 - timeout: 50
 + timeout: 20
 - proxy: 123.234.53.22
 + verbose: 1
}
EOT,
            ]
        ];
    }
}
