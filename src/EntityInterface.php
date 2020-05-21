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

/**
 * Interface EntityInterface
 * Defines common functionality all `EntityInterface` immutable instances must implement
 * @package Core\PersistentData
 */
interface EntityInterface extends MediaInterface
{
    /**
     * Returns the identity of the instance
     * @return string
     */
    public function identity(): string;

    /**
     * Assigns an option to the entity
     * @param string $key The key name of an option
     * @param mixed $val The value of the an option
     * @return EntityInterface
     */
    public function withOption(string $key, $val): EntityInterface;

    /**
     * Returns current entity's options
     * @return EntityOptionsInterface
     */
    public function options(): EntityOptionsInterface;
}
