<?php

function jsonResponse($data, int $code) {
    http_response_code($code);
    echo json_encode($data);
}

function checkId ($db, $id) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = $id");
    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$res) {
        jsonResponse('User not found', 404);
        die();
    }
}

function checkData($data) {
    if(!isset($data) || $data['firstName'] === '' || $data['lastName'] === '' || !isset($data['firstName']) || !isset($data['lastName'])) {
        jsonResponse('The fields are incorrect', 400);
        die();
    }
}

function getUsers($conn, $data) {
    $page = $data['page'] ?? 1;
    $limit = 4;
    $offset = ($page - 1) * $limit;

    $query = "SELECT * FROM users WHERE id > 0 limit $offset, $limit";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $users = [];
    while ($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $users[] = $res;
    }
    jsonResponse($users, 200);
}

function getUser($conn, $id) {

    checkId($conn,$id);

    $query = " SELECT * FROM `users` WHERE `id` = $id";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    jsonResponse($res, 200);
}

function updateUser($conn, $id, $data) {

    checkId($conn,$id);
    checkData($data);

    $query = "UPDATE `users` SET `firstName`= :firstName,`lastName`= :lastName WHERE `id` = $id";

    $stmt = $conn->prepare($query);
    $stmt->execute($data);

    http_response_code(202);
}

function addUser($conn, $data) {

    checkData($data);
    $query = 'INSERT INTO `users` VALUES (NULL, :firstName, :lastName)';

    $stmt = $conn->prepare($query);
    $stmt->execute($data);

    http_response_code(201);
    echo $conn->lastInsertId();
}

function deleteUser($conn, $id) {

    checkId($conn, $id);

    $query = "DELETE FROM `users` WHERE `id` = $id";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    http_response_code(204);
}