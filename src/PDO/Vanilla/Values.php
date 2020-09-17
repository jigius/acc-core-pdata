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

use PDOStatement;

/**
 * Class Values
 * @package Acc\Core\PersistentData\PDO
 */
final class Values implements ValuesInterface
{
    /**
     * A set of appended values
     * @var array
     */
    private array $itm;

    /**
     * A callable object is used for filtering out appending values
     * @var callable|null
     */
    private $f = null;

    /**
     * Values constructor.
     */
    public function __construct()
    {
        $this->itm = [];
    }

    /**
     * @inheritDoc
     */
    public function with(ValueInterface $value): ValuesInterface
    {
        if ($this->f !== null && !call_user_func($this->f, $value)) {
            return $this;
        }
        $obj = $this->blueprinted();
        $obj->itm[] = $value;
        return $obj;
    }

    /**
     * @inheritDoc
     */
    public function bind(PDOStatement $stmt): void
    {
        foreach ($this->itm as $itm) {
            $itm->bind($stmt);
        }
    }

    /**
     * @inheritDoc
     */
    public function withFilteredOutItems(callable $callee): ValuesInterface
    {
        $obj = $this->blueprinted();
        $obj->f = $callee;
        return $obj;
    }

    /**
     * Clones instance
     * @return $this
     */
    private function blueprinted(): self
    {
        $obj = new self();
        $obj->itm = $this->itm;
        $obj->f = $this->f;
        return $obj;
    }
}
