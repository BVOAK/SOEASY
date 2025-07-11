<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS

$css .= '
.uicore-single-header h1.entry-title{
    --uicore-typography--h1-f:' . $this->fam($json_settings['blog_h1']['f']) . ';
    --uicore-typography--h1-w:' . $this->wt($json_settings['blog_h1']) . ';
    --uicore-typography--h1-h:' . $json_settings['blog_h1']['h'] . ';
    --uicore-typography--h1-ls:' . $json_settings['blog_h1']['ls'] . 'em;
    --uicore-typography--h1-t:' . $json_settings['blog_h1']['t'] . ';
    --uicore-typography--h1-st:' . $this->st($json_settings['blog_h1']) . ';
    --uicore-typography--h1-c:' . $this->color($json_settings['blog_h1']['c']) . ';
    --uicore-typography--h1-s:' . $json_settings['blog_h1']['s']['d'] . 'px;
}
.single-post .elementor-widget-uicore-the-content,
.uicore-blog .uicore-post-content:not(.uicore-archive) .entry-content {
    --uicore-typography--h1-f:' . $this->fam($json_settings['blog_h1']['f']) . ';
    --uicore-typography--h1-w:' . $this->wt($json_settings['blog_h1']) . ';
    --uicore-typography--h1-h:' . $json_settings['blog_h1']['h'] . ';
    --uicore-typography--h1-ls:' . $json_settings['blog_h1']['ls'] . 'em;
    --uicore-typography--h1-t:' . $json_settings['blog_h1']['t'] . ';
    --uicore-typography--h1-st:' . $this->st($json_settings['blog_h1']) . ';
    --uicore-typography--h1-c:' . $this->color($json_settings['blog_h1']['c']) . ';
    --uicore-typography--h1-s:' . $json_settings['blog_h1']['s']['d'] . 'px;

    --uicore-typography--h2-f:' . $this->fam($json_settings['blog_h2']['f']) . ';
    --uicore-typography--h2-w:' . $this->wt($json_settings['blog_h2']) . ';
    --uicore-typography--h2-h:' . $json_settings['blog_h2']['h'] . ';
    --uicore-typography--h2-ls:' . $json_settings['blog_h2']['ls'] . 'em;
    --uicore-typography--h2-t:' . $json_settings['blog_h2']['t'] . ';
    --uicore-typography--h2-st:' . $this->st($json_settings['blog_h2']) . ';
    --uicore-typography--h2-c:' . $this->color($json_settings['blog_h2']['c']) . ';
    --uicore-typography--h2-s:' . $json_settings['blog_h2']['s']['d'] . 'px;

    --uicore-typography--h3-f:' . $this->fam($json_settings['blog_h3']['f']) . ';
    --uicore-typography--h3-w:' . $this->wt($json_settings['blog_h3']) . ';
    --uicore-typography--h3-h:' . $json_settings['blog_h3']['h'] . ';
    --uicore-typography--h3-ls:' . $json_settings['blog_h3']['ls'] . 'em;
    --uicore-typography--h3-t:' . $json_settings['blog_h3']['t'] . ';
    --uicore-typography--h3-st:' . $this->st($json_settings['blog_h3']) . ';
    --uicore-typography--h3-c:' . $this->color($json_settings['blog_h3']['c']) . ';
    --uicore-typography--h3-s:' . $json_settings['blog_h3']['s']['d'] . 'px;

    --uicore-typography--h4-f:' . $this->fam($json_settings['blog_h4']['f']) . ';
    --uicore-typography--h4-w:' . $this->wt($json_settings['blog_h4']) . ';
    --uicore-typography--h4-h:' . $json_settings['blog_h4']['h'] . ';
    --uicore-typography--h4-ls:' . $json_settings['blog_h4']['ls'] . 'em;
    --uicore-typography--h4-t:' . $json_settings['blog_h4']['t'] . ';
    --uicore-typography--h4-st:' . $this->st($json_settings['blog_h4']) . ';
    --uicore-typography--h4-c:' . $this->color($json_settings['blog_h4']['c']) . ';
    --uicore-typography--h4-s:' . $json_settings['blog_h4']['s']['d'] . 'px;

    --uicore-typography--h5-f:' . $this->fam($json_settings['blog_h5']['f']) . ';
    --uicore-typography--h5-w:' . $this->wt($json_settings['blog_h5']) . ';
    --uicore-typography--h5-h:' . $json_settings['blog_h5']['h'] . ';
    --uicore-typography--h5-ls:' . $json_settings['blog_h5']['ls'] . 'em;
    --uicore-typography--h5-t:' . $json_settings['blog_h5']['t'] . ';
    --uicore-typography--h5-st:' . $this->st($json_settings['blog_h5']) . ';
    --uicore-typography--h5-c:' . $this->color($json_settings['blog_h5']['c']) . ';
    --uicore-typography--h5-s:' . $json_settings['blog_h5']['s']['d'] . 'px;

    --uicore-typography--h6-f:' . $this->fam($json_settings['blog_h6']['f']) . ';
    --uicore-typography--h6-w:' . $this->wt($json_settings['blog_h6']) . ';
    --uicore-typography--h6-h:' . $json_settings['blog_h6']['h'] . ';
    --uicore-typography--h6-ls:' . $json_settings['blog_h6']['ls'] . 'em;
    --uicore-typography--h6-t:' . $json_settings['blog_h6']['t'] . ';
    --uicore-typography--h6-st:' . $this->st($json_settings['blog_h6']) . ';
    --uicore-typography--h6-c:' . $this->color($json_settings['blog_h6']['c']) . ';
    --uicore-typography--h6-s:' . $json_settings['blog_h6']['s']['d'] . 'px;

    --uicore-typography--p-f:' . $this->fam($json_settings['blog_p']['f']) . ';
    --uicore-typography--p-w:' . $this->wt($json_settings['blog_p']) . ';
    --uicore-typography--p-h:' . $json_settings['blog_p']['h'] . ';
    --uicore-typography--p-ls:' . $json_settings['blog_p']['ls'] . 'em;
    --uicore-typography--p-t:' . $json_settings['blog_p']['t'] . ';
    --uicore-typography--p-st:' . $this->st($json_settings['p']) . ';
    --uicore-typography--p-c:' . $this->color($json_settings['blog_p']['c']) . ';
    --uicore-typography--p-s:' . $json_settings['blog_p']['s']['d'] . 'px;
}
.single .elementor-widget-uicore-the-content a,
.blog-fonts a{
    color: ' . $this->color($json_settings['blog_link_color']['m']) . ';
}
.single .elementor-widget-uicore-the-content a:hover, 
.blog-fonts a:hover{
    color: ' . $this->color($json_settings['blog_link_color']['h']) . ';
}

