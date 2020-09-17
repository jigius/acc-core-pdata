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

use Acc\Core\PersistentData\RegistryInterface;
use Acc\Core\PersistentData\VanillaRegistry;
use Acc\Core\PrinterInterface;
use Acc\Core\PersistentData\RequestInterface;

/**
 * Class Request
 * The example of simple object that implemented contract `RequestInterface`
 * @package Acc\Core\PersistentData\PDO
 */
final class Request implements RequestInterface
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
    private ?PDOStatementInterface $statement = null;

    /**
     * The attributes of the entity
     * @var RegistryInterface|null
     */
    private RegistryInterface $attrs;

    /**
     * Request constructor.
     * @param string $query
     * @param array $value
     * @param RegistryInterface|null $attrs
     */
    public function __construct(string $query, array $value = [], ?RegistryInterface $attrs = null)
    {
        $this->q = $query;
        $this->v = $value;
        $this->attrs = $attrs ?? new VanillaRegistry();
    }

    /**
     * @inheritDoc
     */
    public function printed(PrinterInterface $printer)
    {
       return
           $printer
                ->with('statement', $this->statement)
                ->finished();
    }

    /**
     * @inheritDoc
     */
    public function executed(ExtendedPDOInterface $pdo): RequestInterface
    {
        if (empty($this->q)) {
            return $this;
        }
        $obj =$this->blueprinted();
        $bp = new Value();
        $stmt = $pdo->prepared($this->q);
        foreach ($this->v as $name => $val) {
            $stmt =
                $stmt
                    ->withValue(
                        $bp
                            ->withName($name)
                            ->withValue($val)
                    );
        }
        $obj->statement = $stmt->executed();
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function attrs(): RegistryInterface
    {
        return $this->attrs;
    }

    /**
     * @param string $name
     * @param $val
     * @return RequestInterface
     */
    public function withAttr(string $name, $val): RequestInterface
    {
        $obj = $this->blueprinted();
        $obj->attrs = $this->attrs->with($name, $val);
        return $obj;
    }

    private function blueprinted(): self
    {
        $obj = new self($this->q, $this->v, $this->attrs);
        $obj->statement = $this->statement;
        return $obj;
    }
}
