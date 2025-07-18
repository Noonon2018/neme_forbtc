<?php
include_once 'init_lang.php';
$page_title = $lang['page_title_login'];
include 'header.php';
?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-center mb-4"><?php echo $lang['login_heading']; ?></h2>
                        
                        <div class="alert alert-warning text-center" role="alert">
                            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $lang['login_warning']; ?>
                        </div>
                        
                        <?php
                        if (isset($_GET['error'])) {
                            echo "<div class='alert alert-danger'>Invalid username or password.</div>";
                        }
                        ?>
                        
                        <form action="authenticate.php" method="post">
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-person-badge-fill"></i></span>
                                <input type="text" class="form-control" name="username" placeholder="<?php echo $lang['login_username_placeholder']; ?>" required>
                            </div>
                            
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" name="password" placeholder="<?php echo $lang['login_password_placeholder']; ?>" required>
                            </div>
                            
                            <button type="submit" class="btn btn-dark w-100"><?php echo $lang['login_button']; ?></button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="index.php">&laquo; Back to Homepage</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'footer.php'; ?> 