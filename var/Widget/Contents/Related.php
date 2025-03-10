<?php

namespace Widget\Contents;

use Snowfox\Db;
use Snowfox\Db\Exception;
use Widget\Base\Contents;

if (!defined('__Snowfox_ROOT_DIR__')) {
    exit;
}

/**
 * 相关内容组件(根据标签关联)
 *
 * @author qining
 * @category Snowfox
 * @package Widget
 * @copyright Copyright (c) 2008 Snowfox team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Related extends Contents
{
    /**
     * 执行函数,初始化数据
     *
     * @throws Exception
     */
    public function execute()
    {
        $this->parameter->setDefault('limit=5');

        if ($this->parameter->tags) {
            $tagsGroup = implode(',', array_column($this->parameter->tags, 'mid'));
            $this->db->fetchAll($this->select(
                'DISTINCT table.contents.cid',
                'table.contents.title',
                'table.contents.slug',
                'table.contents.created',
                'table.contents.authorId',
                'table.contents.modified',
                'table.contents.type',
                'table.contents.status',
                'table.contents.text',
                'table.contents.commentsNum',
                'table.contents.order',
                'table.contents.template',
                'table.contents.password',
                'table.contents.allowComment',
                'table.contents.allowPing',
                'table.contents.allowFeed'
            )
                ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
                ->where('table.relationships.mid IN (' . $tagsGroup . ')')
                ->where('table.contents.cid <> ?', $this->parameter->cid)
                ->where('table.contents.status = ?', 'publish')
                ->where('table.contents.password IS NULL')
                ->where('table.contents.created < ?', $this->options->time)
                ->where('table.contents.type = ?', $this->parameter->type)
                ->order('table.contents.created', Db::SORT_DESC)
                ->limit($this->parameter->limit), [$this, 'push']);
        }
    }
}
