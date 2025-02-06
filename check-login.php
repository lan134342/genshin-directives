<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

// 检查是否登录
$response = [
    "loggedIn" => isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true
];

echo json_encode($response);
