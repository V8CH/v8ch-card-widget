The default layout template for the Rice Paper card widget uses a simple php template as for any other template partial in WordPress. The template may be overridden simply by creating a new template named `widget-v8ch-card.php` alongside the `style.css` file in the (child) theme. Check the comments on the template for notes on the included query and template variables used to build the widget HTML output.

    <?php echo $before_widget . PHP_EOL; ?>
      <div class="v8ch-card-wrap">
        <?php if ( ! empty( $title ) ) : ?>
          <?php echo $before_title . $title . $after_title . PHP_EOL; ?>
        <?php endif; ?>
        <div class="v8ch-card-img">
          <?php echo $this->get_image_html( $instance, true ) . PHP_EOL; ?>
        </div>
        <?php if ( ! empty( $description ) ) : ?>
          <div class="v8ch-card-blurb">
            <!-- wpautop() blurb text output -->
            <?php echo wpautop( $description ); ?>
            <!-- /wpautop() blurb text output -->
            <?php if ( ! empty( $link ) ) : ?>
              <div class="v8ch-card-blurb-footer">
                <a href="<?php echo $link; ?>" class="more-link"><span class="badge"><span class="fa fa-chevron-right"></span><span class="fa fa-chevron-right"></span></span>Read more</a>
              </div> <!-- /.v8ch-card-blurb-footer -->
            <?php endif; ?>
          </div> <!-- /.v8ch-card-blurb -->
        <?php endif; ?>
        <?php if ( $tax_name != '' && $tax_slug != '' ) :
          $the_query = new WP_Query( array(
            'posts_type'     => $post_type,
            'tax_query'      => array(
              array(
                'taxonomy'         => $tax_name,
                'field'            => 'slug',
                'terms'            => $tax_slug,
                'include_children' => true,
              ),
            ),
            'posts_per_page' => 3,
          ) ); ?>
          <?php if ( $the_query->have_posts() ) : ?>
            <div class="post-excerpts-list">
              <ul class="post-excerpts">
                <?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
                  <li>
                    <h4 class="<?php echo get_post_type(); ?>-title"><a
                        href="<?php echo get_the_permalink(); ?>"
                        rel="bookmark"><?php echo get_the_title(); ?></a></h4>
                    <div class="<?php echo get_post_type(); ?>-excerpt">
                      <?php echo get_the_excerpt(); ?>
                    </div>
                    <div class="post-excerpts-footer type-<?php echo get_post_type(); ?>">
                      <a href="<?php echo get_the_permalink(); ?>" rel="bookmark" class="more-link"><span class="badge"><span class="fa fa-chevron-right"></span><span class="fa fa-chevron-right"></span></span>Read more</a>
                    </div>
                  </li>
                <?php endwhile; ?>
              </ul>
            </div> <!-- /.post-excerpts-list --
          <?php else : ?>
            <!-- no post excerpts found -->
          <?php endif; ?>
          <?php
          // Restore post data
          wp_reset_postdata();
          ?>
        <?php endif; ?>
      </div> <!-- /.v8ch-card-wrap -->
    <?php echo $after_widget; ?>