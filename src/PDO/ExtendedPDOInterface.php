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
use PDO;

/**
 * Interface ExtendedPDOInterface
 * Expands the contract of original PDO object
 * @package Acc\Core\PersistentData\PDO
 */
interface ExtendedPDOInterface extends PrinterInterface
{
    /**
     * Takes data is needed for creating a connection with a database
     * @param string $key
     * @param mixed $val
     * @return ExtendedPDOInterface
     */
    public function with(string $key, $val): ExtendedPDOInterface;

    /**
     * Creates a connection with a database
     * @return ExtendedPDOInterface
     */
    public function finished(): ExtendedPDOInterface;

    /**
     * Sets an attribute
     * Attention! The setting the attribute `ATTR_ERRMODE` is suppressed
     * @param int $attribute
     * @param $value
     * @return ExtendedPDOInterface
     */
    public function withAttribute(int $attribute, $value): ExtendedPDOInterface;

    /**
     * Wraps up passed anonymous function by transaction
     * @param callable $callee
     * @return mixed
     */
    public function trx(callable $callee);

    /**
     * @param PDOStatementInterface $stmt Defines a statement that will be used with future requests
     * @return ExtendedPDOInterface
     */
    public function withStatement(PDOStatementInterface $stmt): ExtendedPDOInterface;

    /**
     * Prepares a statement for execution and returns a statement object
     * @param string $query
     * @return PDOStatementInterface
     */
    public function prepared(string $query): PDOStatementInterface;

    /**
     *  Executes an SQL statement, returning a result set as an object supports PDOStatementInterface contract
     * @param string $query
     * @return PDOStatementInterface
     */
    public function queried(string $query): PDOStatementInterface;

    /**
     * Returns an vanilla PDO object
     * @return PDO;
     */
    public function vanilla(): PDO;
}
