<h1>Admin Users Dashboard</h1>
<p>Welcome, admin! Here you can manage users.</p>
<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th>
    </tr>
    <?php foreach ($data['users'] as $user): ?>
    <tr>
        <td><?= htmlspecialchars($user['user_id']) ?></td>
        <td><?= htmlspecialchars($user['username']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td><?= htmlspecialchars($user['user_level_id']) ?></td>
        <td>
            <form method="post" style="display:inline;" onsubmit="return confirm('Delete user?');">
                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">
                <button name="delete_user" value="1">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f7f7f7;
        margin: 0;
        padding: 0;
    }
    h1 {
        background: #2d3e50;
        color: #fff;
        padding: 20px;
        margin: 0 0 20px 0;
        text-align: center;
        letter-spacing: 1px;
    }
    p {
        text-align: center;
        color: #444;
        margin-bottom: 30px;
    }
    table {
        margin: 0 auto 40px auto;
        border-collapse: collapse;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        width: 90%;
        max-width: 900px;
    }
    th, td {
        padding: 12px 18px;
        text-align: left;
    }
    th {
        background: #4a6fa5;
        color: #fff;
        font-weight: 600;
    }
    tr:nth-child(even) {
        background: #f2f6fa;
    }
    tr:hover {
        background: #e9f1fb;
    }
    button {
        background: #e74c3c;
        color: #fff;
        border: none;
        padding: 7px 16px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s;
    }
    button:hover {
        background: #c0392b;
    }
    form {
        margin: 0;
    }
</style>