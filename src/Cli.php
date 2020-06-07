<?php

namespace GenDiff\Cli;

use Docopt;

function run()
{
    $doc = <<<'DOCOPT'
gendiff -h

Generate diff

Usage:
  gendiff (-h|--help)
  gendiff (-v|--version)

Options:
  -h --help                     Show this screen
  -v --version                  Show version
DOCOPT;

    Docopt::handle($doc);
}
