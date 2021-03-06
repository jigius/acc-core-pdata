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
    const LOCK_WAIT_TIMEOUT_SQLSTATE = "1205";

    /**
     * Uses three tries to restarts transaction if PDOException with SQLSTATE='1205 occurs'
     * @inheritDoc
     * @throws PDOException
     * @throws Exception
     */
    public function trx(callable $callee, ...$params)
    {
        $savepoint = null;
        try {
            if (parent::inTransaction()) {
                $savepoint = bin2hex(random_bytes(4));
                $this->exec("SAVEPOINT SP{$savepoint}");
            } else {
                parent::beginTransaction();
            }
            $retries = 3;
            $ret = null;
            do {
                try {
                    $ret = call_user_func_array($callee, $params);
                    break;
                } catch (PDOException $ex) {
                    if ($ex->getCode() == self::LOCK_WAIT_TIMEOUT_SQLSTATE && $retries-- > 0) {
                        continue;
                    }
                    throw $ex;
                }
            } while (true);
            if ($savepoint !== null) {
                $this->exec("RELEASE SAVEPOINT SP{$savepoint}");
            } else {
                parent::commit();
            }
            return $ret;
        } catch (Exception $ex) {
            if ($savepoint !== null) {
                $this->exec("ROLLBACK TO SP{$savepoint}");
            } else {
                parent::rollBack();
            }
            throw $ex;
        }
    }
}
