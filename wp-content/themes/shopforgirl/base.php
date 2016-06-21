<?php get_template_part('templates/head'); ?>
<div id="fb-root"></div>
<script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v2.6&appId=508842445974753";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>

  <body <?php body_class(); ?>>
  <!-- Load Facebook SDK for JavaScript -->
    <div id="wrapper" class="container">
    <?php do_action('get_header');
        get_template_part('templates/header');
    ?>
      <div class="wrap contentclass" role="document">

      <?php do_action('kt_afterheader');

          include kadence_template_path(); ?>

          <?php if (kadence_display_sidebar()) : ?>
            <aside class="<?php echo esc_attr(kadence_sidebar_class()); ?> kad-sidebar" role="complementary">
              <div class="sidebar">
                <?php include kadence_sidebar_path(); ?>
              </div><!-- /.sidebar -->
            </aside><!-- /aside -->
          <?php endif; ?>
          </div><!-- /.row-->
        </div><!-- /.content -->
      </div><!-- /.wrap -->
      <?php do_action('get_footer');
      get_template_part('templates/footer'); ?>
    </div><!--Wrapper-->
    <!-- Go to www.addthis.com/dashboard to customize your tools -->
    <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5768597786355569"></script>
  </body>
</html>
