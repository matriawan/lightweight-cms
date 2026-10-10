<?php
if (!isPostRequest()) {
    redirect('home');
}
requireValidCsrf();
logoutUser();
header('Location: index.php?page=login');
exit;
