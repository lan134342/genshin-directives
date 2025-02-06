<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

$storageDir = __DIR__ . '/data'; // 数据存储目录

// 确保数据目录存在
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0777, true);
}

// 获取IP段
function getIPSegment() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $segments = explode('.', $ip);
    return $segments[0] . '.' . $segments[1]; // 返回前两段，例如 "192.168"
}

// 注册逻辑
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    // 检查必要字段
    if (!isset($input['qq']) || !isset($input['email']) || !isset($input['uid']) || !isset($input['password'])) {
        echo json_encode(["success" => false, "message" => "请填写完整信息"]);
        exit;
    }

    $qq = trim($input['qq']);
    $email = trim($input['email']);
    $uid = trim($input['uid']);
    $password = trim($input['password']);

    // 检查UID文件是否已存在
    $filePath = $storageDir . '/' . $uid . '.json';
    if (file_exists($filePath)) {
        echo json_encode(["success" => false, "message" => "该 UID 已被注册"]);
        exit;
    }

    // 检查邮箱格式
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "邮箱格式无效"]);
        exit;
    }

    // 获取IP段
    $ipSegment = getIPSegment();

    // 准备用户数据
    $userData = [
        "qq" => $qq,
        "email" => $email,
        "uid" => $uid,
        "password" => hash("sha256", $password), // 加密存储密码
        "ip_segment" => $ipSegment,
        "created_at" => date("Y-m-d H:i:s")
    ];

    // 将用户数据存储到文件中
    if (file_put_contents($filePath, json_encode($userData, JSON_PRETTY_PRINT))) {
        echo json_encode(["success" => true, "message" => "注册成功"]);
    } else {
        echo json_encode(["success" => false, "message" => "注册失败，请重试"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "不支持的请求方法"]);
}
