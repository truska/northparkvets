<?php
// Shared development-site marker for rendered CMS pages.
?>
<style>
    body { padding-top: 32px !important; }
    #wccms-dev-banner {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #c8102e;
        color: #fff;
        font: 700 14px/1 Arial, sans-serif;
        letter-spacing: 1px;
        z-index: 2147483647;
    }
    body > .header, body > header.header, .fixed-top { top: 32px !important; }
    #sidebar { top: 132px !important; height: calc(100% - 132px) !important; }
    @media print {
        #wccms-dev-banner { display: none; }
        body { padding-top: 0 !important; }
    }
</style>
<div id="wccms-dev-banner">NEW DEV SITE</div>
