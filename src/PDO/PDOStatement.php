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
use LogicException, PDO;

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
    public function executed(): PDOStatementInterface
    {
        if (!isset($this->i['executed'])) {
            throw new LogicException("prohibited! Has being prepared yet");
        }
        if ($this->i['executed']) {
            throw new LogicException("prohibited! Has being executed yet");
        }
        $obj = $this->blueprinted();
        $obj->i['fetchMode']->initialize($obj->i['stmt']);
        foreach ($obj->i['attrs'] as $attribute => $value) {
            $obj->i['stmt']->setAttribute($attribute, $value);
        }
        $obj->i['values']->bind($obj->i['stmt']);
        $obj->i['stmt']->execute();
        $obj->i['executed'] = true;
        return $obj;
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
     */
    public function rowCount(): int
    {
        if ($this->i['executed'] ?? false) {
            throw new LogicException("prohibited! Hasn't being executed yet");
        }
        return $this->i['stmt']->rowCount();
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

