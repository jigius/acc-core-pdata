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

namespace Acc\Core\PersistentData\PDO\MySql\FetchMode;

use Acc\Core\PersistentData\PDO\FetchModeInterface;
use PDOStatement, PDO;

/**
 * Class Into
 * Used to sets up `into` mode type
 * @package Acc\Core\PersistentData\PDO\FetchMode
 */
final class Into implements FetchModeInterface
{
    /**
     * An object
     * @var object
     */
    private object $obj;

    /**
     * Obj constructor.
     * @param object $obj
     */
    public function __construct(object $obj)
    {
        $this->obj = $obj;
    }

    /**
     * @inheritDoc
     */
    public function initialize(PDOStatement $statement): void
    {
        $statement->setFetchMode(PDO::FETCH_INTO, $this->obj);
    }
}