.uicore-blog-grid {
    --uicore-typography--blog_title-f:' . $this->fam($json_settings['blog_title']['f']) . ';
    --uicore-typography--blog_title-w:' . $this->wt($json_settings['blog_title']) . ';
    --uicore-typography--blog_title-h:' . $json_settings['blog_title']['h'] . ';
    --uicore-typography--blog_title-ls:' . $json_settings['blog_title']['ls'] . 'em;
    --uicore-typography--blog_title-t:' . $json_settings['blog_title']['t'] . ';
    --uicore-typography--blog_title-st:' . $this->st($json_settings['blog_title']) . ';
    --uicore-typography--blog_title-c:' . $this->color($json_settings['blog_title']['c']) . ';
    --uicore-typography--blog_title-s:' . $json_settings['blog_title']['s']['d'] . 'px;

    --uicore-typography--blog_ex-f:' . $this->fam($json_settings['blog_ex']['f']) . ';
    --uicore-typography--blog_ex-w:' . $this->wt($json_settings['blog_ex']) . ';
    --uicore-typography--blog_ex-h:' . $json_settings['blog_ex']['h'] . ';
    --uicore-typography--blog_ex-ls:' . $json_settings['blog_ex']['ls'] . 'em;
    --uicore-typography--blog_ex-t:' . $json_settings['blog_ex']['t'] . ';
    --uicore-typography--blog_ex-st:' . $this->st($json_settings['blog_ex']) . ';
    --uicore-typography--blog_ex-c:' . $this->color($json_settings['blog_ex']['c']) . ';
    --uicore-typography--blog_ex-s:' . $json_settings['blog_ex']['s']['d'] . 'px;
}

