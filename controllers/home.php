<?php
// Home is a public page: it shows the header banner, header text, and footer text
$pageTitle = 'Home';
$isPublicPage = true;
$bannerUrl = settingImageUrl('banner');
$headerText = getSetting('header_text');
$footerText = getSetting('footer_text');
