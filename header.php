<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Crypto App'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
    <div class="container text-end py-2">
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</span>
            <a href="/neme_forbtc/logout.php" class="ms-2">Logout</a>
            <span class="mx-2">|</span>
        <?php elseif (isset($_SESSION['customer_loggedin']) && $_SESSION['customer_loggedin'] === true): ?>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['customer_name']); ?>!</span>
            <a href="/neme_forbtc/Crupto_show/index.php" class="ms-2">Dashboard</a>
            <a href="/neme_forbtc/Crupto_show/portfolio.php" class="ms-2">Portfolio</a>
            <a href="/neme_forbtc/Crupto_show/profile.php" class="ms-2">My Profile</a>
            <a href="/neme_forbtc/customer_logout.php" class="ms-2">Logout</a>
            <span class="mx-2">|</span>
        <?php endif; ?>
        <a href="?lang=en">English</a> | <a href="?lang=th">ไทย</a>
    </div> 