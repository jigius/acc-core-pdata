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

use PDO;

/**
 * Class AbstractPDO
 * An abstract implementation of ExtendedPDOInterface contract
 * @package Acc\Core\PersistentData\PDO
 */
abstract class AbstractPDO extends PDO implements ExtendedPDOInterface
{
    /**
     * @var int the current level of transaction
     */
    private int $trxLvl;

    /**
     * AbstractPDO constructor.
     * Attention! The error handling is forced to do by using of exceptions
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array $options driver's options
     * @param array $statements Sql-statements that have to be executed right after connecting
     */
    final public function __construct(
        string $dsn,
        string $username,
        string $password,
        array $options = [],
        array $statements = []
    ) {
        parent::__construct(
            $dsn,
            $username,
            $password,
            array_filter(
                $options,
                function (int $key) {
                    return $key !== self::ATTR_ERRMODE;
                },
                ARRAY_FILTER_USE_KEY
            )
        );
        $this->trxLvl = 0;
        foreach ($statements as $stmt) {
            $this->exec($stmt);
        }
    }

    /**
     * @inheritDoc
     */
    final public function withAttribute(int $attribute, $value): ExtendedPDOInterface
    {
        if ($attribute !== self::ATTR_ERRMODE) {
            $this->setAttribute($attribute, $value);
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function queried(string $query, PDOStatementInterface $stmt = null): PDOStatementInterface
    {
        return
            ($stmt ?? new PDOStatement())
                ->withOrig(
                    $this->query($query)
                );
    }

    /**
     * @inheritDoc
     */
    public function prepared(string $query, PDOStatementInterface $stmt = null): PDOStatementInterface
    {
        return
            ($stmt ?? new PDOStatement())
                ->withOrig(
                    $this->prepare($query)
                );
    }

    /**
     * @inheritDoc
     */
    public function lastInsertedId(string $name = null): string
    {
        return $this->lastInsertId($name);
    }

    /**
     * @inheritDoc
     */
    abstract public function trx(callable $callee, ...$params);
}
