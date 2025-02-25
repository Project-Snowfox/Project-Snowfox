<?php

namespace Snowfox\Widget\Helper\Form\Element;

use Snowfox\Widget\Helper\Form\Element;
use Snowfox\Widget\Helper\Layout;

if (!defined('__Snowfox_ROOT_DIR__')) {
    exit;
}

/**
 * 提交按钮表单项帮手类
 *
 * @category Snowfox
 * @package Widget
 * @copyright Copyright (c) 2008 Snowfox team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Submit extends Element
{
    /**
     * 初始化当前输入项
     *
     * @param string|null $name 表单元素名称
     * @param array|null $options 选择项
     * @return Layout|null
     */
    public function input(?string $name = null, ?array $options = null): ?Layout
    {
        $this->setAttribute('class', 'Snowfox-option Snowfox-option-submit');
        $input = new Layout('button', ['type' => 'submit']);
        $this->container($input);
        $this->inputs[] = $input;

        return $input;
    }

    /**
     * 设置表单元素值
     *
     * @param mixed $value 表单元素值
     */
    protected function inputValue($value)
    {
        $this->input->html($value ?? 'Submit');
    }
}
