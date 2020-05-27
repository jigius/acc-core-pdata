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

use Acc\Core\PersistentData\PDO\FetchMode\Vanilla;
use PDOStatement as OrigPDOStatement;
use LogicException, RuntimeException, PDO;

/**
 * Class PDOStatement
 * @package Acc\Core\PersistentData\PDO
 */
final class PDOStatement implements PDOStatementInterface
{
    /**
     * An injected original PDOStatement object
     * @var OrigPDOStatement|null
     */
    private ?OrigPDOStatement $orig;

    /**
     * Inputs data
     * @var array
     */
    private array $i;

    /**
     * An executed original PDOStatement object
     * @var OrigPDOStatement|null
     */
    private ?OrigPDOStatement $r;

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
        $this->orig = null;
        $this->r = null;
    }

    /**
     * @inheritDoc
     */
    public function withFetchMode(FetchModeInterface $fetchMode): PDOStatementInterface
    {
        if ($this->r !== null) {
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
        if ($this->r !== null) {
            throw new LogicException("prohibited! Has being executed yet");
        }
        $obj = $this->blueprinted();
        $obj->i['attrs'] = $attrs;
        return $obj;
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function executed(): PDOStatementInterface
    {
        if ($this->r !== null) {
            throw new LogicException("prohibited! Has being executed yet");
        }
        $this->i['fetchMode']->initialize($this->orig);
        foreach ($this->i['attrs'] as $attribute => $value) {
            $this->orig->setAttribute($attribute, $value);
        }
        $this->i['values']->bind($this->orig);
        $this->orig->execute();
        $obj = $this->blueprinted();
        $obj->r = $this->orig;
        return $obj;
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    public function withValue(ValueInterface $value): PDOStatementInterface
    {
        if ($this->r !== null) {
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
    public function withValues(ValuesInterface $values): PDOStatementInterface
    {
        if ($this->r !== null) {
            throw new LogicException("prohibited! Has being executed yet");
        }
        $obj = $this->blueprinted();
        $obj->i['values'] = $values;
        return $obj;
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    public function rowCount(): int
    {
        if ($this->r === null) {
            throw new LogicException("prohibited! Hasn't being executed yet");
        }
        return $this->orig->rowCount();
    }

    /**
     * @param OrigPDOStatement $stmt
     * @return PDOStatementInterface
     */
    public function withOrig(OrigPDOStatement $stmt): PDOStatementInterface
    {
        $obj = $this->blueprinted();
        $obj->orig = $stmt;
        $obj->r = null;
        return $obj;
    }

    /**
     * @return OrigPDOStatement
     * @throws LogicException;
     */
    public function orig(): OrigPDOStatement
    {
        if ($this->orig === null) {
            throw new LogicException("original object has not being defined yet");
        }
        return $this->orig;
    }

    /**
     * Clones instance
     * @return $this
     */
    private function blueprinted(): self
    {
        $obj = new self();
        $obj->orig = $this->orig;
        $obj->i = $this->i;
        $obj->r = $this->r;
        return $obj;
    }
}
