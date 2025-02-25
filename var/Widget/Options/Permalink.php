<?php

namespace Widget\Options;

use Snowfox\Common;
use Snowfox\Cookie;
use Snowfox\Db\Exception;
use Snowfox\Http\Client;
use Snowfox\Router\Parser;
use Snowfox\Widget\Helper\Form;
use Widget\ActionInterface;
use Widget\Base\Options;
use Widget\Notice;

if (!defined('__Snowfox_ROOT_DIR__')) {
    exit;
}

/**
 * 基本设置组件
 *
 * @author qining
 * @category Snowfox
 * @package Widget
 * @copyright Copyright (c) 2008 Snowfox team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Permalink extends Options implements ActionInterface
{
    /**
     * 检查pagePattern里是否含有必要参数
     *
     * @param mixed $value
     * @return bool
     */
    public function checkPagePattern($value): bool
    {
        return strpos($value, '{slug}') !== false
            || strpos($value, '{cid}') !== false
            || strpos($value, '{directory}') !== false;
    }

    /**
     * 检查categoryPattern里是否含有必要参数
     *
     * @param mixed $value
     * @return bool
     */
    public function checkCategoryPattern($value): bool
    {
        return strpos($value, '{slug}') !== false
            || strpos($value, '{mid}') !== false
            || strpos($value, '{directory}') !== false;
    }

    /**
     * 检测是否可以rewrite
     *
     * @param string $value 是否打开rewrite
     * @return bool
     */
    public function checkRewrite(string $value): bool
    {
        if ($value) {
            $this->user->pass('administrator');

            /** 首先直接请求远程地址验证 */
            $client = Client::get();
            $hasWrote = false;

            if (!file_exists(__Snowfox_ROOT_DIR__ . '/.htaccess') && strpos(php_sapi_name(), 'apache') !== false) {
                if (is_writable(__Snowfox_ROOT_DIR__)) {
                    $parsed = parse_url($this->options->siteUrl);
                    $basePath = empty($parsed['path']) ? '/' : $parsed['path'];
                    $basePath = rtrim($basePath, '/') . '/';

                    $hasWrote = file_put_contents(__Snowfox_ROOT_DIR__ . '/.htaccess', "<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase {$basePath}
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ {$basePath}index.php/$1 [L]
</IfModule>");
                }
            }

            try {
                if ($client) {
                    /** 发送一个rewrite地址请求 */
                    $client->setData(['do' => 'remoteCallback'])
                        ->setHeader('User-Agent', $this->options->generator)
                        ->setHeader('X-Requested-With', 'XMLHttpRequest')
                        ->send(Common::url('/action/ajax', $this->options->siteUrl));

                    if (200 == $client->getResponseStatus() && 'OK' == $client->getResponseBody()) {
                        return true;
                    }
                }

                if (false !== $hasWrote) {
                    @unlink(__Snowfox_ROOT_DIR__ . '/.htaccess');

                    //增强兼容性,使用wordpress的redirect式rewrite规则,虽然效率有点地下,但是对fastcgi模式兼容性较好
                    $hasWrote = file_put_contents(__Snowfox_ROOT_DIR__ . '/.htaccess', "<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase {$basePath}
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . {$basePath}index.php [L]
</IfModule>");

                    //再次进行验证
                    $client = Client::get();

                    if ($client) {
                        /** 发送一个rewrite地址请求 */
                        $client->setData(['do' => 'remoteCallback'])
                            ->setHeader('User-Agent', $this->options->generator)
                            ->setHeader('X-Requested-With', 'XMLHttpRequest')
                            ->send(Common::url('/action/ajax', $this->options->siteUrl));

                        if (200 == $client->getResponseStatus() && 'OK' == $client->getResponseBody()) {
                            return true;
                        }
                    }

                    unlink(__Snowfox_ROOT_DIR__ . '/.htaccess');
                }
            } catch (Client\Exception $e) {
                if ($hasWrote) {
                    @unlink(__Snowfox_ROOT_DIR__ . '/.htaccess');
                }
                return false;
            }

            return false;
        } elseif (file_exists(__Snowfox_ROOT_DIR__ . '/.htaccess')) {
            @unlink(__Snowfox_ROOT_DIR__ . '/.htaccess');
        }

        return true;
    }

    /**
     * 执行更新动作
     *
     * @throws Exception
     */
    public function updatePermalinkSettings()
    {
        $customPattern = $this->request->get('customPattern');
        $postPattern = $this->request->get('postPattern');

        /** 验证格式 */
        if ($this->form()->validate()) {
            Cookie::set('__Snowfox_form_item_postPattern', $customPattern);
            $this->response->goBack();
        }

        $patternValid = $this->checkRule($postPattern);

        /** 解析url pattern */
        if ('custom' == $postPattern) {
            $postPattern = '/' . ltrim($this->encodeRule($customPattern), '/');
        }

        $settings = defined('__Snowfox_REWRITE__') ? [] : $this->request->from('rewrite');
        if (isset($postPattern) && $this->request->is('pagePattern')) {
            $routingTable = $this->options->routingTable;
            $routingTable['post']['url'] = $postPattern;
            $routingTable['page']['url'] = '/' . ltrim($this->encodeRule($this->request->get('pagePattern')), '/');
            $routingTable['category']['url'] = '/' . ltrim($this->encodeRule($this->request->get('categoryPattern')), '/');
            $routingTable['category_page']['url'] = rtrim($routingTable['category']['url'], '/') . '/[page:digital]/';

            if (isset($routingTable[0])) {
                unset($routingTable[0]);
            }

            $settings['routingTable'] = json_encode($routingTable);
        }

        foreach ($settings as $name => $value) {
            $this->update(['value' => $value], $this->db->sql()->where('name = ?', $name));
        }

        if ($patternValid) {
            Notice::alloc()->set(_t("设置已经保存"), 'success');
        } else {
            Notice::alloc()->set(_t("自定义链接与现有规则存在冲突! 它可能影响解析效率, 建议你重新分配一个规则."));
        }
        $this->response->goBack();
    }

    /**
     * 输出表单结构
     *
     * @return Form
     */
    public function form(): Form
    {
        /** 构建表格 */
        $form = new Form($this->security->getRootUrl('index.php/action/options-permalink'), Form::POST_METHOD);

        if (!defined('__Snowfox_REWRITE__')) {
            /** 是否使用地址重写功能 */
            $rewrite = new Form\Element\Radio(
                'rewrite',
                ['0' => _t('不启用'), '1' => _t('启用')],
                $this->options->rewrite,
                _t('是否使用地址重写功能'),
                _t('地址重写即 rewrite 功能是某些服务器软件提供的优化内部连接的功能.') . '<br />'
                . _t('打开此功能可以让你的链接看上去完全是静态地址.')
            );

            // disable rewrite check when rewrite opened
            if (!$this->options->rewrite && !$this->request->is('enableRewriteAnyway=1')) {
                $errorStr = _t('重写功能检测失败, 请检查你的服务器设置');

                /** 如果是apache服务器, 可能存在无法写入.htaccess文件的现象 */
                if (
                    strpos(php_sapi_name(), 'apache') !== false
                    && !file_exists(__Snowfox_ROOT_DIR__ . '/.htaccess')
                    && !is_writable(__Snowfox_ROOT_DIR__)
                ) {
                    $errorStr .= '<br /><strong>' . _t('我们检测到你使用了apache服务器, 但是程序无法在根目录创建.htaccess文件, 这可能是产生这个错误的原因.')
                        . _t('请调整你的目录权限, 或者手动创建一个.htaccess文件.') . '</strong>';
                }

                $errorStr .=
                    '<br /><input type="checkbox" name="enableRewriteAnyway" id="enableRewriteAnyway" value="1" />'
                    . ' <label for="enableRewriteAnyway">' . _t('如果你仍然想启用此功能, 请勾选这里') . '</label>';
                $rewrite->addRule([$this, 'checkRewrite'], $errorStr);
            }

            $form->addInput($rewrite);
        }

        $patterns = [
            '/archives/[cid:digital]/'                                        => _t('默认风格')
                . ' <code>/archives/{cid}/</code>',
            '/archives/[slug].html'                                           => _t('wordpress风格')
                . ' <code>/archives/{slug}.html</code>',
            '/[year:digital:4]/[month:digital:2]/[day:digital:2]/[slug].html' => _t('按日期归档')
                . ' <code>/{year}/{month}/{day}/{slug}.html</code>',
            '/[category]/[slug].html'                                         => _t('按分类归档')
                . ' <code>/{category}/{slug}.html</code>'
        ];

        /** 自定义文章路径 */
        $postPatternValue = $this->options->routingTable['post']['url'];

        /** 增加个性化路径 */
        $customPatternValue = null;
        if ($this->request->is('__Snowfox_form_item_postPattern')) {
            $customPatternValue = $this->request->get('__Snowfox_form_item_postPattern');
            Cookie::delete('__Snowfox_form_item_postPattern');
        } elseif (!isset($patterns[$postPatternValue])) {
            $customPatternValue = $this->decodeRule($postPatternValue);
        }
        $patterns['custom'] = _t('个性化定义') .
            ' <input type="text" class="w-50 text-s mono" name="customPattern" value="' . $customPatternValue . '" />';

        $postPattern = new Form\Element\Radio(
            'postPattern',
            $patterns,
            $postPatternValue,
            _t('自定义文章路径'),
            _t('可用参数: <code>{cid}</code> 日志 ID, <code>{slug}</code> 日志缩略名, <code>{category}</code> 分类, <code>{directory}</code> 多级分类, <code>{year}</code> 年, <code>{month}</code> 月, <code>{day}</code> 日')
            . '<br />' . _t('选择一种合适的文章静态路径风格, 使得你的网站链接更加友好.')
            . '<br />' . _t('一旦你选择了某种链接风格请不要轻易修改它.')
        );
        if ($customPatternValue) {
            $postPattern->value('custom');
        }
        $form->addInput($postPattern->multiMode());

        /** 独立页面后缀名 */
        $pagePattern = new Form\Element\Text(
            'pagePattern',
            null,
            $this->decodeRule($this->options->routingTable['page']['url']),
            _t('独立页面路径'),
            _t('可用参数: <code>{cid}</code> 页面 ID, <code>{slug}</code> 页面缩略名, <code>{directory}</code> 多级页面')
            . '<br />' . _t('请在路径中至少包含上述的一项参数.')
        );
        $pagePattern->input->setAttribute('class', 'mono w-60');
        $form->addInput($pagePattern->addRule([$this, 'checkPagePattern'], _t('独立页面路径中没有包含 {cid} 或者 {slug} ')));

        /** 分类页面 */
        $categoryPattern = new Form\Element\Text(
            'categoryPattern',
            null,
            $this->decodeRule($this->options->routingTable['category']['url']),
            _t('分类路径'),
            _t('可用参数: <code>{mid}</code> 分类 ID, <code>{slug}</code> 分类缩略名, <code>{directory}</code> 多级分类')
            . '<br />' . _t('请在路径中至少包含上述的一项参数.')
        );
        $categoryPattern->input->setAttribute('class', 'mono w-60');
        $form->addInput($categoryPattern->addRule([$this, 'checkCategoryPattern'], _t('分类路径中没有包含 {mid} 或者 {slug} ')));

        /** 提交按钮 */
        $submit = new Form\Element\Submit('submit', null, _t('保存设置'));
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);

        return $form;
    }

    /**
     * 解析自定义的路径
     *
     * @param string $rule 待解码的路径
     * @return string
     */
    protected function decodeRule(string $rule): string
    {
        return preg_replace("/\[([_a-z0-9-]+)[^\]]*\]/i", "{\\1}", $rule);
    }

    /**
     * 检验规则是否冲突
     *
     * @param string $value 路由规则
     * @return boolean
     */
    public function checkRule(string $value): bool
    {
        if ('custom' != $value) {
            return true;
        }

        $routingTable = $this->options->routingTable;
        $currentTable = ['custom' => ['url' => $this->encodeRule($this->request->get('customPattern'))]];
        $parser = new Parser($currentTable);
        $currentTable = $parser->parse();
        $regx = $currentTable['custom']['regx'];

        foreach ($routingTable as $key => $val) {
            if ('post' != $key && 'page' != $key) {
                $pathInfo = preg_replace("/\[([_a-z0-9-]+)[^\]]*\]/i", "{\\1}", $val['url']);
                $pathInfo = str_replace(
                    ['{cid}', '{slug}', '{category}', '{year}', '{month}', '{day}', '{', '}'],
                    ['123', 'hello', 'default', '2008', '08', '08', '', ''],
                    $pathInfo
                );

                if (preg_match($regx, $pathInfo)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * 编码自定义的路径
     *
     * @param string $rule 待编码的路径
     * @return string
     */
    protected function encodeRule(string $rule): string
    {
        return str_replace(
            ['{cid}', '{slug}', '{category}', '{directory}', '{year}', '{month}', '{day}', '{mid}'],
            [
                '[cid:digital]', '[slug]', '[category]', '[directory:split:0]',
                '[year:digital:4]', '[month:digital:2]', '[day:digital:2]', '[mid:digital]'
            ],
            $rule
        );
    }

    /**
     * 绑定动作
     *
     * @access public
     * @return void
     */
    public function action()
    {
        $this->user->pass('administrator');
        $this->security->protect();
        $this->on($this->request->isPost())->updatePermalinkSettings();
        $this->response->redirect($this->options->adminUrl);
    }
}
