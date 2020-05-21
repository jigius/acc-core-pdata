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

use Iterator;

interface EntitiesInterface extends Iterator
{
    /**
     * Rewinds the position of the iterator
     * @return EntitiesInterface
     */
    public function rewinded(): EntitiesInterface;

    /**
     * Pulls a value from current position of the iterator.
     * If current position is invalid - usses returned value from passed(if it's passed) `rejected` function
     * @param callable|null $rejected
     * @return EntityInterface
     */
    public function pulled(callable $rejected = null): EntityInterface;

    /**
     * @inheritDoc
     * @return EntityInterface
     */
    public function current(): EntityInterface;
}
