<?php
/**
 * Snowfox Blog Platform
 *
 * @copyright  Copyright (c) 2008 Snowfox team (http://www.typecho.org)
 * @license    GNU General Public License 2.0
 * @version    $Id: index.php 1153 2009-07-02 10:53:22Z magike.net $
 */

/** 载入配置支持 */
if (!defined('__Snowfox_ROOT_DIR__') && !@include_once 'config.inc.php') {
    file_exists('./install.php') ? header('Location: install.php') : print('Missing Config File');
    exit;
}

/** 初始化组件 */
\Widget\Init::alloc();

/** 注册一个初始化插件 */
\Snowfox\Plugin::factory('index.php')->call('begin');

/** 开始路由分发 */
\Snowfox\Router::dispatch();

/** 注册一个结束插件 */
\Snowfox\Plugin::factory('index.php')->call('end');
