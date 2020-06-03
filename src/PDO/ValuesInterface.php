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
 * Interface ValuesInterface
 * @package Acc\Core\PersistentData\PDO
 */
interface ValuesInterface extends BindValueInterface
{
    /**
     * Appends a value into a set
     * @param ValueInterface $value
     * @return ValuesInterface
     */
    public function with(ValueInterface $value): ValuesInterface;

    /**
     * Defines a filter that is used for filtering out appending values
     * @param callable $callee
     * @return ValuesInterface
     */
    public function withFilteredOutItems(callable $callee): ValuesInterface;
}
