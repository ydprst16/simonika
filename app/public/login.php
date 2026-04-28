<?php
session_start();

require_once __DIR__ . '/../config/conn.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

$data = handleAuth($conn);

$login_error = $data['login_error'];
$forgot_error = $data['forgot_error'];
$forgot_success = $data['forgot_success'];
$show_forgot_modal = $data['show_forgot_modal'];
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login Monografi Kelurahan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

</head>

<body style="
font-family:Poppins;
background:#f4f6f9;
height:100vh;
display:flex;
align-items:center;
justify-content:center;
">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-md-4">

                <div class="card shadow-sm border-0 p-4" style="border-radius:12px">

                    <div class="text-center mb-4">

                        <img src="<?= BASE_URL ?>assets/images/logo2.png" width="70">

                        <h5 class="mt-3">Monografi Kelurahan</h5>

                    </div>

                    <?php if ($login_error): ?>

                        <div class="alert alert-danger text-center">
                            <?php echo htmlspecialchars($login_error); ?>
                        </div>

                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bi bi-person"></i>
                                </span>

                                <input type="text" name="username" class="form-control" placeholder="Username" required>

                            </div>

                        </div>

                        <div class="mb-3">

                            <div class="input-group">

                                <span class="input-group-text">
                                    <i class="bi bi-lock"></i>
                                </span>

                                <input type="password" name="password" class="form-control" placeholder="Password"
                                    required>

                            </div>

                        </div>

                        <button type="submit" name="login" class="btn btn-primary w-100">

                            <i class="bi bi-box-arrow-in-right"></i>
                            Login

                        </button>

                    </form>

                    <div class="text-center mt-3">

                        <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">

                            Lupa Password?

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- MODAL UBAH PASSWORD -->

    <div class="modal fade <?php echo $show_forgot_modal ? 'show' : ''; ?>" id="forgotPasswordModal">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">
                        Ubah Password
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                </div>

                <div class="modal-body">

                    <?php if ($forgot_error): ?>

                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($forgot_error); ?>
                        </div>

                    <?php endif; ?>

                    <?php if ($forgot_success): ?>

                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($forgot_success); ?>
                        </div>

                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">

                            <input type="text" name="username" class="form-control" placeholder="Username" required>

                        </div>

                        <div class="mb-3">

                            <input type="password" name="new_password" class="form-control" placeholder="Password Baru"
                                required>

                        </div>

                        <div class="mb-3">

                            <input type="password" name="confirm_password" class="form-control"
                                placeholder="Konfirmasi Password" required>

                            <div id="passwordMatch" class="small mt-1"></div>

                        </div>

                        <button type="submit" name="ubah_password" class="btn btn-success w-100">

                            Ubah Password

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>

        const newPass = document.querySelector("input[name='new_password']");
        const confirmPass = document.querySelector("input[name='confirm_password']");
        const msg = document.getElementById("passwordMatch");

        if (newPass && confirmPass) {

            confirmPass.addEventListener("keyup", function () {

                if (newPass.value === confirmPass.value) {

                    msg.innerHTML = "Password cocok";
                    msg.style.color = "green";

                } else {

                    msg.innerHTML = "Password tidak cocok";
                    msg.style.color = "red";

                }

            });

        }

    </script>

    <?php if ($show_forgot_modal): ?>

        <script>

            var myModal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
            myModal.show();

        </script>

    <?php endif; ?>

</body>

</html>