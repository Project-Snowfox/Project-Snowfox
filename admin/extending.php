<?php

include 'common.php';

$panel = $request->get('panel');
$panelTable = $options->panelTable;

if (!isset($panelTable['file']) || !in_array(urlencode($panel), $panelTable['file'])) {
    throw new \Snowfox\Plugin\Exception(_t('页面不存在'), 404);
}

[$pluginName, $file] = explode('/', trim($panel, '/'), 2);

require_once $options->pluginDir($pluginName) . '/' . $file;
