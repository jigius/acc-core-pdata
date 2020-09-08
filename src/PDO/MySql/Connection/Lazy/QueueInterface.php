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

namespace Acc\Core\PersistentData\PDO\MySql\Connection\Lazy;

use Acc\Core\PersistentData\PDO\ExtendedPDOInterface;

interface QueueInterface
{
    public function withAttribute(callable $callee): QueueInterface;

    public function withRequest(RequestInterface $r): QueueInterface;

    public function process(ExtendedPDOInterface $pdo): void;
}
