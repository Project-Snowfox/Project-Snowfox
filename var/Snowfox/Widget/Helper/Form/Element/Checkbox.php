<?php

namespace Snowfox\Widget\Helper\Form\Element;

use Snowfox\Widget\Helper\Form\Element;
use Snowfox\Widget\Helper\Layout;

if (!defined('__Snowfox_ROOT_DIR__')) {
    exit;
}

/**
 * 多选框帮手类
 *
 * @category Snowfox
 * @package Widget
 * @copyright Copyright (c) 2008 Snowfox team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Checkbox extends Element
{
    /**
     * 选择值
     *
     * @var array
     */
    private array $options = [];

    /**
     * 初始化当前输入项
     *
     * @param string|null $name 表单元素名称
     * @param array|null $options 选择项
     * @return Layout|null
     */
    public function input(?string $name = null, ?array $options = null): ?Layout
    {
        foreach ($options as $value => $label) {
            $this->options[$value] = new Layout('input');
            $item = $this->multiline();
            $id = $this->name . '-' . $this->filterValue($value);
            $this->inputs[] = $this->options[$value];

            $item->addItem($this->options[$value]->setAttribute('name', $this->name . '[]')
                ->setAttribute('type', 'checkbox')
                ->setAttribute('value', $value)
                ->setAttribute('id', $id));

            $labelItem = new Layout('label');
            $item->addItem($labelItem->setAttribute('for', $id)->html($label));
            $this->container($item);
        }

        return current($this->options) ?: null;
    }

    /**
     * 设置表单元素值
     *
     * @param mixed $value 表单元素值
     */
    protected function inputValue($value)
    {
        $values = isset($value) ? (is_array($value) ? $value : [$value]) : [];

        foreach ($this->options as $option) {
            $option->removeAttribute('checked');
        }

        foreach ($values as $value) {
            if (isset($this->options[$value])) {
                $this->options[$value]->setAttribute('checked', 'true');
            }
        }
    }
}
