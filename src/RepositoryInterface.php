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
use Iterator;

/**
 * Interface RepositoryInterface
 * Defines common functionality all `RepositoryInterface` immutable instances must implement
 * @package Core\PersistentData
 */
interface RepositoryInterface extends MediaInterface
{
    /**
     * Pulls items those satisfied an executed request
     * @return BeansInterface
     */
    public function pulled(): Iterator;

    /**
     * Executes a passed request
     * @param RequestInterface $request
     * @return RepositoryInterface
     */
    public function executed(RequestInterface $request): RepositoryInterface;
}
