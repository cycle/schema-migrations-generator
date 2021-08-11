<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests\Fixtures\Alter;

/**
 * @entity
 */
class Post
{
    /**
     * @column(type=primary)
     * @var int
     */
    protected $id;

    /**
     * @refersTo(target=Other,nullable=true)
     * @var Other
     */
    protected $other;
}
