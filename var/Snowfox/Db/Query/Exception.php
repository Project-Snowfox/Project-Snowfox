<?php

namespace Snowfox\Db\Query;

if (!defined('__Snowfox_ROOT_DIR__')) {
    exit;
}

use Snowfox\Db\Exception as DbException;

/**
 * 数据库查询异常类
 *
 * @package Db
 */
class Exception extends DbException
{
}
