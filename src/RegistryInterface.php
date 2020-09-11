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
use Acc\Core\PrinterInterface;

/**
 * Interface RegistryInterface
 * Defines common functionality all `RegistryInterface` immutable instances must implement
 * @package Acc\Core\PersistentData
 */
interface RegistryInterface extends MediaInterface, PrinterInterface
{
    /**
     * @inheritDoc
     * @param string $key
     * @param mixed $val
     * @return RegistryInterface
     */
    public function with(string $key, $val): PrinterInterface;

    /**
     * @inheritDoc
     * @return RegistryInterface
     */
    public function finished(): RegistryInterface;

    /**
     * Returns the value for a requested attribute processed with processors (if they are defined)
     * @param string $key the name of a requesting attribute
     * @param callable|null $success optional processor for known attribute
     * @param callable|null $failed optional processor for unknown attribute
     * @return mixed
     */
    public function value(string $key, callable $success = null, callable $failed = null);
}
