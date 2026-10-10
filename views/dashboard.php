<?php include __DIR__ . '/header.php'; ?>
        <h1>Dashboard</h1>
        <p>Welcome, <strong><?= htmlspecialchars($me['display_name']) ?></strong>.</p>
        <table class="detail">
            <tr><th>Role</th><td><?= htmlspecialchars(ucfirst($me['role'])) ?></td></tr>
            <tr><th>Your Posts</th><td><?= (int) $ownPostCount ?></td></tr>
        </table>
<?php include __DIR__ . '/footer.php'; ?>
