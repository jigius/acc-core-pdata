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

namespace Acc\Core\PersistentData\PDO\Request;

use Acc\Core\PrinterInterface;
use Acc\Core\PersistentData\RequestInterface;
use Acc\Core\PersistentData\PDO\{PDOInterface, Vendor\PDOStatementInterface};

/**
 * Class VanillaRequest
 * The example of simple object that implemented contract `RequestInterface`
 * @package Acc\Core\PersistentData\PDO\Request
 */
final class VanillaRequest implements RequestInterface
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
     * Executed statement
     * @var PDOStatementInterface|null
     */
    private PDOStatementInterface $stmt;

    /**
     * VanillaRequest constructor.
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
                ->with('statement', $this->stmt)
                ->finished();
    }

    /**
     * @inheritDoc
     */
    public function executed(PDOInterface $pdo): RequestInterface
    {
        if (empty($this->q)) {
            return $this;
        }
        $obj = new self($this->q, $this->v);
        $obj->stmt = $pdo->query($this->q, $this->v);
        return $obj;
    }
}
