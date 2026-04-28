<?php

require_once __DIR__ . '/../services/AuthService.php';

function handleAuth($conn)
{
    $login_error = "";
    $forgot_error = "";
    $forgot_success = "";
    $show_forgot_modal = false;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // LOGIN
        if (isset($_POST['login'])) {

            $res = loginUser($conn, trim($_POST['username']), $_POST['password']);

            if ($res['status']) {

                if ($res['role'] === 'admin') {
                    header("Location: dashboard-admin.php");
                } else {
                    header("Location: dashboard-operator.php");
                }
                exit();
            }

            $login_error = $res['message'];
        }

        // UBAH PASSWORD
        if (isset($_POST['ubah_password'])) {

            $show_forgot_modal = true;

            $res = changePassword(
                $conn,
                trim($_POST['username']),
                $_POST['new_password'],
                $_POST['confirm_password']
            );

            if ($res['status']) {
                $forgot_success = $res['message'];
            } else {
                $forgot_error = $res['message'];
            }
        }
    }

    return [
        'login_error' => $login_error,
        'forgot_error' => $forgot_error,
        'forgot_success' => $forgot_success,
        'show_forgot_modal' => $show_forgot_modal
    ];
}