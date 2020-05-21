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

namespace Acc\Core\PersistentData;

use Acc\Core\MediaInterface;
use Acc\Core\PersistentData\PDO\PDOInterface;
use Iterator;

/**
 * Interface CriteriaInterface
 * Defines common functionality all `CriteriaInterface` immutable instances must implement
 * @package Acc\Core\PersistentData
 */
interface CriteriaInterface extends MediaInterface
{
    /**
     * Fetches an items
     * @param PDOInterface $pdo
     * @return Iterator
     */
    public function items(PDOInterface $pdo): Iterator;
}
