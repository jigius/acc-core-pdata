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

use Acc\Core\PersistentData\{
    RepositoryInterface,
    RequestInterface,
    CriteriaInterface
};
use Acc\Core\PrinterInterface;
use Iterator;

final class VanillaRepository implements RepositoryInterface
{
    /**
     * @var PDOInterface
     */
    private PDOInterface $pdo;

    /**
     * @var RequestInterface|null
     */
    private RequestInterface $r;

    /**
     * Repository constructor.
     * @param PDOInterface $pdo
     */
    public function __construct(PDOInterface $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @inheritDoc
     */
    public function pulled(CriteriaInterface $criteria): Iterator
    {
        return $criteria->items($this->pdo);
    }

    /**
     * @inheritDoc
     */
    public function requested(RequestInterface $request): RepositoryInterface
    {
        $obj = new self($this->pdo);
        $obj->r = $request->executed($this->pdo);
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function printed(PrinterInterface $printer)
    {
        return
            $printer
                ->with('request', $this->r)
                ->finished();
    }
}
