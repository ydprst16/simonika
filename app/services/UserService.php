<?php
require_once __DIR__ . '/../helpers/log.php';

function getAllOperators($conn)
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE role='operator'");
    $stmt->execute();
    return $stmt->get_result();
}

function getUserById($conn, $id)
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function createUser($conn, $data)
{
    $username = $data['username'];
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $role = $data['role'];
    $kelurahan = ($data['role'] === 'viewer') ? null : $data['kelurahan'];

    $stmt = $conn->prepare("
        INSERT INTO users (username, password, role, kelurahan)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("ssss", $username, $password, $role, $kelurahan);
    $stmt->execute();
    log_activity($conn, $_SESSION['username'], "Menambah user: $username ($role)");
}

function updateUser($conn, $data)
{
    $id = $data['id'];
    $username = $data['username'];
    $role = $data['role'];

    $kelurahan = ($role === 'viewer') ? null : $data['kelurahan'];

    if (!empty($data['password'])) {

        $password = password_hash($data['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("
            UPDATE users SET username=?, password=?, role=?, kelurahan=? WHERE id=?
        ");
        $stmt->bind_param("ssssi", $username, $password, $role, $kelurahan, $id);

    } else {

        $stmt = $conn->prepare("
            UPDATE users SET username=?, role=?, kelurahan=? WHERE id=?
        ");
        $stmt->bind_param("sssi", $username, $role, $kelurahan, $id);
    }

    $stmt->execute();
    log_activity($conn, $_SESSION['username'], "Update user: $username ($role)");
}

function deleteUser($conn, $id)
{
    $stmt_get = $conn->prepare("SELECT username, role FROM users WHERE id=?");
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $user = $stmt_get->get_result()->fetch_assoc();

    // delete
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    log_activity(
        $conn,
        $_SESSION['username'],
        "Menghapus user: " . $user['username'] . " (" . $user['role'] . ")"
    );
}