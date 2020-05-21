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
 * Interface EntityOptionsInterface
 * Defines common functionality all `EntityOptionsInterface` immutable instances must implement
 * @package Acc\Core\PersistentData
 */
interface EntityOptionsInterface extends MediaInterface, PrinterInterface
{
    /**
     * @inheritDoc
     * @param string $key
     * @param mixed $val
     * @return EntityOptionsInterface
     */
    public function with(string $key, $val): EntityOptionsInterface;

    /**
     * Return the value for requested option or returns default value
     * @param string $key the name of requesting option
     * @param mixed|null $default the default value for requested option if it's unknown
     * @return mixed
     */
    public function option(string $key, $default = null);
}
