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

use Acc\Core\PrinterInterface;
use Iterator, OutOfBoundsException, LogicException;

/**
 * Class Entities
 * @package Acc\Core\PersistentData
 */
final class Entities implements EntitiesInterface
{
    /**
     * @var Iterator
     */
    private Iterator $orig;

    /**
     * @var PrinterInterface
     */
    private PrinterInterface $f;

    /**
     * Entities constructor.
     * @param Iterator $itr
     * @param PrinterInterface $entityFactory
     */
    public function __construct(Iterator $itr, PrinterInterface $entityFactory)
    {
        $this->orig = $itr;
        $this->f = $entityFactory;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->orig->key();
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->orig->next();
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->orig->rewind();
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->orig->valid();
    }

    /**
     * @inheritDoc
     */
    public function current(): EntityInterface
    {
        $f = $this->f;
        foreach ($this->orig->current() as $key => $val) {
            $f = $f->with($key, $val);
        }
        return $f->finished();
    }

    /**
     * @inheritDoc
     */
    public function rewinded(): EntitiesInterface
    {
        $this->rewind();
        return $this;
    }

    /**
     * @param callable|null $rejected
     * @return EntityInterface
     */
    public function pulled(callable $rejected = null): EntityInterface
    {
        if (!$this->valid()) {
            if ($rejected !== null) {
                $ret = call_user_func($rejected);
                if (!($ret instanceof EntityInterface)) {
                    throw new LogicException();
                }
            } else {
                throw new OutOfBoundsException("there is no valid entity");
            }
        } else {
            $ret = $this->current();
        }
        return $ret;
    }
}
