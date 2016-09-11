<?php get_header(); ?>
<div class="container">
    <?php
    $args = array(
        'redirect' => '/',
        'form_id' => 'spg-login-form',
        'form_class' => 'form-horizontal',
        'label_username' => __('Tên tài khoản'),
        'label_password' => __('Mật khẩu'),
        'label_remember' => __('Ghi nhớ'),
        'label_log_in' => __('Đăng nhập'),
    );

    ?>
    <div class="col-sm-12 col-md-12">
        <?php if (!is_user_logged_in()): ?>
            <div class="page-header">
                <h1 class="entry-title" itemprop="name">Đăng nhập</h1>
            </div>
            <div class="login-wrap">
                <?php
                $login = (isset($_GET['login'])) ? $_GET['login'] : 0;
                if ($login === "failed") {
                    echo '<p class="error"><strong>Lỗi:</strong> Sai username hoặc mật khẩu.</p>';
                } elseif ($login === "empty") {
                    echo '<p class="error"><strong>Lỗi:</strong> Username và mật khẩu không thể bỏ trống.</p>';
                } elseif ($login === "false") {
                    echo '<p class="error"><strong>Lỗi:</strong> Bạn đã thoát ra.</p>';
                }
                ?>
                <?php wp_login_form($args); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
