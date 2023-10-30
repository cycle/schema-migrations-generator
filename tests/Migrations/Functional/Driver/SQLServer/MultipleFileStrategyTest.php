<?php

declare(strict_types=1);
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Cycle\Schema\Generator\Migrations\Tests\Functional\Driver\SQLServer;

use Cycle\Schema\Generator\Migrations\Tests\Functional\MultipleFileStrategyTest as CommonTestCase;

final class MultipleFileStrategyTest extends CommonTestCase
{
    public const DRIVER = 'sqlserver';
}
