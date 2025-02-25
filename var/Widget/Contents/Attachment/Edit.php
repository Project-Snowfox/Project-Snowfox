<?php

namespace Widget\Contents\Attachment;

use Snowfox\Common;
use Snowfox\Widget\Exception;
use Snowfox\Widget\Helper\Form;
use Snowfox\Widget\Helper\Layout;
use Widget\ActionInterface;
use Widget\Base\Contents;
use Widget\Contents\PrepareEditTrait;
use Widget\Notice;
use Widget\Upload;

if (!defined('__Snowfox_ROOT_DIR__')) {
    exit;
}

/**
 * 编辑文章组件
 *
 * @author qining
 * @category Snowfox
 * @package Widget
 * @copyright Copyright (c) 2008 Snowfox team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Edit extends Contents implements ActionInterface
{
    use PrepareEditTrait;

    /**
     * 执行函数
     *
     * @throws Exception|\Snowfox\Db\Exception
     */
    public function execute()
    {
        /** 必须为贡献者以上权限 */
        $this->user->pass('contributor');
    }

    /**
     * 判断文件名转换到缩略名后是否合法
     *
     * @param string $name 文件名
     * @return boolean
     */
    public function nameToSlug(string $name): bool
    {
        if (empty($this->request->slug)) {
            $slug = Common::slugName($name);
            if (empty($slug) || !$this->slugExists($name)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 判断文件缩略名是否存在
     *
     * @param string $slug 缩略名
     * @return boolean
     * @throws \Snowfox\Db\Exception
     */
    public function slugExists(string $slug): bool
    {
        $select = $this->db->select()
            ->from('table.contents')
            ->where('type = ?', 'attachment')
            ->where('slug = ?', Common::slugName($slug))
            ->limit(1);

        if ($this->request->is('cid')) {
            $select->where('cid <> ?', $this->request->get('cid'));
        }

        $attachment = $this->db->fetchRow($select);
        return !$attachment;
    }

    /**
     * 更新文件
     *
     * @throws \Snowfox\Db\Exception
     * @throws Exception
     */
    public function updateAttachment()
    {
        if ($this->form()->validate()) {
            $this->response->goBack();
        }

        /** 取出数据 */
        $input = $this->request->from('name', 'slug', 'description');
        $input['slug'] = Common::slugName(Common::strBy($input['slug'] ?? null, $input['name']));

        $attachment['title'] = $input['name'];
        $attachment['slug'] = $input['slug'];

        $content = $this->attachment->toArray();
        $content['description'] = $input['description'];

        $attachment['text'] = json_encode($content);
        $cid = $this->request->filter('int')->get('cid');

        /** 更新数据 */
        $updateRows = $this->update($attachment, $this->db->sql()->where('cid = ?', $cid));

        if ($updateRows > 0) {
            $this->db->fetchRow($this->select()
                ->where('table.contents.type = ?', 'attachment')
                ->where('table.contents.cid = ?', $cid)
                ->limit(1), [$this, 'push']);

            /** 设置高亮 */
            Notice::alloc()->highlight($this->theId);

            /** 提示信息 */
            Notice::alloc()->set('publish' == $this->status ?
                _t('文件 <a href="%s">%s</a> 已经被更新', $this->permalink, $this->title) :
                _t('未归档文件 %s 已经被更新', $this->title), 'success');
        }

        /** 转向原页 */
        $this->response->redirect(Common::url('manage-medias.php?' .
            $this->getPageOffsetQuery($cid, $this->status), $this->options->adminUrl));
    }

    /**
     * 生成表单
     *
     * @return Form
     */
    public function form(): Form
    {
        /** 构建表格 */
        $form = new Form($this->security->getIndex('/action/contents-attachment-edit'), Form::POST_METHOD);

        /** 文件名称 */
        $name = new Form\Element\Text('name', null, $this->title, _t('标题') . ' *');
        $form->addInput($name);

        /** 文件缩略名 */
        $slug = new Form\Element\Text(
            'slug',
            null,
            $this->slug,
            _t('缩略名'),
            _t('文件缩略名用于创建友好的链接形式,建议使用字母,数字,下划线和横杠.')
        );
        $form->addInput($slug);

        /** 文件描述 */
        $description = new Form\Element\Textarea(
            'description',
            null,
            $this->attachment->description,
            _t('描述'),
            _t('此文字用于描述文件,在有的主题中它会被显示.')
        );
        $form->addInput($description);

        /** 分类动作 */
        $do = new Form\Element\Hidden('do', null, 'update');
        $form->addInput($do);

        /** 分类主键 */
        $cid = new Form\Element\Hidden('cid', null, $this->cid);
        $form->addInput($cid);

        /** 提交按钮 */
        $submit = new Form\Element\Submit(null, null, _t('提交修改'));
        $submit->input->setAttribute('class', 'btn primary');
        $delete = new Layout('a', [
            'href'  => $this->security->getIndex('/action/contents-attachment-edit?do=delete&cid=' . $this->cid),
            'class' => 'operate-delete',
            'lang'  => _t('你确认删除文件 %s 吗?', $this->attachment->name)
        ]);
        $submit->container($delete->html(_t('删除文件')));
        $form->addItem($submit);

        $name->addRule('required', _t('必须填写文件标题'));
        $name->addRule([$this, 'nameToSlug'], _t('文件标题无法被转换为缩略名'));
        $slug->addRule([$this, 'slugExists'], _t('缩略名已经存在'));

        return $form;
    }

    /**
     * 获取页面偏移的URL Query
     *
     * @param integer $cid 文件id
     * @param string|null $status 状态
     * @return string
     * @throws \Snowfox\Db\Exception|Exception
     */
    protected function getPageOffsetQuery(int $cid, string $status = null): string
    {
        return 'page=' . $this->getPageOffset(
            'cid',
            $cid,
            'attachment',
            $status,
            $this->user->pass('editor', true) ? 0 : $this->user->uid
        );
    }

    /**
     * 删除文章
     *
     * @throws \Snowfox\Db\Exception
     */
    public function deleteAttachment()
    {
        $posts = $this->request->filter('int')->getArray('cid');
        $deleteCount = 0;

        $this->deleteByIds($posts, $deleteCount);

        if ($this->request->isAjax()) {
            $this->response->throwJson($deleteCount > 0 ? ['code' => 200, 'message' => _t('文件已经被删除')]
                : ['code' => 500, 'message' => _t('没有文件被删除')]);
        } else {
            /** 设置提示信息 */
            Notice::alloc()
                ->set(
                    $deleteCount > 0 ? _t('文件已经被删除') : _t('没有文件被删除'),
                    $deleteCount > 0 ? 'success' : 'notice'
                );

            /** 返回原网页 */
            $this->response->redirect(Common::url('manage-medias.php', $this->options->adminUrl));
        }
    }

    /**
     * clearAttachment
     *
     * @access public
     * @return void
     * @throws \Snowfox\Db\Exception
     */
    public function clearAttachment()
    {
        $page = 1;
        $deleteCount = 0;

        do {
            $posts = array_column($this->db->fetchAll($this->db->select('cid')
                ->from('table.contents')
                ->where('type = ? AND parent = ?', 'attachment', 0)
                ->page($page, 100)), 'cid');
            $page++;

            $this->deleteByIds($posts, $deleteCount);
        } while (count($posts) == 100);

        /** 设置提示信息 */
        Notice::alloc()->set(
            $deleteCount > 0 ? _t('未归档文件已经被清理') : _t('没有未归档文件被清理'),
            $deleteCount > 0 ? 'success' : 'notice'
        );

        /** 返回原网页 */
        $this->response->redirect(Common::url('manage-medias.php', $this->options->adminUrl));
    }

    /**
     * @return $this
     * @throws Exception
     * @throws \Snowfox\Db\Exception
     */
    public function prepare(): self
    {
        return $this->prepareEdit('attachment', false, _t('文件不存在'));
    }

    /**
     * 绑定动作
     *
     * @access public
     * @return void
     */
    public function action()
    {
        $this->security->protect();
        $this->on($this->request->is('do=delete'))->deleteAttachment();
        $this->on($this->request->is('do=update'))
            ->prepare()->updateAttachment();
        $this->on($this->request->is('do=clear'))->clearAttachment();
        $this->response->redirect($this->options->adminUrl);
    }

    /**
     * @param array $posts
     * @param int $deleteCount
     * @return void
     */
    protected function deleteByIds(array $posts, int &$deleteCount): void
    {
        foreach ($posts as $post) {
            // 删除插件接口
            self::pluginHandle()->call('delete', $post, $this);

            $condition = $this->db->sql()->where('cid = ?', $post);
            $row = $this->db->fetchRow($this->select()
                ->where('table.contents.type = ?', 'attachment')
                ->where('table.contents.cid = ?', $post)
                ->limit(1), [$this, 'push']);

            if ($this->isWriteable(clone $condition) && $this->delete($condition)) {
                /** 删除文件 */
                Upload::deleteHandle($this->toColumn(['cid', 'attachment', 'parent']));

                /** 删除评论 */
                $this->db->query($this->db->delete('table.comments')
                    ->where('cid = ?', $post));

                // 完成删除插件接口
                self::pluginHandle()->call('finishDelete', $post, $this);

                $deleteCount++;
            }

            unset($condition);
        }
    }
}
