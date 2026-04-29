<?php

require_once __DIR__ . '/../services/UserService.php';

function handleUserRequest($conn)
{
    if (isset($_POST['tambah'])) {

        createUser($conn, $_POST);

        header("Location: kelola-operator.php?status=created");
        exit();
    }

    if (isset($_POST['update'])) {

        updateUser($conn, $_POST);

        header("Location: kelola-operator.php?status=updated");
        exit();
    }

    if (isset($_GET['hapus'])) {

        deleteUser($conn, $_GET['hapus']);

        header("Location: kelola-operator.php?status=deleted");
        exit();
    }

    if (isset($_GET['edit'])) {
        return getUserById($conn, $_GET['edit']);
    }

    return null;
}