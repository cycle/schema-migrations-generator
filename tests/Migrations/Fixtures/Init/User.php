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
use Cycle\Annotated\Annotation\Table;
use Cycle\Annotated\Annotation\Table\Index;

/**
 * @Entity
 * @Table(
 *      indexes={
 *             @Index(columns = {"email"}, unique = true)
 *      }
 * )
 */
class User
{
    /**
     * @Column(type = "primary")
     *
     * @var int
     */
    protected $id;

    /**
     * @Column(type = "string")
     *
     * @var string
     */
    protected $email;
}
