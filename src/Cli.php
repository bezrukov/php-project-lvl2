<?php

namespace Differ\Cli;

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
  -f --format <fmt>     Report format [default: json]
DOCOPT;

function run()
{
    $args = Docopt::handle(DOC);
    $diff = \Differ\Differ\genDiff($args->args['<firstFile>'], $args->args['<secondFile>'], $args->args['--format']);

    print_r($diff);
}
