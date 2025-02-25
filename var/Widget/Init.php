<?php

namespace Widget;

use Snowfox\Common;
use Snowfox\Cookie;
use Snowfox\Date;
use Snowfox\Db;
use Snowfox\I18n;
use Snowfox\Plugin;
use Snowfox\Response;
use Snowfox\Router;
use Snowfox\Widget;

if (!defined('__Snowfox_ROOT_DIR__')) {
    exit;
}

/**
 * 初始化模块
 *
 * @package Widget
 */
class Init extends Widget
{
    /**
     * 入口函数,初始化路由器
     *
     * @access public
     * @return void
     * @throws Db\Exception
     */
    public function execute()
    {
        /** 初始化exception */
        if (!defined('__Snowfox_DEBUG__') || !__Snowfox_DEBUG__) {
            set_exception_handler(function (\Throwable $exception) {
                Response::getInstance()->clean();
                ob_end_clean();

                ob_start(function ($content) {
                    Response::getInstance()->sendHeaders();
                    return $content;
                });

                if (404 == $exception->getCode()) {
                    ExceptionHandle::alloc();
                } else {
                    Common::error($exception);
                }

                exit;
            });
        }

        // init class
        define('__Snowfox_CLASS_ALIASES__', [
            'Snowfox_Plugin_Interface'    => '\Snowfox\Plugin\PluginInterface',
            'Snowfox_Widget_Helper_Empty' => '\Snowfox\Widget\Helper\EmptyClass',
            'Snowfox_Db_Adapter_Mysql'    => '\Snowfox\Db\Adapter\Mysqli',
            'Widget_Abstract'             => '\Widget\Base',
            'Widget_Abstract_Contents'    => '\Widget\Base\Contents',
            'Widget_Abstract_Comments'    => '\Widget\Base\Comments',
            'Widget_Abstract_Metas'       => '\Widget\Base\Metas',
            'Widget_Abstract_Options'     => '\Widget\Base\Options',
            'Widget_Abstract_Users'       => '\Widget\Base\Users',
            'Widget_Metas_Category_List'  => '\Widget\Metas\Category\Rows',
            'Widget_Contents_Page_List'   => '\Widget\Contents\Page\Rows',
            'Widget_Plugins_List'         => '\Widget\Plugins\Rows',
            'Widget_Themes_List'          => '\Widget\Themes\Rows',
            'Widget_Interface_Do'         => '\Widget\ActionInterface',
            'Widget_Do'                   => '\Widget\Action',
            'AutoP'                       => '\Utils\AutoP',
            'PasswordHash'                => '\Utils\PasswordHash',
            'Markdown'                    => '\Utils\Markdown',
            'HyperDown'                   => '\Utils\HyperDown',
            'Helper'                      => '\Utils\Helper',
            'Upgrade'                     => '\Utils\Upgrade'
        ]);

        /** 对变量赋值 */
        $options = Options::alloc();

        /** 语言包初始化 */
        if ($options->lang && $options->lang != 'zh_CN') {
            $dir = defined('__Snowfox_LANG_DIR__') ? __Snowfox_LANG_DIR__ : __Snowfox_ROOT_DIR__ . '/usr/langs';
            I18n::setLang($dir . '/' . $options->lang . '.mo');
        }

        /** 备份文件目录初始化 */
        if (!defined('__Snowfox_BACKUP_DIR__')) {
            define('__Snowfox_BACKUP_DIR__', __Snowfox_ROOT_DIR__ . '/usr/backups');
        }

        /** cookie初始化 */
        Cookie::setPrefix($options->rootUrl);
        if (defined('__Snowfox_COOKIE_OPTIONS__')) {
            Cookie::setOptions(__Snowfox_COOKIE_OPTIONS__);
        }

        /** 初始化路由器 */
        Router::setRoutes($options->routingTable);

        /** 初始化插件 */
        Plugin::init($options->plugins);

        /** 初始化回执 */
        $this->response->setCharset($options->charset);
        $this->response->setContentType($options->contentType);

        /** 初始化时区 */
        Date::setTimezoneOffset($options->timezone);

        /** 开始会话, 减小负载只针对后台打开session支持 */
        if ($options->installed && User::alloc()->hasLogin()) {
            @session_start();
        }
    }
}
