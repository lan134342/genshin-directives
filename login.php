<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");

$storageDir = __DIR__ . '/data'; // 数据存储目录

// 获取IP段
function getIPSegment() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $segments = explode('.', $ip);
    return $segments[0] . '.' . $segments[1]; // 返回前两段，例如 "192.168"
}

// 登录逻辑
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);

    // 检查必要字段
    if (!isset($input['identifier']) || !isset($input['password'])) {
        echo json_encode(["success" => false, "message" => "请填写完整信息"]);
        exit;
    }

    $identifier = trim($input['identifier']); // 玩家UID或邮箱
    $password = trim($input['password']);
    $foundUser = null;

    // 遍历数据目录，寻找匹配的UID或邮箱
    foreach (glob($storageDir . '/*.json') as $file) {
        $userData = json_decode(file_get_contents($file), true);
        if ($userData['uid'] === $identifier || $userData['email'] === $identifier) {
            $foundUser = $userData;
            break;
        }
    }

    // 如果用户不存在
    if (!$foundUser) {
        echo json_encode(["success" => false, "message" => "用户不存在"]);
        exit;
    }

    // 验证密码
    if (hash("sha256", $password) !== $foundUser['password']) {
        echo json_encode(["success" => false, "message" => "密码错误"]);
        exit;
    }

    // 获取当前IP段
    $ipSegment = getIPSegment();
    if ($foundUser['ip_segment'] !== $ipSegment) {
        echo json_encode(["success" => false, "message" => "检测到IP的跨度较大，请联系管理员解决"]);
        exit;
    }

    // 设置登录状态
    $_SESSION['loggedin'] = true;
    $_SESSION['uid'] = $foundUser['uid'];

    echo json_encode(["success" => true, "message" => "登录成功"]);
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "不支持的请求方法"]);
}
