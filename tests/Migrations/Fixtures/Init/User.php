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
use Cycle\Annotated\Annotation\Table\Index;

#[Entity]
#[Index(columns: ['email'], unique: true)]
class User
{
    #[Column(type: 'primary')]
    protected int $id;

    #[Column(type: 'string')]
    protected $email;
}
