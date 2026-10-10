        <?php $isAdmin = $navUser['role'] === 'admin'; ?>
        <nav class="nav panel-nav">
            <div class="panel-main">
                <?php if (!$navUser['must_change_password']): ?>
                    <a href="index.php?page=dashboard">Dashboard</a>
                    <a href="index.php?page=posts">Posts</a>
                    <a href="index.php?page=categories">Categories</a>
                    <a href="index.php?page=media">Media</a>
                    <?php if ($isAdmin): ?>
                        <a href="index.php?page=singles">Single</a>
                        <a href="index.php?page=settings">Settings</a>
                        <a href="index.php?page=users">Users</a>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="index.php?page=change-password">Change Password</a>
            </div>
            <div class="panel-session">
                <?php if (!$navUser['must_change_password']): ?>
                    <a href="index.php">Home</a>
                <?php endif; ?>
                <form method="post" action="index.php?page=logout" class="inline-form">
                    <?= csrfField() ?>
                    <button type="submit" class="logout-button">Logout</button>
                </form>
            </div>
        </nav>
