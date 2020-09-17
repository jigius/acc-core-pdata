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

use Iterator, Traversable;
use LogicException;

/**
 * Class Beans
 * Used for iteration of beans
 * @package Acc\Core\PersistentData
 */
final class Beans implements BeansInterface
{
    /**
     * @var Iterator
     */
    private Iterator $original;

    /**
     * @var BeanInterface
     */
    private BeanInterface $blueprint;

    /**
     * Entities constructor.
     * @param Iterator $itr a decorated iterator
     * @param BeanInterface $blueprint a bean is used as a blueprint for returned values of current() method
     */
    public function __construct(Iterator $itr, BeanInterface $blueprint)
    {
        $this->original = $itr;
        $this->blueprint = $blueprint;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->original->key();
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->original->next();
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->original->rewind();
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->original->valid();
    }

    /**
     * @inheritDoc
     * @return BeanInterface
     */
    public function current(): BeanInterface
    {
        $i = $this->original->current();
        if ($i === null || !($i instanceof Traversable)) {
            throw new LogicException("invalid type");
        }
        $bean = $this->blueprint;
        array_walk(
            $i,
            function ($val, $prop) use (&$bean) {
                $bean = $bean->withProp($prop, $val);
            }
        );
        return $bean;
    }
}
