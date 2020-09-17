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

namespace Acc\Core\PersistentData\PDO\MySql\Statement;

use Acc\Core\PersistentData\PDO\{
    ExtendedPDOInterface,
    FetchModeInterface,
    PDOStatementInterface,
    ValueInterface,
    ValuesInterface
};
use PDOStatement;
use PDOException, LogicException;

/**
 * Class WithResurrectingConnection
 * ERROR 2006 (HY000): MySQL server has gone away
 * Tries to reconnect to a database if PDOException with SQLSTATE='2006' has been occurred
 * @package Acc\Core\PersistentData\PDO\MySql\Statement
 */
final class WithResurrectingConnection implements PDOStatementInterface
{
    /**
     * An original connection
     * @var PDOStatementInterface
     */
    private PDOStatementInterface $orig;

    /**
     * The number of retries
     * @var int
     */
    private int $retries;

    /**
     * A period into microseconds for sleep between retries in time of creating of a connection
     * @var int
     */
    private int $sleep;

    /**
     * An input data
     * @var array
     */
    private array $i;

    /**
     * WithResurrectingConnection constructor.
     * @param PDOStatementInterface $stmt
     * @param int|5 $retries The number of retries
     * @param int|0 $sleep A period into seconds for sleep between retries in time of creating of a connection
     */
    public function __construct(PDOStatementInterface $stmt, int $retries = 5, int $sleep = 0)
    {
        $this->orig = $stmt;
        $this->retries = $retries;
        $this->sleep = $sleep;
        $this->i = [];
    }

    /**
     * @inheritDoc
     */
    public function prepared(ExtendedPDOInterface $pdo, string $query): self
    {
        $obj = $this->blueprinted();
        $obj->i['pdo'] = $pdo;
        $obj->i['query'] = $query;
        $obj->orig = $this->orig->prepared($pdo, $query);
        return $obj;
    }

    public function executed(): PDOStatementInterface
    {
        throw new LogicException("Broken implementation!!!"); /* FIXME: WithResurrectingConnection::class is broken */
        try {
            return $this->orig->executed();
        } catch (PDOException $ex) {
            if ($ex->errorInfo[1] === 2006 && $this->retries-- > 0 && !$this->i['pdo']->vanilla()->inTransaction()) {
                if ($this->sleep > 0) {
                    sleep($this->sleep);
                }
                $this->orig = $this->orig->prepared($this->i['pdo']->finished(), $this->i['query']);
                return $this->executed();
            }
            throw $ex;
        }
    }

    public function withFetchMode(FetchModeInterface $mode): PDOStatementInterface
    {
        $obj = $this->blueprinted();
        $obj->orig = $obj->withFetchMode($mode);
        return $obj;
    }

    public function vanilla(): PDOStatement
    {
        return $this->orig->vanilla();
    }

    public function withAttributes(array $attrs): PDOStatementInterface
    {
        $obj = $this->blueprinted();
        $obj->orig = $obj->withAttributes($attrs);
        return $obj;
    }

    public function withValue(ValueInterface $value): PDOStatementInterface
    {
        $obj = $this->blueprinted();
        $obj->orig = $obj->withValue($value);
        return $obj;
    }

    public function withValues(ValuesInterface $values): PDOStatementInterface
    {
        $obj = $this->blueprinted();
        $obj->orig = $obj->withValues($values);
        return $obj;
    }

    /**
     * Clones the instance
     * @return $this
     */
    private function blueprinted(): self
    {
        $obj = new self($this->orig, $this->retries, $this->sleep);
        $obj->i = $this->i;
        return $obj;
    }
}
