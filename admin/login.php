<?php
include 'common.php';

if ($user->hasLogin()) {
    $response->redirect($options->adminUrl);
}
$rememberName = htmlspecialchars(\Snowfox\Cookie::get('__Snowfox_remember_name', ''));
\Snowfox\Cookie::delete('__Snowfox_remember_name');

$bodyClass = 'body-100';

include 'header.php';
?>
<div class="Snowfox-login-wrap">
    <div class="Snowfox-login">
        <h1><a href="https://typecho.org" class="i-logo">Snowfox</a></h1>
        <form action="<?php $options->loginAction(); ?>" method="post" name="login" role="form">
            <p>
                <label for="name" class="sr-only"><?php _e('用户名或邮箱'); ?></label>
                <input type="text" id="name" name="name" value="<?php echo $rememberName; ?>" placeholder="<?php _e('用户名或邮箱'); ?>" class="text-l w-100" autofocus />
            </p>
            <p>
                <label for="password" class="sr-only"><?php _e('密码'); ?></label>
                <input type="password" id="password" name="password" class="text-l w-100" placeholder="<?php _e('密码'); ?>" required />
            </p>
            <p class="submit">
                <button type="submit" class="btn btn-l w-100 primary"><?php _e('登录'); ?></button>
                <input type="hidden" name="referer" value="<?php echo $request->filter('html')->get('referer'); ?>" />
            </p>
            <p>
                <label for="remember">
                    <input<?php if(\Snowfox\Cookie::get('__Snowfox_remember_remember')): ?> checked<?php endif; ?> type="checkbox" name="remember" class="checkbox" value="1" id="remember" /> <?php _e('下次自动登录'); ?>
                </label>
            </p>
        </form>
        
        <p class="more-link">
            <a href="<?php $options->siteUrl(); ?>"><?php _e('返回首页'); ?></a>
            <?php if($options->allowRegister): ?>
            &bull;
            <a href="<?php $options->registerUrl(); ?>"><?php _e('用户注册'); ?></a>
            <?php endif; ?>
        </p>
    </div>
</div>
<?php 
include 'common-js.php';
?>
<script>
$(document).ready(function () {
    $('#name').focus();
});
</script>
<?php
include 'footer.php';
?>
