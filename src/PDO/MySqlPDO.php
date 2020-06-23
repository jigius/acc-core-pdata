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

use PDOException, Exception;

/**
 * Class MySqlPDO
 * An implementation of `ExtendedPDOInterface` contract for MYSQL
 * @package Acc\Core\PersistentData\PDO
 */
final class MySqlPDO extends AbstractPDO
{
    /*
     * Lock wait timeout exceeded; try restarting transaction
     */
    const LOCK_WAIT_TIMEOUT_SQLSTATE = '1205';

    /**
     * Uses three tries to restarts transaction if PDOException with SQLSTATE='1205 occurs'
     * @inheritDoc
     * @throws PDOException
     */
    final public function trx(callable $callee, ...$params)
    {
        $retries = 3;
        $this->beginTrx();
        $ret = false;
        do {
            try {
                $ret = call_user_func_array($callee, $params);
                $this->commitTrx();
                break;
            } catch (PDOException $ex) {
                if ($ex->getCode() !== self::LOCK_WAIT_TIMEOUT_SQLSTATE || $retries === 0) {
                    $this->rollbackTrx();
                    throw $ex;
                }
            } catch (Exception $ex) {
                $this->rollbackTrx();
                throw $ex;
            }
        } while ($retries-- > 0);
        return $ret;
    }
}
