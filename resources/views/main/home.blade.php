@extends('yivic-base::layouts/wp-main')

@section('content')
    <div class="container">
        <h1 style="text-align: center; margin: 1.2rem auto;"><?php echo 'Home Page'; ?></h1>

        <div id="primary" class="content-area">
            <main id="main" class="site-main" role="main">
				<?php
				if ( have_posts() ) :

				while ( have_posts() ) :
					the_post();
					?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <h2><?php the_title(); ?></h2>
                    <div class="post-content">
                            <?php the_content(); ?>
                    </div>
                </article><!-- #post-## -->
				<?php
				endwhile;

				else :
					echo 'No content';
				endif;
				?>
            </main><!-- #main -->
        </div><!-- #primary -->
    </div>
@endsection
