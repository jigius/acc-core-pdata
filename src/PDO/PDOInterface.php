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

/**
 * Interface PDOInterface
 * @package Acc\Core\PersistentData\PDO
 */
interface PDOInterface
{
    /**
     * @param string $query
     * @param array $values
     * @return Vendor\PDOStatementInterface
     */
    public function query(string $query, array $values = []): Vendor\PDOStatementInterface;

    /**
     * @param callable $callee
     * @param array $args
     * @return mixed
     */
    public function trx(callable $callee, array $args = []);

    /**
     * @param string|null $name
     * @return string
     */
    public function lastInsertId(string $name = null): string;
}
