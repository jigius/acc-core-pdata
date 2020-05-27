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
use Acc\Core\PersistentData\PDO\ExtendedPDOInterface;

/**
 * Interface RequestInterface
 * Defines common functionality all `RequestInterface` immutable instances must implement
 * @package Core\PersistentData
 */
interface RequestInterface extends MediaInterface
{
    /**
     * Executes a prepared request
     * @param ExtendedPDOInterface $pdo
     * @return RequestInterface
     */
    public function executed(ExtendedPDOInterface $pdo): RequestInterface;
}
