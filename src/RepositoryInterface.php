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
     * Pulls from the repository items those satisfied to passed criteria
     * @param CriteriaInterface $criteria
     * @return EntitiesInterface
     */
    public function pulled(CriteriaInterface $criteria): Iterator;

    /**
     * Passes a request into repository
     * @param RequestInterface $request
     * @return RepositoryInterface
     */
    public function requested(RequestInterface $request): RepositoryInterface;
}
