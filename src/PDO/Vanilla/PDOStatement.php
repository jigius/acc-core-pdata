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

namespace Acc\Core\PersistentData\PDO\Vanilla;

use Acc\Core\PrinterInterface;
use Acc\Core\ResultInterface;
use Acc\Core\PersistentData\PDO\{ExtendedPDOInterface,
    FetchMode\Vanilla,
    FetchModeInterface,
    PDOStatementInterface,
    ValueInterface,
    Values,
    ValuesInterface};
use LogicException, PDO, PDOStatement as StockPDOStatement;

/**
 * Class PDOStatement
 * @package Acc\Core\PersistentData\PDO
 */
final class PDOStatement implements PDOStatementInterface
{
    /**
     * Inputs data
     * @var array
     */
    private array $i;

    /**
     * PDOStatement constructor.
     */
    public function __construct()
    {
        $this->i = [
            'fetchMode' => new Vanilla(PDO::FETCH_OBJ),
            'attrs' => [],
            'values' => new Values()
        ];
    }

    /**
     * @inheritDoc
     */
    public function withFetchMode(FetchModeInterface $fetchMode): PDOStatementInterface
    {
        if ($this->i['executed'] ?? false) {
            throw new LogicException("prohibited! Has being executed yet");
        }
        $obj = $this->blueprinted();
        $obj->i['fetchMode'] = $fetchMode;
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function withAttributes(array $attrs): PDOStatementInterface
    {
        if ($this->i['executed'] ?? false) {
            throw new LogicException("prohibited! Has being executed yet");
        }
        $obj = $this->blueprinted();
        $obj->i['attrs'] = $attrs;
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function prepared(ExtendedPDOInterface $pdo, string $query): self
    {
        $obj = $this->blueprinted();
        $obj->i['stmt'] = $pdo->vanilla()->prepare($query);
        $obj->i['query'] = $query;
        $obj->i['pdo'] = $pdo;
        $obj->i['executed'] = false;
        return $obj;
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    public function executed(PrinterInterface $p): PrinterInterface
    {
        $this->i['fetchMode']->initialize($this->i['stmt']);
        array_walk (
            $this->i['attrs'],
            function ($val, string $attr): void {
                $this->i['stmt']->setAttribute($attr, $val);
            }
        );
        $this->i['values']->bind($this->i['stmt']);
        $this->i['stmt']->execute();
        return
            $p
                ->with('values', $this->i['values'])
                ->with('stmt', $this)
                ->with('pdo', $this->i['pdo']);
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    public function withValue(ValueInterface $value): PDOStatementInterface
    {
        if ($this->i['executed'] ?? false) {
            throw new LogicException("prohibited! Has being executed yet");
        }
        $obj = $this->blueprinted();
        $obj->i['values'] = $obj->i['values']->with($value);
        return $obj;
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    public function withValues(ValuesInterface $values): self
    {
        if ($this->i['executed'] ?? false) {
            throw new LogicException("prohibited! Has being executed yet");
        }
        $obj = $this->blueprinted();
        $obj->i['values'] = $values;
        return $obj;
    }

    /**
     * @inheritDoc
     * @throws LogicException
     * @return StockPDOStatement
     */
    public function vanilla(): StockPDOStatement
    {
        if (!isset($this->i['stmt'])) {
            throw new LogicException("has not prepared yet");
        }
        return $this->i['stmt'];
    }

    /**
     * Clones instance
     * @return $this
     */
    private function blueprinted(): self
    {
        $obj = new self();
        $obj->i = $this->i;
        return $obj;
    }
}

