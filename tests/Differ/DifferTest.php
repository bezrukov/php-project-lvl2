<?php

declare(strict_types=1);

namespace Differ;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class DifferTest extends TestCase
{
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
