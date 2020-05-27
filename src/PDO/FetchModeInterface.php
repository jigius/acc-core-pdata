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

use PDOStatement;

/**
 * Interface FetchModeInterface
 * @package Acc\Core\PersistentData\PDO
 */
interface FetchModeInterface
{
    /**
     * Defines fetch mode
     * @param PDOStatement $statement
     */
    public function initialize(PDOStatement $statement): void;
}
