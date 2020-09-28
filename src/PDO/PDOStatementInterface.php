<?php
/**
 * This file is part of the jigius/acc-core-pdata library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2020 Jigius <jigius@gmail.com>
 * @link https://github.com/jigius/acc-core-pdata GitHub
 */

declare(strict_types=1);

namespace Acc\Core\PersistentData\PDO;

use Acc\Core\PrinterInterface;
use PDOStatement;

/**
 * Interface PDOStatementInterface
 * @package Acc\Core\PersistentData\PDO
 */
interface PDOStatementInterface
{
    /**
     * Defines fetch mode for original PDOStatement object
     * @param FetchModeInterface $mode
     * @return PDOStatementInterface
     */
    public function withFetchMode(FetchModeInterface $mode): PDOStatementInterface;

    /**
     * Defines attributes for original PDOStatement object
     * @param array $attrs
     * @return PDOStatementInterface
     */
    public function withAttributes(array $attrs): PDOStatementInterface;

    /**
     * Prepares a statement for executing
     * @param ExtendedPDOInterface $pdo
     * @param string $query
     * @return PDOStatementInterface
     */
    public function prepared(ExtendedPDOInterface $pdo, string $query): PDOStatementInterface;

    /**
     * Executes a request
     * @param PrinterInterface $p An object for printing the result of a request to it
     * @return PrinterInterface
     */
    public function executed(PrinterInterface $p): PrinterInterface;

    /**
     * Appends a value to current set
     * @param ValueInterface $value
     * @return PDOStatementInterface
     */
    public function withValue(ValueInterface $value): PDOStatementInterface;

    /**
     * Defines the set of values
     * @param ValuesInterface $values
     * @return PDOStatementInterface
     */
    public function withValues(ValuesInterface $values): PDOStatementInterface;

    /**
     * Returns an vanilla PDOStatement object
     * @return PDOStatement
     */
    public function vanilla(): PDOStatement;
}
