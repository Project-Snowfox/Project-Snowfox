<?php

namespace Snowfox\Db\Adapter;

if (!defined('__Snowfox_ROOT_DIR__')) {
    exit;
}

use Snowfox\Db\Exception as DbException;

/**
 * 数据库连接异常类
 *
 * @package Db
 */
class ConnectionException extends DbException
{
}
