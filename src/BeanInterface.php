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

use Acc\Core\Value;
use Acc\Core\MediaInterface;

/**
 * Interface BeanInterface
 * Defines public contract all `BeanInterface` immutable instances must implement
 * @package Core\PersistentData
 */
interface BeanInterface extends MediaInterface
{
    /**
     * Assigns a value to a property
     * @param string $name a property's name
     * @param mixed $val a value of the a property
     * @return BeanInterface
     */
    public function withProp(string $name, $val): BeanInterface;

    /**
     * Assigns a value to an attribute
     * @param string $name an attribute's name
     * @param mixed $val a value of the an attribute
     * @return BeanInterface
     */
    public function withAttr(string $name, $val): BeanInterface;

    /**
     * Returns attributes have assigned to the instance
     * @return Value\BeansInterface
     */
    public function attrs(): Value\BeansInterface;
}
