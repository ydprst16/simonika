<?php

require_once __DIR__ . '/../services/UserService.php';

function handleUserRequest($conn)
{
    if (isset($_POST['tambah'])) {
        createUser($conn, $_POST);
    }

    if (isset($_POST['update'])) {
        updateUser($conn, $_POST);
    }

    if (isset($_GET['hapus'])) {
        deleteUser($conn, intval($_GET['hapus']));
    }

    if (isset($_GET['edit'])) {
        return getUserById($conn, intval($_GET['edit']));
    }

    return null;
}