<?php if(!defined('__Snowfox_ADMIN__')) exit; ?>
<div class="Snowfox-page-title">
    <h2><?php echo $menu->title; ?><?php 
    if (!empty($menu->addLink)) {
        echo "<a href=\"{$menu->addLink}\">" . _t("新增") . "</a>";
    }
    ?></h2>
</div>
