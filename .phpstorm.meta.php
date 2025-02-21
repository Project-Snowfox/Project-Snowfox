<?php

namespace PHPSTORM_META {
    override(\Snowfox\Widget::widget(0), map([
        '' => '@'
    ]));

    exitPoint(\Snowfox\Widget\Response::redirect());
    exitPoint(\Snowfox\Widget\Response::throwContent());
    exitPoint(\Snowfox\Widget\Response::throwFile());
    exitPoint(\Snowfox\Widget\Response::throwJson());
    exitPoint(\Snowfox\Widget\Response::throwXml());
    exitPoint(\Snowfox\Widget\Response::goBack());

    override(\Widget\Options::__get(0), map([
        'feedUrl'               =>  string,
        'feedRssUrl'            =>  string,
        'feedAtomUrl'           =>  string,
        'commentsFeedUrl'       =>  string,
        'commentsFeedRssUrl'    =>  string,
        'commentsFeedAtomUrl'   =>  string,
        'xmlRpcUrl'             =>  string,
        'index'                 =>  string,
        'siteUrl'               =>  string,
        'routingTable'          =>  \ArrayObject::class,
        'rootUrl'               =>  string,
        'themeUrl'              =>  string,
        'pluginUrl'             =>  string,
        'adminUrl'              =>  string,
        'loginUrl'              =>  string,
        'loginAction'           =>  string,
        'registerUrl'           =>  string,
        'registerAction'        =>  string,
        'profileUrl'            =>  string,
        'logoutUrl'             =>  string,
        'serverTimezone'        =>  int,
        'contentType'           =>  string,
        'software'              =>  string,
        'version'               =>  string,
        'markdown'              =>  int,
        'allowedAttachmentTypes'=>  \ArrayObject::class
    ]));

    override(\Snowfox\Widget::__get(0), map([
        'sequence' => int,
        'length'   => int
    ]));
}