<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Functional\Fixtures\Alter;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity(database: 'secondary')]
class Other
{
    #[Column(type: 'primary')]
    protected int $id;

    #[Column(type: 'enum(active,disabled,pending)', default: 'active')]
    protected string $status;
}
