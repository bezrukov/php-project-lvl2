<?php

namespace GenDiff\Cli;

use Docopt;

const PLAIN_FORMAT = 'plain';
const JSON_FORMAT = 'json';

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
  --format <fmt>     Report format [default: plain]
DOCOPT;

function run()
{
    Docopt::handle(DOC);
}
