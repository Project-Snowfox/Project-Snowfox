<?php if(!defined('__Snowfox_ADMIN__')) exit; ?>
<script src="<?php $options->adminStaticUrl('js', 'purify.js'); ?>"></script>
<script>
(function () {
    $(document).ready(function () {
        $('.Snowfox-list-table').tableSelectable({
            checkEl     :   'input[type=checkbox]',
            rowEl       :   'tr',
            selectAllEl :   '.Snowfox-table-select-all',
            actionEl    :   '.dropdown-menu a,button.btn-operate'
        });

        $('.btn-drop').dropdownMenu({
            btnEl       :   '.dropdown-toggle',
            menuEl      :   '.dropdown-menu'
        });
    });
})();
</script>
