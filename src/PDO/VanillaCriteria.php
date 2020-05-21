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

use Acc\Core\PrinterInterface;
use Acc\Core\PersistentData\CriteriaInterface;
use Iterator, IteratorIterator;

/**
 * Class VanillaCriteria
 * The example of simple object that implemented contract `CriteriaInterface`
 * @package Acc\Core\PersistentData\PDO\Criteria
 */
final class VanillaCriteria implements CriteriaInterface
{
    /**
     * @var string The query string
     */
    private string $q;

    /**
     * @var array The values for the query string
     */
    private array $v;

    /**
     * VanillaCriteria constructor.
     * @param string $query
     * @param array $value
     */
    public function __construct(string $query, array $value = [])
    {
        $this->q = $query;
        $this->v = $value;
    }

    /**
     * @inheritDoc
     */
    public function printed(PrinterInterface $printer)
    {
        return
            $printer
                ->with('query', $this->q)
                ->with('values', $this->v)
                ->finished();
    }

    /**
     * @inheritDoc
     */
    public function items(PDOInterface $pdo): Iterator
    {
        return
            new IteratorIterator(
                $pdo
                    ->query(
                        $this->q,
                        $this->v
                    )
            );
    }
}
