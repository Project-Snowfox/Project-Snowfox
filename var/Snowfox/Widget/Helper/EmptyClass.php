<?php

namespace Snowfox\Widget\Helper;

/**
 * widget对象帮手,用于处理空对象方法
 *
 * @category Snowfox
 * @package Widget
 * @copyright Copyright (c) 2008 Snowfox team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class EmptyClass
{
    /**
     * 单例句柄
     *
     * @access private
     * @var EmptyClass
     */
    private static ?EmptyClass $instance = null;

    /**
     * 获取单例句柄
     *
     * @access public
     * @return EmptyClass
     */
    public static function getInstance(): EmptyClass
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 所有方法请求直接返回
     *
     * @access public
     * @param string $name 方法名
     * @param array $args 参数列表
     * @return $this
     */
    public function __call(string $name, array $args)
    {
        return $this;
    }
}
