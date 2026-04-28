<?php

require_once __DIR__ . '/../helpers/log.php';

function loginUser($conn, $username, $password)
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        log_activity($conn, $username, "Login gagal - username tidak ditemukan");
        return ['status' => false, 'message' => 'Username tidak ditemukan!'];
    }

    $user = $result->fetch_assoc();

    // password baru (bcrypt)
    if (password_verify($password, $user['password'])) {

        session_regenerate_id(true);

        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['kelurahan'] = $user['kelurahan'];

        log_activity($conn, $user['username'], "Login berhasil");

        return ['status' => true, 'role' => $user['role']];
    }

    // fallback md5 lama
    if (md5($password) === $user['password']) {

        $newHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt_update = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt_update->bind_param("si", $newHash, $user['id']);
        $stmt_update->execute();

        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['kelurahan'] = $user['kelurahan'];

        log_activity($conn, $user['username'], "Login berhasil (upgrade hash)");

        return ['status' => true, 'role' => $user['role']];
    }

    log_activity($conn, $username, "Login gagal - password salah");

    return ['status' => false, 'message' => 'Password salah!'];
}


function changePassword($conn, $username, $new_password, $confirm_password)
{
    if ($new_password !== $confirm_password) {
        return ['status' => false, 'message' => 'Password tidak cocok!'];
    }

    if (strlen($new_password) < 6) {
        return ['status' => false, 'message' => 'Password minimal 6 karakter.'];
    }

    if (!preg_match('/[A-Za-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
        return ['status' => false, 'message' => 'Password harus mengandung huruf dan angka.'];
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ['status' => false, 'message' => 'Username tidak ditemukan!'];
    }

    $hash = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt_update = $conn->prepare("UPDATE users SET password=? WHERE username=?");
    $stmt_update->bind_param("ss", $hash, $username);

    if ($stmt_update->execute()) {
        log_activity($conn, $username, "Mengubah password");
        return ['status' => true, 'message' => 'Password berhasil diubah.'];
    }

    return ['status' => false, 'message' => 'Gagal mengubah password.'];
}