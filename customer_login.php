<?php
include_once 'init_lang.php';
$page_title = $lang['page_title_customer_login'];
include 'header.php';
?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <h3><i class="bi bi-shield-check"></i> CryptoReg</h3>
                        </div>
                        <h2 class="text-center mb-4"><?php echo $lang['customer_login_heading']; ?></h2>
                        
                        <?php
                        if (isset($_GET['error'])) {
                            echo "<div class='alert alert-danger'>Invalid email or password.</div>";
                        }
                        ?>
                        
                        <form action="customer_authenticate.php" method="post">
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                <input type="email" class="form-control" name="email" placeholder="<?php echo $lang['customer_login_email_placeholder']; ?>" required>
                            </div>
                            
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" name="password" placeholder="<?php echo $lang['customer_login_password_placeholder']; ?>" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100"><?php echo $lang['customer_login_button']; ?></button>
                        </form>
                        
                        <p class="text-center mt-3"><?php echo $lang['customer_login_no_account']; ?> <a href="index.php"><?php echo $lang['customer_login_register_link']; ?></a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'footer.php'; ?> 