@media (max-width: ' . $br_points['lg'] . 'px) {
    body{
        --uicore-typography--h1-s:' . $json_settings['h1']['s']['t'] . 'px;
        --uicore-typography--h2-s:' . $json_settings['h2']['s']['t'] . 'px;
        --uicore-typography--h3-s:' . $json_settings['h3']['s']['t'] . 'px;
        --uicore-typography--h4-s:' . $json_settings['h4']['s']['t'] . 'px;
        --uicore-typography--h5-s:' . $json_settings['h5']['s']['t'] . 'px;
        --uicore-typography--h6-s:' . $json_settings['h6']['s']['t'] . 'px;
        --uicore-typography--p-s:' . $json_settings['p']['s']['t'] . 'px;
    }
    .uicore-single-header h1.entry-title{
        --uicore-typography--h1-s:' . $json_settings['blog_h1']['s']['t'] . 'px;
    }
    .single-post .elementor-widget-uicore-the-content,
    .uicore-blog .uicore-post-content:not(.uicore-archive) .entry-content{
        --uicore-typography--h1-s:' . $json_settings['blog_h1']['s']['t'] . 'px;
        --uicore-typography--h2-s:' . $json_settings['blog_h2']['s']['t'] . 'px;
        --uicore-typography--h3-s:' . $json_settings['blog_h3']['s']['t'] . 'px;
        --uicore-typography--h4-s:' . $json_settings['blog_h4']['s']['t'] . 'px;
        --uicore-typography--h5-s:' . $json_settings['blog_h5']['s']['t'] . 'px;
        --uicore-typography--h6-s:' . $json_settings['blog_h6']['s']['t'] . 'px;
        --uicore-typography--p-s:' . $json_settings['blog_p']['s']['t'] . 'px;
    }
    .uicore-blog-grid {
        --uicore-typography--blog_title-s:' . $json_settings['blog_title']['s']['t'] . 'px;
        --uicore-typography--p-s:' . $json_settings['blog_ex']['s']['t'] . 'px;
    }
}
@media (max-width: ' . $br_points['md'] . 'px) {
    body{
        --uicore-typography--h1-s:' . $json_settings['h1']['s']['m'] . 'px;
        --uicore-typography--h2-s:' . $json_settings['h2']['s']['m'] . 'px;
        --uicore-typography--h3-s:' . $json_settings['h3']['s']['m'] . 'px;
        --uicore-typography--h4-s:' . $json_settings['h4']['s']['m'] . 'px;
        --uicore-typography--h5-s:' . $json_settings['h5']['s']['m'] . 'px;
        --uicore-typography--h6-s:' . $json_settings['h6']['s']['m'] . 'px;
        --uicore-typography--p-s:' . $json_settings['p']['s']['m'] . 'px;
    }
    .uicore-single-header h1.entry-title{
        --uicore-typography--h1-s:' . $json_settings['blog_h1']['s']['m'] . 'px;
    }
    .single-post .elementor-widget-uicore-the-content,
    .uicore-blog .uicore-post-content:not(.uicore-archive) .entry-content{
        --uicore-typography--h1-s:' . $json_settings['blog_h1']['s']['m'] . 'px;
        --uicore-typography--h2-s:' . $json_settings['blog_h2']['s']['m'] . 'px;
        --uicore-typography--h3-s:' . $json_settings['blog_h3']['s']['m'] . 'px;
        --uicore-typography--h4-s:' . $json_settings['blog_h4']['s']['m'] . 'px;
        --uicore-typography--h5-s:' . $json_settings['blog_h5']['s']['m'] . 'px;
        --uicore-typography--h6-s:' . $json_settings['blog_h6']['s']['m'] . 'px;
        --uicore-typography--p-s:' . $json_settings['blog_p']['s']['m'] . 'px;
    }
    .uicore-blog-grid {
        --uicore-typography--blog_title-s:' . $json_settings['blog_title']['s']['m'] . 'px;
        --uicore-typography--p-s:' . $json_settings['blog_ex']['s']['m'] . 'px;
    }
    }

