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
 * Class WithRetriesOnPdoExceptionOccurred
 * Tries to restart a request of an original instance if PDOException with specified code has been occurred
 * @package Acc\Core\PersistentData\PDO\MySql\Statement
 */
final class WithRetriesOnPdoExceptionOccurred implements PDOStatementInterface
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
     * An input data
     * @var array
     */
    private array $i;

    /**
     * The code of a PDOException that has been looked for
     * @var int
     */
    private int $code;

    /**
     * WithRetriesOnPdoException constructor.
     * @param PDOStatementInterface $stmt
     * @param int $code The code of a PDOException that has been looked for
     * @param int|3 $retries The number of retries
     */
    public function __construct(PDOStatementInterface $stmt, int $code, int $retries = 3)
    {
        $this->orig = $stmt;
        $this->retries = $retries;
        $this->code = $code;
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
        try {
            return $this->orig->executed();
        } catch (PDOException $ex) {
            if ($ex->errorInfo[1] === $this->code && $this->retries > 0) {
                $obj = $this->blueprinted();
                $obj->retries = $this->retries - 1;
                echo "retry - {$obj->retries}! ";
                $obj->orig =
                    $this
                        ->orig
                        ->prepared(
                            $this->i['pdo'],
                            $this->i['query']
                        );
                return $obj->executed();
            }
            throw $ex;
        }
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
        $obj = new self($this->orig, $this->code, $this->retries);
        $obj->i = $this->i;
        return $obj;
    }
}
