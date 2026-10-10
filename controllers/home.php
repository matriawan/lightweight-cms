<?php
// Home is the public page: top navigation, banner, categories, search, and the published posts
$layout = 'public';
$pageTitle = 'Home';
$isPublicPage = true;
$bannerUrl = settingImageUrl('banner');
$headerText = getSetting('header_text');
$footerText = getSetting('footer_text');

$page_number = isset($_GET['p']) ? (int) $_GET['p'] : 1;
$keyword = isset($_GET['q']) && is_string($_GET['q']) ? mb_substr(trim($_GET['q']), 0, 100) : '';
$categoryId = isset($_GET['category']) && is_string($_GET['category']) && ctype_digit($_GET['category']) ? (int) $_GET['category'] : 0;

$categories = getCategories();
$filterParams = [];
if ($categoryId > 0) {
    $filterParams['category'] = $categoryId;
}
if ($keyword !== '') {
    $filterParams['q'] = $keyword;
}

$pagination = getPagination(countPublishedPosts($categoryId, $keyword), $page_number, getPerPage());
$posts = getPublishedPosts($categoryId, $keyword, $pagination['per_page'], $pagination['offset']);
