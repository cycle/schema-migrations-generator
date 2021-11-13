<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Fixtures\Init;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;
use Cycle\Annotated\Annotation\Relation\BelongsTo;

/**
 * @Entity
 */
class Post
{
    /**
     * @Column(type = "primary")
     *
     * @var int
     */
    protected $id;

    /**
     * @BelongsTo(target = "User")
     *
     * @var User
     */
    protected $user;
}
