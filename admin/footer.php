<?php if(!defined('__Snowfox_ADMIN__')) exit; ?>
<?php \Snowfox\Plugin::factory('admin/footer.php')->call('begin'); ?>
    </body>
</html>
<?php
/** 注册一个结束插件 */
\Snowfox\Plugin::factory('admin/footer.php')->call('end');
