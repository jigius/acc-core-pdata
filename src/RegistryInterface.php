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
     * Return the value for a requested attribute or returns default value
     * @param string $key the name of a requesting attribute
     * @param mixed|null $default the default value for a requested attribute if it's unknown
     * @return mixed
     */
    public function value(string $key, $default = null);
}
