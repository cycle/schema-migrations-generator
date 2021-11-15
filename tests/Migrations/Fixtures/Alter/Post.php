<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Fixtures\Alter;

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
     * @BelongsTo(target = "Other")
     *
     * @var Other
     */
    protected $other;
}
