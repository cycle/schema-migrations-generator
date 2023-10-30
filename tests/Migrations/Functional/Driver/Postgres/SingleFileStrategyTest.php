<?php

declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Cycle\Schema\Generator\Migrations\Tests\Functional\Driver\Postgres;

use Cycle\Schema\Generator\Migrations\Tests\Functional\SingleFileStrategyTest as CommonTestCase;

final class SingleFileStrategyTest extends CommonTestCase
{
    public const DRIVER = 'postgres';
}
