<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Fixtures\Alter;

use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Column;

#[Entity]
class User
{
    #[Column(type: 'primary')]
    protected int $id;
}
