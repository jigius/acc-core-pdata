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

use PDOStatement, DomainException, PDO;

/**
 * Class Value
 * @package Acc\Core\PersistentData\PDO
 */
final class Value implements ValueInterface
{
    /**
     * @var int the type
     */
    private int $type;

    /**
     * @var array An input data
     */
    private array $i;

    /**
     * Value constructor.
     * @param int $type
     */
    public function __construct(int $type = PDO::PARAM_STR)
    {
        $this->type = $type;
        $this->i = [];
    }

    /**
     * @inheritDoc
     */
    public function withName($name): ValueInterface
    {
        $obj = $this->blueprinted();
        $obj->i['name'] = $name;
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function withValue($value): ValueInterface
    {
        $obj = $this->blueprinted();
        $obj->i['value'] = $value;
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function withType(int $type): ValueInterface
    {
        $obj = $this->blueprinted();
        $obj->type = $type;
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function defined(): bool
    {
        return isset($this->i['value']);
    }

    /**
     * @inheritDoc
     */
    public function bind(PDOStatement $stmt): void
    {
        if (!isset($this->i['name'])) {
            throw new DomainException("instance hasn't fully configured yet: `name` is not defined");
        }
        $stmt
            ->bindValue(
                $this->i['name'],
                $this->i['value'] ?? null,
                $this->i['value']? $this->type: PDO::PARAM_NULL
            );
    }

    /**
     * @return $this
     */
    private function blueprinted(): self
    {
        $obj = new self($this->type);
        $obj->i = $this->i;
        return $obj;
    }
}
