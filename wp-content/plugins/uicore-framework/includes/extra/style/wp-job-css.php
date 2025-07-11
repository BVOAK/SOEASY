<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS

$css .= '
.awsm-job-form-inner h2 {
    font-size: var(--uicore-typography--h4-s);
}
.awsm-job-content h3{
    font-size: var(--uicore-typography--h3-s);
}
.awsm-job-single-wrap {
    margin-bottom: 3em;
    margin-top: 3em;
}
.awsm-jobs-archive-title{
    display:none
}
.awsm-job-container{
    width: 95%;
    padding-left: 10px!important;
    padding-right: 10px!important;
    max-width:' . $json_settings['gen_full_w'] . 'px;
}
';