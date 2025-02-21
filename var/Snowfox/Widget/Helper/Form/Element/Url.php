<?php

namespace Snowfox\Widget\Helper\Form\Element;

use Snowfox\Common;
use Snowfox\Widget\Helper\Form\Element;

if (!defined('__Snowfox_ROOT_DIR__')) {
    exit;
}

/**
 * Url 表单项帮手类
 *
 * @category Snowfox
 * @package Widget
 * @copyright Copyright (c) 2008 Snowfox team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Url extends Element
{
    use TextInputTrait;

    /**
     * @param string $value
     * @return string
     */
    protected function filterValue(string $value): string
    {
        return htmlspecialchars(Common::idnToUtf8($value));
    }

    /**
     * @return string
     */
    protected function getType(): string
    {
        return 'url';
    }
}
