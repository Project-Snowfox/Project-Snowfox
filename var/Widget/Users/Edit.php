<?php

namespace Widget\Users;

use Snowfox\Common;
use Snowfox\Widget\Exception;
use Snowfox\Widget\Helper\Form;
use Utils\PasswordHash;
use Widget\ActionInterface;
use Widget\Base\Users;
use Widget\Notice;

if (!defined('__Snowfox_ROOT_DIR__')) {
    exit;
}

/**
 * 编辑用户组件
 *
 * @link Snowfox
 * @package Widget
 * @copyright Copyright (c) 2008 Snowfox team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Edit extends Users implements ActionInterface
{
    use EditTrait;

    /**
     * 执行函数
     *
     * @return void
     * @throws Exception|\Snowfox\Db\Exception
     */
    public function execute()
    {
        /** 管理员以上权限 */
        $this->user->pass('administrator');

        /** 更新模式 */
        if (($this->request->is('uid') && 'delete' != $this->request->get('do')) || $this->request->is('do=update')) {
            $this->db->fetchRow($this->select()
                ->where('uid = ?', $this->request->get('uid'))->limit(1), [$this, 'push']);

            if (!$this->have()) {
                throw new Exception(_t('用户不存在'), 404);
            }
        }
    }

    /**
     * 获取菜单标题
     *
     * @return string
     */
    public function getMenuTitle(): string
    {
        return _t('编辑用户 %s', $this->name);
    }

    /**
     * 判断用户是否存在
     *
     * @param integer $uid 用户主键
     * @return boolean
     * @throws \Snowfox\Db\Exception
     */
    public function userExists(int $uid): bool
    {
        $user = $this->db->fetchRow($this->db->select()
            ->from('table.users')
            ->where('uid = ?', $uid)->limit(1));

        return !empty($user);
    }

    /**
     * 增加用户
     *
     * @throws \Snowfox\Db\Exception
     */
    public function insertUser()
    {
        if ($this->form('insert')->validate()) {
            $this->response->goBack();
        }

        $hasher = new PasswordHash(8, true);

        /** 取出数据 */
        $user = $this->request->from('name', 'mail', 'screenName', 'password', 'url', 'group');
        $user['screenName'] = empty($user['screenName']) ? $user['name'] : $user['screenName'];
        $user['password'] = $hasher->hashPassword($user['password']);
        $user['created'] = $this->options->time;

        /** 插入数据 */
        $user['uid'] = $this->insert($user);

        /** 设置高亮 */
        Notice::alloc()->highlight('user-' . $user['uid']);

        /** 提示信息 */
        Notice::alloc()->set(_t('用户 %s 已经被增加', $user['screenName']), 'success');

        /** 转向原页 */
        $this->response->redirect(Common::url('manage-users.php', $this->options->adminUrl));
    }

    /**
     * 生成表单
     *
     * @access public
     * @param string|null $action 表单动作
     * @return Form
     */
    public function form(?string $action = null): Form
    {
        /** 构建表格 */
        $form = new Form($this->security->getIndex('/action/users-edit'), Form::POST_METHOD);

        /** 用户名称 */
        $name = new Form\Element\Text('name', null, null, _t('用户名') . ' *', _t('此用户名将作为用户登录时所用的名称.')
            . '<br />' . _t('请不要与系统中现有的用户名重复.'));
        $form->addInput($name);

        /** 电子邮箱地址 */
        $mail = new Form\Element\Text('mail', null, null, _t('邮件地址') . ' *', _t('电子邮箱地址将作为此用户的主要联系方式.')
            . '<br />' . _t('请不要与系统中现有的电子邮箱地址重复.'));
        $form->addInput($mail);

        /** 用户昵称 */
        $screenName = new Form\Element\Text('screenName', null, null, _t('用户昵称'), _t('用户昵称可以与用户名不同, 用于前台显示.')
            . '<br />' . _t('如果你将此项留空, 将默认使用用户名.'));
        $form->addInput($screenName);

        /** 用户密码 */
        $password = new Form\Element\Password('password', null, null, _t('用户密码'), _t('为此用户分配一个密码.')
            . '<br />' . _t('建议使用特殊字符与字母、数字的混编样式,以增加系统安全性.'));
        $password->input->setAttribute('class', 'w-60');
        $form->addInput($password);

        /** 用户密码确认 */
        $confirm = new Form\Element\Password('confirm', null, null, _t('用户密码确认'), _t('请确认你的密码, 与上面输入的密码保持一致.'));
        $confirm->input->setAttribute('class', 'w-60');
        $form->addInput($confirm);

        /** 个人主页地址 */
        $url = new Form\Element\Text('url', null, null, _t('个人主页地址'), _t('此用户的个人主页地址, 请用 <code>https://</code> 开头.'));
        $form->addInput($url);

        /** 用户组 */
        $group = new Form\Element\Select(
            'group',
            [
                'subscriber'  => _t('关注者'),
                'contributor' => _t('贡献者'), 'editor' => _t('编辑'), 'administrator' => _t('管理员')
            ],
            null,
            _t('用户组'),
            _t('不同的用户组拥有不同的权限.') . '<br />' . _t('具体的权限分配表请<a href="https://docs.typecho.org/develop/acl">参考这里</a>.')
        );
        $form->addInput($group);

        /** 用户动作 */
        $do = new Form\Element\Hidden('do');
        $form->addInput($do);

        /** 用户主键 */
        $uid = new Form\Element\Hidden('uid');
        $form->addInput($uid);

        /** 提交按钮 */
        $submit = new Form\Element\Submit();
        $submit->input->setAttribute('class', 'btn primary');
        $form->addItem($submit);

        if ($this->request->is('uid')) {
            $submit->value(_t('编辑用户'));
            $name->value($this->name);
            $screenName->value($this->screenName);
            $url->value($this->url);
            $mail->value($this->mail);
            $group->value($this->group);
            $do->value('update');
            $uid->value($this->uid);
            $_action = 'update';
        } else {
            $submit->value(_t('增加用户'));
            $do->value('insert');
            $_action = 'insert';
        }

        if (empty($action)) {
            $action = $_action;
        }

        /** 给表单增加规则 */
        if ('insert' == $action || 'update' == $action) {
            $screenName->addRule([$this, 'screenNameExists'], _t('昵称已经存在'));
            $screenName->addRule('xssCheck', _t('请不要在昵称中使用特殊字符'));
            $url->addRule('url', _t('个人主页地址格式错误'));
            $mail->addRule('required', _t('必须填写电子邮箱'));
            $mail->addRule([$this, 'mailExists'], _t('电子邮箱地址已经存在'));
            $mail->addRule('email', _t('电子邮箱格式错误'));
            $password->addRule('minLength', _t('为了保证账户安全, 请输入至少六位的密码'), 6);
            $confirm->addRule('confirm', _t('两次输入的密码不一致'), 'password');
        }

        if ('insert' == $action) {
            $name->addRule('required', _t('必须填写用户名称'));
            $name->addRule('xssCheck', _t('请不要在用户名中使用特殊字符'));
            $name->addRule([$this, 'nameExists'], _t('用户名已经存在'));
            $password->label(_t('用户密码') . ' *');
            $confirm->label(_t('用户密码确认') . ' *');
            $password->addRule('required', _t('必须填写密码'));
        }

        if ('update' == $action) {
            $name->input->setAttribute('disabled', 'disabled');
            $uid->addRule('required', _t('用户主键不存在'));
            $uid->addRule([$this, 'userExists'], _t('用户不存在'));
        }

        return $form;
    }

    /**
     * 更新用户
     *
     * @throws \Snowfox\Db\Exception
     */
    public function updateUser()
    {
        if ($this->form('update')->validate()) {
            $this->response->goBack();
        }

        /** 取出数据 */
        $user = $this->request->from('mail', 'screenName', 'password', 'url', 'group');
        $user['screenName'] = empty($user['screenName']) ? $user['name'] : $user['screenName'];
        if (empty($user['password'])) {
            unset($user['password']);
        } else {
            $hasher = new PasswordHash(8, true);
            $user['password'] = $hasher->hashPassword($user['password']);
        }

        /** 更新数据 */
        $this->update($user, $this->db->sql()->where('uid = ?', $this->request->get('uid')));

        /** 设置高亮 */
        Notice::alloc()->highlight('user-' . $this->request->get('uid'));

        /** 提示信息 */
        Notice::alloc()->set(_t('用户 %s 已经被更新', $user['screenName']), 'success');

        /** 转向原页 */
        $this->response->redirect(Common::url('manage-users.php?' .
            $this->getPageOffsetQuery($this->request->get('uid')), $this->options->adminUrl));
    }

    /**
     * 获取页面偏移的URL Query
     *
     * @param integer $uid 用户id
     * @return string
     * @throws \Snowfox\Db\Exception
     */
    protected function getPageOffsetQuery(int $uid): string
    {
        return 'page=' . $this->getPageOffset('uid', $uid);
    }

    /**
     * 删除用户
     *
     * @throws \Snowfox\Db\Exception
     */
    public function deleteUser()
    {
        $users = $this->request->filter('int')->getArray('uid');
        $masterUserId = $this->db->fetchObject($this->db->select(['MIN(uid)' => 'num'])->from('table.users'))->num;
        $deleteCount = 0;

        foreach ($users as $user) {
            if ($masterUserId == $user || $user == $this->user->uid) {
                continue;
            }

            if ($this->delete($this->db->sql()->where('uid = ?', $user))) {
                $deleteCount++;
            }
        }

        /** 提示信息 */
        Notice::alloc()->set(
            $deleteCount > 0 ? _t('用户已经删除') : _t('没有用户被删除'),
            $deleteCount > 0 ? 'success' : 'notice'
        );

        /** 转向原页 */
        $this->response->redirect(Common::url('manage-users.php', $this->options->adminUrl));
    }

    /**
     * 入口函数
     *
     * @access public
     * @return void
     */
    public function action()
    {
        $this->user->pass('administrator');
        $this->security->protect();
        $this->on($this->request->is('do=insert'))->insertUser();
        $this->on($this->request->is('do=update'))->updateUser();
        $this->on($this->request->is('do=delete'))->deleteUser();
        $this->response->redirect($this->options->adminUrl);
    }
}
