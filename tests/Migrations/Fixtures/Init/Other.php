<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Fixtures\Init;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;

#[Entity(database: 'secondary')]
class Other
{
    #[Column(type: 'primary')]
    protected int $id;

    #[Column(type: 'enum(active,disabled)', default: 'active')]
    protected string $status;
}
