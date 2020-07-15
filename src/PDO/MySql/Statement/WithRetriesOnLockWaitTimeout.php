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
use PDOException;

/**
 * Class WithRetriesOnLockWaitTimeout
 * SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded; try restarting transaction.
 * Tries to restart a request of an original instance if PDOException with SQLSTATE='1205' has been occurred
 * @package Acc\Core\PersistentData\PDO\MySql\Statement
 */
final class WithRetriesOnLockWaitTimeout implements PDOStatementInterface
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
     * A period into milliseconds for sleep between retries in time of creating of a connection
     * @var int
     */
    private int $sleep;

    /**
     * An input data
     * @var array
     */
    private array $i;

    /**
     * WithRetriesOnLockWaitTimeout constructor.
     * @param PDOStatementInterface $stmt
     * @param int|5 $retries The number of retries
     * @param int|250000 $sleep A period into milliseconds for sleep between retries in time of creating of a connection
     */
    public function __construct(PDOStatementInterface $stmt, ?int $retries = 3, ?int $sleep = 250000)
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

    /**
     * @inheritDoc
     */
    public function executed(): PDOStatementInterface
    {
        $retries = $this->retries;
        $ret = null;
        while (true) {
            try {
                $ret = $this->orig->executed();
                break;
            } catch (PDOException $ex) {
                if ($ex->errorInfo[1] === 1205 && $retries-- > 0) {
                    echo "restart...";
                    if ($this->sleep > 0) {
                        usleep($this->sleep);
                    }
                    $this->orig = $this->orig->prepared($this->i['pdo']->finished(), $this->i['query']);
                    continue;
                }
                throw $ex;
            }
        }
        return $ret;
    }

    /**
     * @inheritDoc
     */
    public function withFetchMode(FetchModeInterface $mode): PDOStatementInterface
    {
        $obj = $this->blueprinted();
        $obj->orig = $obj->withFetchMode($mode);
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function rowCount(): int
    {
        return $this->orig->rowCount();
    }

    /**
     * @inheritDoc
     */
    public function withAttributes(array $attrs): PDOStatementInterface
    {
        $obj = $this->blueprinted();
        $obj->orig = $obj->withAttributes($attrs);
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function withValue(ValueInterface $value): PDOStatementInterface
    {
        $obj = $this->blueprinted();
        $obj->orig = $obj->withValue($value);
        return $obj;
    }

    /**
     * @inheritDoc
     */
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

