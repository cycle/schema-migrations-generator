<?php

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations;

use Cycle\Migrations\Atomizer\Atomizer;

interface NameGeneratorInterface
{
    public function generate(Atomizer $atomizer): string;
}
