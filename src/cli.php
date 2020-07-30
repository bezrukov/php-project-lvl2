<?php

declare(strict_types=1);

namespace Differ;

use Docopt;

const DOC = <<<'DOCOPT'
gendiff -h

Generate diff

Usage:
  gendiff (-h|--help)
  gendiff (-v|--version)
  gendiff [--format <fmt>] <firstFile> <secondFile>

Options:
  -h --help          Show this screen
  -v --version       Show version
  -f --format <fmt>     Report format [default: pretty]
DOCOPT;

function run()
{
    $args = Docopt::handle(DOC);
    $diff = genDiff($args->args['<firstFile>'], $args->args['<secondFile>'], $args->args['--format']);

    print_r($diff . "\n");
}
