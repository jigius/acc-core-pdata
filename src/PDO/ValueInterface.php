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
 * Interface ValueInterface
 * @package Acc\Core\PersistentData\PDO
 */
interface ValueInterface extends BindValueInterface
{
    /**
     * Defines a type for instance
     * @param int $type
     * @return ValueInterface
     */
    public function withType(int $type): ValueInterface;

    /**
     * Defines a name for instance
     * @param int|string $name
     * @return ValueInterface
     */
    public function withName($name): ValueInterface;

    /**
     * Defines a value for instance
     * @param mixed $value
     * @return ValueInterface
     */
    public function withValue($value): ValueInterface;
}
