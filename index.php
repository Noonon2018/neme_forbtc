<?php
include_once 'init_lang.php';
$page_title = $lang['page_title_register'];
include 'header.php';
?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <form action="register.php" method="post">
                            <div class="text-center mb-4">
                                <h3><i class="bi bi-shield-check"></i> CryptoReg</h3>
                            </div>
                            <h2 class="text-center mb-4"><?php echo $lang['register_heading']; ?></h2>

                            
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control" name="first_name" placeholder="<?php echo $lang['register_first_name']; ?>" required>
                            </div>
                            
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control" name="last_name" placeholder="<?php echo $lang['register_last_name']; ?>" required>
                            </div>
                            
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                                <input type="email" class="form-control" name="email" placeholder="<?php echo $lang['register_email']; ?>" required>
                            </div>
                            
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" name="password" placeholder="<?php echo $lang['register_password']; ?>" required>
                            </div>
                            
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" name="confirm_password" placeholder="<?php echo $lang['register_confirm_password']; ?>" required>
                            </div>
                            
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-telephone-fill"></i></span>
                                <input type="text" class="form-control" name="phone" placeholder="<?php echo $lang['register_phone']; ?>" required>
                            </div>
                            
                            <div class="g-recaptcha mb-3" data-sitekey="6LfM54YrAAAAAK4XSfV0KaQLUmp7MD_KbLl9TVQN"></div>
                            
                            <button type="submit" class="btn btn-primary w-100"><?php echo $lang['register_button']; ?></button>
                        </form>
                        
                        <p class="text-center mt-3"><?php echo $lang['register_already_account']; ?> <a href="customer_login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include 'footer.php'; ?>

<script src="https://www.google.com/recaptcha/api.js" async defer></script> 