<?php
requireLogin();
$me = currentUser();
// Only the posts owned by this user (an admin sees the total of all posts on the Posts page, later)
$ownPostCount = countPostsByUser((int) $me['id']);

$pageTitle = 'Dashboard';
