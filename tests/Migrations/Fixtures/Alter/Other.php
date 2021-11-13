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

/**
 * @Entity(database = "secondary")
 */
class Other
{
    /**
     * @Column(type = "primary")
     *
     * @var int
     */
    protected $id;

    /**
     * @Column(type = "enum(active,disabled,pending)")
     *
     * @var string
     */
    protected $status;
}
