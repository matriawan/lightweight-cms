<?php include __DIR__ . '/header.php'; ?>
        <h1>Login</h1>
        <?php if ($error !== ''): ?>
            <p class="message error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post" action="index.php?page=login" class="form">
            <?= csrfField() ?>
            <label for="login">Username or email</label>
            <input type="text" id="login" name="login" value="<?= htmlspecialchars($login) ?>" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <label class="checkbox"><input type="checkbox" name="remember" value="1"> Remember me</label>
            <button type="submit">Login</button>
        </form>
        <p><a href="index.php">Back to Home</a></p>
<?php include __DIR__ . '/footer.php'; ?>
