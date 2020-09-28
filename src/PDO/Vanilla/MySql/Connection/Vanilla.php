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

namespace Acc\Core\PersistentData\PDO\Vanilla\MySql\Connection;

use Acc\Core\PersistentData\PDO\{
    ExtendedPDOInterface,
    Vanilla\PDOStatement,
    PDOStatementInterface
};
use PDO;
use Exception, LogicException, DomainException;

/**
 * Class Connection
 * An implementation of `ExtendedPDOInterface` contract for MySQL
 * @package Acc\Core\PersistentData\PDO\MySql\Connection
 */
final class Vanilla implements ExtendedPDOInterface
{
    /**
     * A default statement instance used for prepared requests
     * @var PDOStatementInterface|null
     */
    private ?PDOStatementInterface $stmt;
    /**
     * A data is used for creates a connection with
     * a database(the keys are: dsn, username, password, options, initEvent)
     * @var array
     */
    private array $i;

    /**
     * A connection with a database
     * @var PDO|null
     */
    private ?PDO $original = null;

    /**
     * Connection constructor.
     * @param PDOStatementInterface|null $stmt
     */
    final public function __construct(?PDOStatementInterface $stmt = null)
    {
        $this->i = [
            'username' => "",
            'password' => "",
            'options' => []
        ];
        $this->stmt = $stmt;
    }

    public function withStatement(PDOStatementInterface $stmt): self
    {
        $obj = $this->blueprinted();
        $obj->stmt = $stmt;
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function with(string $key, $val): self
    {
        $obj = $this->blueprinted();
        $obj->i[$key] = $val;
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function finished(): self
    {
        if (!isset($this->i['dsn']) || !is_string($this->i['dsn'])) {
            throw new DomainException("`dsn` is invalid. It must to be a not empty string");
        }
        if (!is_array($this->i['options'])) {
            throw new DomainException("`options` is invalid. It must to be an array");
        }
        $this
            ->original =
                new PDO(
                    $this->i['dsn'],
                    $this->i['username'],
                    $this->i['password'],
                    array_merge(
                        $this->i['options'],
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                        ]
                    )
                );
        if (isset($this->i['initEvent'])) {
            if (!is_callable($this->i['initEvent'])) {
                throw new DomainException("`initEvent` is invalid. It must be a callee");
            }
            call_user_func($this->i['initEvent'], $this);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function withAttribute(int $attribute, $value): ExtendedPDOInterface
    {
        if ($attribute === PDO::ATTR_ERRMODE) {
            throw new LogicException("PDO::ATTR_ERRMODE is prohibited from changing");
        }
        $this->vanilla()->setAttribute($attribute, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function queried(string $query): PDOStatementInterface
    {
        return $this->prepared($query)->executed();
    }

    /**
     * @inheritDoc
     */
    public function prepared(string $query): PDOStatementInterface
    {
        return $this->statement()->prepared($this, $query);
    }

    /**
     * @inheritDoc
     */
    public function vanilla(): PDO
    {
        if ($this->original === null) {
            throw new LogicException("has not been initialized yet");
        }
        return $this->original;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function trx(callable $callee)
    {
        $savepoint = null;
        try {
            if ($this->vanilla()->inTransaction()) {
                $savepoint = bin2hex(random_bytes(4));
                $this->vanilla()->exec("SAVEPOINT SP{$savepoint}");
            } else {
                $this->vanilla()->beginTransaction();
            }
            $ret = call_user_func($callee, $this);
            if ($savepoint !== null) {
                $this->vanilla()->exec("RELEASE SAVEPOINT SP{$savepoint}");
            } else {
                $this->vanilla()->commit();
            }
            return $ret;
        } catch (Exception $ex) {
            if ($savepoint !== null) {
                $this->vanilla()->exec("ROLLBACK TO SP{$savepoint}");
            } else {
                $this->vanilla()->rollBack();
            }
            throw $ex;
        }
    }

    /**
     * Clones an instance
     * @return self
     */
    private function blueprinted(): self
    {
        $obj = new self($this->stmt);
        $obj->i = $this->i;
        $obj->original = $this->original;
        return $obj;
    }

    /**
     * @return PDOStatementInterface
     */
    private function statement(): PDOStatementInterface
    {
       return $this->stmt ?? new PDOStatement();
    }
}
