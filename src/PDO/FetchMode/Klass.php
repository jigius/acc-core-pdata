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
 * Class Klass
 * Used to sets up `class` mode type
 * @package Acc\Core\PersistentData\PDO\FetchMode
 */
final class Klass implements FetchModeInterface
{
    /**
     * A classname
     * @var string
     */
    private string $name;

    /**
     * Constructor's arguments
     * @var array
     */
    private array $ctorArgs;

    /**
     * Klass constructor.
     * @param string $name
     * @param array $ctorArgs
     */
    public function __construct(string $name, array $ctorArgs = [])
    {
        $this->name = $name;
        $this->ctorArgs = $ctorArgs;
    }

    /**
     * @inheritDoc
     */
    public function initialize(PDOStatement $statement): void
    {
        $statement->setFetchMode(PDO::FETCH_CLASS, $this->name, $this->ctorArgs);
    }
}
