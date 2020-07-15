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

namespace Acc\Core\PersistentData\PDO\MySql\Connection;

use Acc\Core\PersistentData\PDO\{ExtendedPDOInterface, PDOStatement, PDOStatementInterface};
use Exception, PDO, DomainException;

/**
 * Class Connection
 * An implementation of `ExtendedPDOInterface` contract for MySQL
 * @package Acc\Core\PersistentData\PDO\MySql\Connection
 */
final class Vanilla implements ExtendedPDOInterface
{
    /**
     * A default statement instance used for prepared requests
     * @var PDOStatementInterface
     */
    private PDOStatementInterface $stmt;
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
    private ?PDO $orig;

    /**
     * Connection constructor.
     * @param PDOStatementInterface|null $stmt
     */
    final public function __construct(?PDOStatementInterface $stmt = null)
    {
        $this->i = [
            'username' => "",
            'password' => "",
            'initEvent' => []
        ];
        $this->stmt = $stmt ?? new PDOStatement();
        $this->orig = null;
    }

    /**
     * @inheritDoc
     */
    public function with(string $key, $val): self
    {
        $obj = $this->blueprinted();
        if (isset($this->i[$key]) && is_array($this->i[$key]) && $key === 'initEvent') {
            $obj->i[$key][] = $val;
        } else {
            $obj->i[$key] = $val;
        }
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
        if (isset($this->i['options']) && !is_array($this->i['options'])) {
            throw new DomainException("`options` is invalid. It must to be an array");
        }
        $options = $this->i['options'] ?? [];
        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
        $this
            ->orig =
                new PDO(
                    $this->i['dsn'],
                    $this->i['username'],
                    $this->i['password'],
                    $options
                );
        array_walk(
            $this->i['initEvent'],
            function (callable $hdlr) {
                call_user_func($hdlr, $this);
            }
        );
        return $this;
    }

    /**
     * @inheritDoc
     */
    final public function withAttribute(int $attribute, $value): ExtendedPDOInterface
    {
        $this->validate();
        if ($attribute !== PDO::ATTR_ERRMODE) {
            $this->vanilla()->setAttribute($attribute, $value);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function queried(string $query, PDOStatementInterface $stmt = null): PDOStatementInterface
    {
        return $this->prepared($query, $stmt)->executed();
    }

    /**
     * @inheritDoc
     */
    public function prepared(string $query, PDOStatementInterface $stmt = null): PDOStatementInterface
    {
        $this->validate();
        return ($stmt ?? $this->stmt)->prepared($this, $query);
    }

    /**
     * @inheritDoc
     */
    public function lastInsertedId(string $name = null): string
    {
        $this->validate();
        return $this->vanilla()->lastInsertId($name);
    }

    /**
     * @inheritDoc
     */
    public function vanilla(): PDO
    {
        return $this->orig;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function trx(callable $callee)
    {
        $this->validate();
        $savepoint = null;
        try {
            if ($this->vanilla()->inTransaction()) {
                $savepoint = bin2hex(random_bytes(4));
                $this->vanilla()->exec("SAVEPOINT SP{$savepoint}");
            } else {
                $this->vanilla()->beginTransaction();
            }
            $ret = call_user_func($callee);
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
     * @return $this
     */
    private function blueprinted(): self
    {
        $obj = new self($this->stmt);
        $obj->i = $this->i;
        $obj->orig = $this->orig;
        return $obj;
    }

    private function validate(): void
    {
        if ($this->orig === null) {
            throw new DomainException("there is no created connection with a database");
        }
    }
}
