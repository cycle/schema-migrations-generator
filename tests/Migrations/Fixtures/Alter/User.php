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
class User
{
    /**
     * @column(type=primary)
     *
     * @var int
     */
    protected $id;
}
