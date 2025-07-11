<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS

$css .='    
body{
    --uicore-typography--h1-f:' . $this->fam($json_settings['h1']['f']) . ';
    --uicore-typography--h1-w:' . $this->wt($json_settings['h1']) . ';
    --uicore-typography--h1-h:' . $json_settings['h1']['h'] . ';
    --uicore-typography--h1-ls:' . $json_settings['h1']['ls'] . 'em;
    --uicore-typography--h1-t:' . $json_settings['h1']['t'] . ';
    --uicore-typography--h1-st:' . $this->st($json_settings['h1']) . ';
    --uicore-typography--h1-c:' . $this->color($json_settings['h1']['c']) . ';
    --uicore-typography--h1-s:' . $json_settings['h1']['s']['d'] . 'px;

    --uicore-typography--h2-f:' . $this->fam($json_settings['h2']['f']) . ';
    --uicore-typography--h2-w:' . $this->wt($json_settings['h2']) . ';
    --uicore-typography--h2-h:' . $json_settings['h2']['h'] . ';
    --uicore-typography--h2-ls:' . $json_settings['h2']['ls'] . 'em;
    --uicore-typography--h2-t:' . $json_settings['h2']['t'] . ';
    --uicore-typography--h2-st:' . $this->st($json_settings['h2']) . ';
    --uicore-typography--h2-c:' . $this->color($json_settings['h2']['c']) . ';
    --uicore-typography--h2-s:' . $json_settings['h2']['s']['d'] . 'px;

    --uicore-typography--h3-f:' . $this->fam($json_settings['h3']['f']) . ';
    --uicore-typography--h3-w:' . $this->wt($json_settings['h3']) . ';
    --uicore-typography--h3-h:' . $json_settings['h3']['h'] . ';
    --uicore-typography--h3-ls:' . $json_settings['h3']['ls'] . 'em;
    --uicore-typography--h3-t:' . $json_settings['h3']['t'] . ';
    --uicore-typography--h3-st:' . $this->st($json_settings['h3']) . ';
    --uicore-typography--h3-c:' . $this->color($json_settings['h3']['c']) . ';
    --uicore-typography--h3-s:' . $json_settings['h3']['s']['d'] . 'px;

    --uicore-typography--h4-f:' . $this->fam($json_settings['h4']['f']) . ';
    --uicore-typography--h4-w:' . $this->wt($json_settings['h4']) . ';
    --uicore-typography--h4-h:' . $json_settings['h4']['h'] . ';
    --uicore-typography--h4-ls:' . $json_settings['h4']['ls'] . 'em;
    --uicore-typography--h4-t:' . $json_settings['h4']['t'] . ';
    --uicore-typography--h4-st:' . $this->st($json_settings['h4']) . ';
    --uicore-typography--h4-c:' . $this->color($json_settings['h4']['c']) . ';
    --uicore-typography--h4-s:' . $json_settings['h4']['s']['d'] . 'px;

    --uicore-typography--h5-f:' . $this->fam($json_settings['h5']['f']) . ';
    --uicore-typography--h5-w:' . $this->wt($json_settings['h5']) . ';
    --uicore-typography--h5-h:' . $json_settings['h5']['h'] . ';
    --uicore-typography--h5-ls:' . $json_settings['h5']['ls'] . 'em;
    --uicore-typography--h5-t:' . $json_settings['h5']['t'] . ';
    --uicore-typography--h5-st:' . $this->st($json_settings['h5']) . ';
    --uicore-typography--h5-c:' . $this->color($json_settings['h5']['c']) . ';
    --uicore-typography--h5-s:' . $json_settings['h5']['s']['d'] . 'px;

    --uicore-typography--h6-f:' . $this->fam($json_settings['h6']['f']) . ';
    --uicore-typography--h6-w:' . $this->wt($json_settings['h6']) . ';
    --uicore-typography--h6-h:' . $json_settings['h6']['h'] . ';
    --uicore-typography--h6-ls:' . $json_settings['h6']['ls'] . 'em;
    --uicore-typography--h6-t:' . $json_settings['h6']['t'] . ';
    --uicore-typography--h6-st:' . $this->st($json_settings['h6']) . ';
    --uicore-typography--h6-c:' . $this->color($json_settings['h6']['c']) . ';
    --uicore-typography--h6-s:' . $json_settings['h6']['s']['d'] . 'px;

    --uicore-typography--p-f:' . $this->fam($json_settings['p']['f']) . ';
    --uicore-typography--p-w:' . $this->wt($json_settings['p']) . ';
    --uicore-typography--p-h:' . $json_settings['p']['h'] . ';
    --uicore-typography--p-ls:' . $json_settings['p']['ls'] . 'em;
    --uicore-typography--p-t:' . $json_settings['p']['t'] . ';
    --uicore-typography--p-st:' . $this->st($json_settings['p']) . ';
    --uicore-typography--p-c:' . $this->color($json_settings['p']['c']) . ';
    --uicore-typography--p-s:' . $json_settings['p']['s']['d'] . 'px;
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