';
/*
    .uicore-blog.single #main.uicore{
        padding-top:10px!important;
    }
*/
if ($json_settings['blogs_title'] === 'simple creative') {
    $css .= '
    .single-post .uicore-page-title {
        min-height: 50vh;
    }

	@media (max-width: ' . $br_points['md'] . 'px) {
		.single-post .uicore-single-header .uicore-entry-meta {
			flex-direction: column;
		}
		.single-post .uicore-single-header .uicore-entry-meta > * {
			margin-bottom: 10px;
		}
		.uicore-meta-separator {
    		display: none;
		}
	}
    ';
}
if ($json_settings['blogs_title'] === 'simple page title') {
    $css .= '
    .single-post div.ui-breadcrumb {
        margin-bottom: 20px;
    }
    .uicore-blog.single .uicore-single-header {
        margin-bottom: 30px;
    }
    ';

    if($json_settings['blogs_breadcrumb'] === 'true'){
        $css .= '
        .ui-breadcrumb{
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 1px;
            font-weight: 600;
        }
        ';
    }
}

$css .= '
@media (min-width: ' .$br_points['lg'] .'px) {
    .uicore-blog.single .uicore-page-title h1.uicore-title,
    .uicore-blog.single .uicore-page-title a,
    .uicore-blog.single .uicore-page-title p{
        max-width:' . $json_settings['blogs_pagetitle_width'] . '%;
    }
}

@media (max-width: ' .$br_points['lg'] .'px) {
    .uicore-blog #main.uicore{
    padding:' .    $json_settings['blog_padding']['t'] . 'px 0px;
    }
}

@media (max-width: ' . $br_points['md'] . 'px) {
    .uicore-blog #main.uicore{
        padding:' . $json_settings['blog_padding']['m'] . 'px 0px;
    }
}

@media (min-width: ' .$br_points['lg'] .'px) {
    .uicore-blog #main.uicore{
        padding:' .$json_settings['blog_padding']['d'] .'px 0px;
    }
}

.uicore-blog-grid{
    --uicore-blog--radius:' . $json_settings['blog_img_radius'] .'px;
}
@media(min-width: 768px) {
    .wp-block-image.alignwide img, .wp-block-image.alignwide figcaption {
      margin-left: clamp(-' . $json_settings['blogs_wide_align'] .'vw, -10em, -8vw);
      width: calc(100% + calc(clamp(-' . $json_settings['blogs_wide_align'] .'vw, -10em, -8vw) * -2));
      max-width: 100vw;
    }
  }
';

if ($json_settings['blogs_progress'] === 'true') {
    $css .= '
    .uicore-progress-bar{
        height: 2px;
        top: 0;
        width: 0;
        max-width: 100vw;
        overflow: hidden;
        position: fixed;
        z-index:98;
        left: 0;
        right: 0;
    }';
}

//filters
if($json_settings['blog_filters'] === 'true'){
    $css .= '
    ul.ui-category-list {
        display: none;
        list-style: none;
        padding: 0;
        margin: 0 0 3%;
        text-align: '.$json_settings['blog_filters_align'].';
    }

    .ui-category-list li {
        display: inline;
        margin-right: 10px;
        line-height: 2!important;
    }

    .ui-category-link {
        text-decoration: none;
        color: #333;
    }

    .ui-category-link:hover {
        text-decoration: underline;
    }

    .ui-category-link.ui-active {
        font-weight: bold;
    }

    /* CSS styles for category filter select dropdown */
    select#ui-category-filter {
        display: block;
        margin: 0 0 3%;
        padding: 10px;
        width: 100%;
        max-width: 100%;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
    }
    @media (min-width: 768px) {
        select#ui-category-filter {
            display: none;
        }
        ul.ui-category-list {
            display: block;
        }
    }
    ';
}

//animations
$css .= $this->grid_animation('blog');
