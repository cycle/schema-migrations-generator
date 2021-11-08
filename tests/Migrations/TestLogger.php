<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Cycle\Schema\Generator\Migrations\Tests;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

class TestLogger implements LoggerInterface
{
    use LoggerTrait;

    private bool $display = false;

    private int $countWrites = 0;
    private int $countReads = 0;

    public function countWriteQueries(): int
    {
        return $this->countWrites;
    }

    public function countReadQueries(): int
    {
        return $this->countReads;
    }

    public function log($level, $message, array $context = []): void
    {
        if (!empty($context['query'])) {
            $sql = strtolower($context['query']);
            if (
                str_starts_with($sql, 'insert')
                || str_starts_with($sql, 'update')
                || str_starts_with($sql, 'delete')
            ) {
                $this->countWrites++;
            } else {
                if (!$this->isPostgresSystemQuery($sql)) {
                    $this->countReads++;
                }
            }
        }

        if (!$this->display) {
            return;
        }

        if ($level == LogLevel::ERROR) {
            echo " \n! \033[31m" . $message . "\033[0m";
        } elseif ($level == LogLevel::ALERT) {
            echo " \n! \033[35m" . $message . "\033[0m";
        } elseif (str_starts_with($message, 'SHOW')) {
            echo " \n> \033[34m" . $message . "\033[0m";
        } else {
            if ($this->isPostgresSystemQuery($message)) {
                echo " \n> \033[90m" . $message . "\033[0m";

                return;
            }

            if (str_starts_with($message, 'SELECT')) {
                echo " \n> \033[32m" . $message . "\033[0m";
            } elseif (str_starts_with($message, 'INSERT')) {
                echo " \n> \033[36m" . $message . "\033[0m";
            } else {
                echo " \n> \033[33m" . $message . "\033[0m";
            }
        }
    }

    public function display(): void
    {
        $this->display = true;
    }

    public function hide(): void
    {
        $this->display = false;
    }

    protected function isPostgresSystemQuery(string $query): bool
    {
        $query = strtolower($query);
        return strpos($query, 'tc.constraint_name')
        || strpos($query, 'pg_indexes')
        || strpos($query, 'tc.constraint_name')
        || strpos($query, 'pg_constraint')
        || strpos($query, 'information_schema')
        || strpos($query, 'pg_class');
    }
}
