<?php
/**
 * Template Name: Default No SideBar
 */
?>
<?php
global $post;
$page_slug = $post->post_name;
?>
<div id="pageheader" class="titleclass">
    <div class="container">
        <?php get_template_part('templates/page', 'header'); ?>
    </div><!--container-->
</div><!--titleclass-->

<div id="content" class="container">
    <div class="row">
        <div class="main <?php echo esc_attr(kadence_main_class()); ?>" id="ktmain" role="main">
            <div class="entry-content" itemprop="mainContentOfPage">
                <?php if (!empty($page_slug)): ?>
                    <?php get_template_part('templates/content', $page_slug); ?>
                <?php else: ?>
                    <?php get_template_part('templates/content', 'page'); ?>
                <?php endif; ?>

            </div>
            <?php global $virtue_premium;
            if (isset($virtue_premium['page_comments']) && $virtue_premium['page_comments'] == '1') {
                comments_template('/templates/comments.php');
            } ?>
        </div><!-- /.main -->
