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

namespace Acc\Core\PersistentData\PDO\MySql\Connection\Lazy;

use Acc\Core\PrinterInterface;
use Acc\Core\PersistentData\PDO\{ExtendedPDOInterface, PDOStatementInterface};
use DomainException, Throwable, PDOException;
use PDO;

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

    private int $nestedLevel;

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
        $this->nestedLevel = 0;
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
     * @throws Throwable
     */
    public function trx(callable $callee)
    {
        if ($this->connected) {
            return $this->orig->trx($callee);
        }
        $uniqNum = bin2hex(random_bytes(6));
        $this
            ->queue =
                $this
                    ->queue
                    ->withRequest(
                        $this
                            ->rp
                            ->with('id', $uniqNum)
                            ->with('cb',
                                function (ExtendedPDOInterface $pdo) use ($uniqNum): void {
                                    if ($this->nestedLevel++ > 0) {
                                        echo "SAVEPOINT SP{$uniqNum}\n";
                                        $pdo->queried("SAVEPOINT SP{$uniqNum}");
                                    } else {
                                        echo "beginTransaction()\n";
                                        $pdo->vanilla()->beginTransaction();
                                    }
                                }
                            )
                            ->finished()
                    );
        try {
            $ret = call_user_func($callee);
        } catch (PDOException $ex) {
            if (--$this->nestedLevel > 0) {
                throw $ex;
            }
            $this->connected = false;
            $this->queue = $queue ?? new Queue();
            $this->queried("SET SESSION wait_timeout=10");
            $this->nestedLevel = 0;
            return $this->trx($callee);
        } catch (Throwable $ex) {
            $cb =
                function (ExtendedPDOInterface $pdo) use ($uniqNum): void {
                    if (--$this->nestedLevel > 0) {
                        echo "ROLLBACK TO SP{$uniqNum}\n";
                        $pdo->queried("ROLLBACK TO SP{$uniqNum}");
                    } else {
                        echo "rollback()\n";
                        $pdo->vanilla()->rollBack();
                    }
                };
            if ($this->connected) {
                call_user_func($cb, $this);
            } else {
                $this
                    ->queue =
                    $this
                        ->queue
                        ->withRequest(
                            $this
                                ->rp
                                ->with('id', $uniqNum)
                                ->with('cb', $cb)
                                ->finished()
                        );
            }
            throw $ex;

        }
        $cb =
            function (ExtendedPDOInterface $pdo) use ($uniqNum): void {
                if (--$this->nestedLevel > 0) {
                    echo "RELEASE SAVEPOINT SP{$uniqNum}\n";
                    $pdo->queried("RELEASE SAVEPOINT SP{$uniqNum}");
                } else {
                    echo "commit()\n";
                    $pdo->vanilla()->commit();
                }
            };
        if ($this->connected) {
            call_user_func($cb, $this);
        } else {
            $this
                ->queue =
                $this
                    ->queue
                    ->withRequest(
                        $this
                            ->rp
                            ->with('id', $uniqNum)
                            ->with('cb', $cb)
                            ->finished()
                    );
        }
        return $ret;
    }

    /**
     * Clones an instance
     * @return $this
     */
    private function blueprinted(): self
    {
        $obj = new self($this->orig, $this->queue);
        $obj->connected = $this->connected;
        $obj->nestedLevel = $this->nestedLevel;
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
