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

namespace Acc\Core\PersistentData\PDO\MySql\WithHandledError;

use Acc\Core\PersistentData\PDO\{ExtendedPDOInterface, PDOStatementInterface};
use PDO;
use PDOException;

/**
 * Class Error1205
 * SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded; try restarting transaction.
 * Tries to restart a request of an original instance if PDOException with SQLSTATE='1205' has been occurred
 * @package Acc\Core\PersistentData\PDO\MySql\WithHandledError
 */
final class Error1205 implements ExtendedPDOInterface
{
    /**
     * An original connection
     * @var ExtendedPDOInterface
     */
    private ExtendedPDOInterface $orig;

    /**
     * The number of retries
     * @var int
     */
    private int $retries;

    /**
     * WithError1205Handling constructor.
     * @param ExtendedPDOInterface $connection
     * @param int $retries
     */
    public function __construct(ExtendedPDOInterface $connection, int $retries = 3)
    {
        $this->orig = $connection;
        $this->retries = $retries;
    }

    /**
     * @inheritDoc
     */
    public function trx(callable $callee)
    {
        return
            $this->processed(
                function (ExtendedPDOInterface $pdo) use ($callee) {
                    return $pdo->trx($callee);
                });
    }

    /**
     * @inheritDoc
     */
    public function lastInsertedId(string $name = null): string
    {
        return $this->orig->lastInsertedId($name);
    }

    /**
     * @inheritDoc
     */
    public function with(string $key, $val): self
    {
        $obj = $this->blueprinted();
        $obj->orig = $this->orig->with($key, $val);
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function finished(): self
    {
        $obj = $this->blueprinted();
        $obj->orig = $this->orig->finished();
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function vanilla(): PDO
    {
        return $this->orig->vanilla();
    }

    /**
     * @inheritDoc
     */
    public function prepared(string $query, PDOStatementInterface $stmt = null): PDOStatementInterface
    {
        return
            $this->processed(
                function (ExtendedPDOInterface $pdo) use ($query, $stmt) {
                    return $pdo->prepared($query, $stmt);
                });
    }

    /**
     * @inheritDoc
     */
    public function queried(string $query, PDOStatementInterface $stmt = null): PDOStatementInterface
    {
        return
            $this->processed(
                function (ExtendedPDOInterface $pdo) use ($query, $stmt) {
                    return $pdo->queried($query, $stmt);
                });
    }

    /**
     * @inheritDoc
     */
    public function withAttribute(int $attribute, $value): ExtendedPDOInterface
    {
        $obj = $this->blueprinted();
        $obj->orig = $this->orig->withAttribute($attribute, $value);
        return $obj;
    }

    /**
     *  Tries to restart a transaction if PDOException with SQLSTATE='1205' has been occurred
     * @param callable $callee
     * @return mixed
     */
    private function processed(callable $callee)
    {
        $retries = $this->retries;
        $ret = null;
        while ($retries--) {
            try {
                $ret = call_user_func($callee, $this->orig);
            } catch (PDOException $ex) {
                if ($ex->getCode() == 1205 && $retries > 0) {
                    continue;
                }
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * Clones the instance
     * @return $this
     */
    private function blueprinted(): self
    {
        return new self($this->orig, $this->retries);
    }
}
