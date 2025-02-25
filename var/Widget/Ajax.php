<?php

namespace Widget;

use Snowfox\Http\Client;
use Snowfox\Widget\Exception;
use Widget\Base\Options as BaseOptions;

if (!defined('__Snowfox_ROOT_DIR__')) {
    exit;
}

/**
 * 异步调用组件
 *
 * @author qining
 * @category Snowfox
 * @package Widget
 */
class Ajax extends BaseOptions implements ActionInterface
{
    /**
     * 针对rewrite验证的请求返回
     *
     * @access public
     * @return void
     */
    public function remoteCallback()
    {
        if ($this->options->generator == $this->request->getAgent()) {
            echo 'OK';
        }
    }

    /**
     * 获取最新版本
     *
     * @throws Exception|\Snowfox\Db\Exception
     */
    public function checkVersion()
    {
        $this->user->pass('editor');
        $client = Client::get();
        $result = ['available' => 0];
        if ($client) {
            $client->setHeader('User-Agent', $this->options->generator)
                ->setTimeout(10);

            try {
                $client->send('https://typecho.org/version.json');

                /** 匹配内容体 */
                $response = $client->getResponseBody();
                $json = json_decode($response, true);

                if (!empty($json)) {
                    $version = $this->options->version;

                    if (
                        isset($json['release'])
                        && preg_match("/^[0-9.]+$/", $json['release'])
                        && version_compare($json['release'], $version, '>')
                    ) {
                        $result = [
                            'available' => 1,
                            'latest'    => $json['release'],
                            'current'   => $version,
                            'link'      => 'https://typecho.org/download'
                        ];
                    }
                }
            } catch (\Exception $e) {
                // do nothing
            }
        }

        $this->response->throwJson($result);
    }

    /**
     * 远程请求代理
     *
     * @throws Exception
     * @throws Client\Exception|\Snowfox\Db\Exception
     */
    public function feed()
    {
        $this->user->pass('subscriber');
        $client = Client::get();
        $data = [];
        if ($client) {
            $client->setHeader('User-Agent', $this->options->generator)
                ->setTimeout(10)
                ->send('https://typecho.org/feed/');

            /** 匹配内容体 */
            $response = $client->getResponseBody();
            preg_match_all(
                "/<item>\s*<title>([^>]*)<\/title>\s*<link>([^>]*)<\/link>\s*<guid>[^>]*<\/guid>\s*<pubDate>([^>]*)<\/pubDate>/i",
                $response,
                $matches
            );

            if ($matches) {
                foreach ($matches[0] as $key => $val) {
                    $data[] = [
                        'title' => $matches[1][$key],
                        'link'  => $matches[2][$key],
                        'date'  => date('n.j', strtotime($matches[3][$key]))
                    ];

                    if ($key > 8) {
                        break;
                    }
                }
            }
        }

        $this->response->throwJson($data);
    }

    /**
     * 自定义编辑器大小
     *
     * @throws \Snowfox\Db\Exception|Exception
     */
    public function editorResize()
    {
        $this->user->pass('contributor');
        $size = $this->request->filter('int')->get('size');

        if (
            $this->db->fetchObject($this->db->select(['COUNT(*)' => 'num'])
                ->from('table.options')->where('name = ? AND user = ?', 'editorSize', $this->user->uid))->num > 0
        ) {
            parent::update(
                ['value' => $size],
                $this->db->sql()->where('name = ? AND user = ?', 'editorSize', $this->user->uid)
            );
        } else {
            parent::insert([
                'name'  => 'editorSize',
                'value' => $size,
                'user'  => $this->user->uid
            ]);
        }
    }

    /**
     * 异步请求入口
     *
     * @access public
     * @return void
     */
    public function action()
    {
        if (!$this->request->isAjax()) {
            $this->response->goBack();
        }

        $this->on($this->request->is('do=remoteCallback'))->remoteCallback();
        $this->on($this->request->is('do=feed'))->feed();
        $this->on($this->request->is('do=checkVersion'))->checkVersion();
        $this->on($this->request->is('do=editorResize'))->editorResize();
    }
}
