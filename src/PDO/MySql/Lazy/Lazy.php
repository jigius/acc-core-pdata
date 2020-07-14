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

namespace Acc\Core\PersistentData\PDO\MySql\Lazy;

use Acc\Core\PrinterInterface;
use Acc\Core\PersistentData\PDO\{ExtendedPDOInterface, PDOStatementInterface};
use Exception, DomainException, PDO;

/**
 * Class LazyConnection
 * @package Acc\Core\PersistentData\PDO\MySql
 */
final class Lazy implements ExtendedPDOInterface
{
    private ExtendedPDOInterface $orig;

    private bool $connected;

    private QueueInterface $queue;

    private PrinterInterface $rp;

    private bool $trxIsIssued;

    /**
     * Lazy constructor.
     * @param ExtendedPDOInterface $connection
     * @param QueueInterface|null $queue
     * @param PrinterInterface|null $requestPrinter
     */
    final public function __construct(
        ExtendedPDOInterface $connection,
        QueueInterface $queue = null,
        PrinterInterface $requestPrinter = null
    ) {
        $this->orig = $connection;
        $this->connected = false;
        $this->queue = $queue ?? new Queue();
        $this->rp = $requestPrinter ?? new RequestPrinter();
        $this->trxIsIssued = false;
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
        /*
         * does not pass the request into the original instance yet
         */
        return $this->blueprinted();
    }

    /**
     * @inheritDoc
     */
    public function vanilla(): PDO
    {
        if (!$this->connected) {
            return $this->connected()->vanilla();
        }
        return $this->orig->vanilla();
    }

    /**
     * @inheritDoc
     */
    final public function withAttribute(int $attribute, $value): self
    {
        if ($this->connected) {
            $this->orig = $this->orig->withAttribute($attribute, $value);
        } else {
            $this->queue =
                $this->queue->withAttribute(
                    function (ExtendedPDOInterface $pdo) use ($attribute, $value) {
                        $pdo->vanilla()->setAttribute($attribute, $value);
                    }
                );
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function queried(string $query, PDOStatementInterface $stmt = null): PDOStatementInterface
    {
        if (!$this->connected) {
            return $this->connected()->queried($query, $stmt);
        }
        return $this->orig->queried($query, $stmt);
    }

    /**
     * @inheritDoc
     */
    public function prepared(string $query, PDOStatementInterface $stmt = null): PDOStatementInterface
    {
        if (!$this->connected) {
            return $this->connected()->prepared($query, $stmt);
        }
        return $this->orig->prepared($query, $stmt);
    }

    /**
     * @inheritDoc
     */
    public function lastInsertedId(string $name = null): string
    {
        if (!$this->connected) {
            return $this->connected()->lastInsertedId($name);
        }
        return $this->orig->lastInsertedId($name);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function trx(callable $callee)
    {
        if ($this->connected) {
            return $this->orig->trx($callee);
        }
        $uniqNum = bin2hex(random_bytes(6));
        if (!$this->trxIsIssued) {
            $cb =
                function (ExtendedPDOInterface $pdo): void {
                    $pdo->vanilla()->beginTransaction();
                };
        } else {
            $cb =
                function (ExtendedPDOInterface $pdo) use ($uniqNum): void
                {
                    $pdo->vanilla()->exec("SAVEPOINT SP{$uniqNum}");
                };
        }
        $this
            ->queue =
                $this
                    ->queue
                    ->withRequest(
                        $this
                            ->rp
                            ->with('uniqNum', $uniqNum)
                            ->with('cb', $cb)
                            ->finished()
                    );
        $savepoint = null;
        try {
            $ret = call_user_func($callee);
            if (!$this->trxIsIssued) {
                $cb =
                    function (ExtendedPDOInterface $pdo): void {
                        $pdo->vanilla()->commit();
                    };
            } else {
                $cb =
                    function (ExtendedPDOInterface $pdo) use ($uniqNum): void
                    {
                        $pdo->vanilla()->exec("RELEASE SAVEPOINT SP{$uniqNum}");
                    };
            }
            $this
                ->queue =
                    $this
                        ->queue
                        ->withRequest(
                            $this
                                ->rp
                                ->with('uniqNum', $uniqNum)
                                ->with('cb', $cb)
                                ->finished()
                        );
            return $ret;
        } catch (Exception $ex) {
            if (!$this->trxIsIssued) {
                $cb =
                    function (ExtendedPDOInterface $pdo): void {
                        $pdo->vanilla()->rollBack();
                    };
            } else {
                $cb =
                    function (ExtendedPDOInterface $pdo) use ($uniqNum): void
                    {
                        $pdo->vanilla()->exec("ROLLBACK TO SP{$uniqNum}");
                    };
            }
            $this
                ->queue =
                $this
                    ->queue
                    ->withRequest(
                        $this
                            ->rp
                            ->with('uniqNum', $uniqNum)
                            ->with('cb', $cb)
                            ->finished()
                    );
            throw $ex;
        }
    }

    /**
     * Clones an instance
     * @return $this
     */
    private function blueprinted(): self
    {
        $obj = new self($this->orig, $this->queue);
        $obj->connected = $this->connected;
        $obj->trxIsIssued = $this->trxIsIssued;
        return $obj;
    }

    private function connected(): self
    {
        if ($this->connected) {
            throw new DomainException("already connected");
        }
        $this->orig = $this->orig->finished();
        $this->connected = true;
        $this->queue->process($this);
        return $this;
    }
}
