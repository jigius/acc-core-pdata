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

namespace Acc\Core\PersistentData\PDO\Vendor;

/**
 * Interface PDOStatementInterface
 * @package Acc\Core\PersistentData\PDO\Vendor
 */
interface PDOStatementInterface extends \Traversable
{
    /**
     * Returns the number of rows affected by the last SQL statement
     * @return int the number of rows.
     */
    public function rowCount(): int;
}
