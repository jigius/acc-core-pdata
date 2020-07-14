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
     * @var ExtendedPDOInterface|null
     */
    private ?ExtendedPDOInterface $pdo = null;

    private ?string $query = null;
    /**
     * An injected original PDOStatement object
     * @var OrigPDOStatement|null
     */
    private ?OrigPDOStatement $orig = null;

    /**
     * Inputs data
     * @var array
     */
    private array $i;

    /**
     * An executed original PDOStatement object
     * @var OrigPDOStatement|null
     */
    private ?OrigPDOStatement $r = null;

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

    public function withRequestedPdo(ExtendedPDOInterface $pdo): PDOStatementInterface
    {
        $obj = $this->blueprinted();
        $obj->pdo = $pdo;
        return $obj;
    }

    public function withQuery(string $query): PDOStatementInterface
    {
        $obj = $this->blueprinted();
        $obj->query = $query;
        return $obj;
    }

    /**
     * @inheritDoc
     * @throws \Exception
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
        try {
            $this->orig->execute();
        } catch (\Exception $ex) {
            dump($ex);
            if ($this->pdo !== null && $this->query !== null) {
                echo "resurrected!\n";
                return $this->pdo->finished()->prepared($this->query, $this)->executed();
            } else {
                throw $ex;
            }
        }
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

    public function withPdo(ExtendedPDOInterface $pdo): self
    {
        $obj = $this->blueprinted();
        $obj->pdo = $pdo;
        return $obj;
    }

    /**
     * @inheritDoc
     * @throws LogicException
     */
    public function withValues(ValuesInterface $values): self
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
    public function withVanilla(OrigPDOStatement $stmt): PDOStatementInterface
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
    public function vanilla(): OrigPDOStatement
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
        $obj->query = $this->query;
        $obj->pdo = $this->pdo;
        return $obj;
    }
}

