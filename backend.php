<?php
session_start(); // 启用会话管理

// 设置跨域头，支持前端请求
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Muip 配置
$muipConfig = [
    "muipEndpoint" => "http://110.40.24.226:23406/api", // Muip API 服务地址
    "muipRegion"   => "sans_dev",                       // 区域配置
    "ticket"       => "GM",                             // 固定 ticket 值
    "cmd"          => "1116"                            // 固定 cmd 值
];

// 执行 Muip 指令
function executeMuipCommand($uid, $command, $muipConfig) {
    // 构造完整的请求 URL
    $requestUrl = sprintf(
        "%s?region=%s&ticket=%s&cmd=%s&uid=%s&msg=%s",
        $muipConfig["muipEndpoint"],
        $muipConfig["muipRegion"],
        $muipConfig["ticket"],
        $muipConfig["cmd"],
        urlencode($uid),
        urlencode($command)
    );
    // 检查 UID 是否与注册时一致
if ($_SESSION['uid'] !== $uid) {
    echo json_encode(["success" => false, "message" => "UID 与注册时不一致，禁止执行指令"]);
    exit;
}

    // 打印请求 URL 调试信息
    error_log("Muip 请求 URL: " . $requestUrl);

    // 发起 HTTP GET 请求
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $requestUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 设置超时时间
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 忽略 HTTPS 验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 忽略 HTTPS 验证

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // 检查 Curl 请求是否出错
    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        error_log("Curl 错误：" . $error);
        return ["success" => false, "message" => "Muip 请求失败：" . $error];
    }

    curl_close($ch);

    // 检查 HTTP 状态码
    if ($httpCode !== 200) {
        error_log("Muip 请求失败，HTTP 状态码：" . $httpCode);
        return ["success" => false, "message" => "Muip 响应 HTTP 状态码错误：" . $httpCode];
    }

    // 解析 Muip 响应内容
    $responseBody = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Muip 响应解析失败：" . $response); // 打印未解析的原始响应
        return ["success" => false, "message" => "Muip 响应格式错误"];
    }

    // 打印完整响应内容（调试用）
    error_log("Muip 返回的完整响应：" . json_encode($responseBody));

    // 检查响应中的 retcode
    if ($responseBody["retcode"] !== 0) {
        error_log("Muip 返回错误信息：" . $responseBody["msg"]);
        return ["success" => false, "message" => "指令执行失败：" . $responseBody["msg"]];
    }

    // 返回成功信息
    return ["success" => true, "message" => $responseBody["data"]["msg"]];
}

// 检查是否有权限
function checkPermission($uid) {
    $filePath = 'records/zl.txt';
    if (!file_exists($filePath)) {
        return false;
    }
    $contents = file_get_contents($filePath);
    $uids = explode(PHP_EOL, $contents);
    return in_array($uid, $uids);
}

// 记录日志
function logExecution($uid, $command) {
    $logData = sprintf(
        "[%s] IP: %s, UID: %s, Command: %s\n",
        date('Y-m-d H:i:s'),
        $_SERVER['REMOTE_ADDR'],
        $uid,
        $command
    );
    file_put_contents('execution_log.txt', $logData, FILE_APPEND);
}

// 主逻辑：处理前端请求
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rawInput = file_get_contents("php://input");
    $input = json_decode($rawInput, true);

    // 检查用户是否已登录
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        echo json_encode(["success" => false, "message" => "请登录后重试"]);
        exit;
    }

    // 检查参数是否完整
    if (!isset($input["uid"]) || !isset($input["command"])) {
        echo json_encode(["success" => false, "message" => "请求参数不完整"]);
        exit;
    }

    $uid = trim($input["uid"]);         // 前端传递的 UID
    $command = trim($input["command"]); // 前端传递的指令

    // 检查权限
    if (!checkPermission($uid)) {
        echo json_encode(["success" => false, "message" => "您无权限使用指令"]);
        exit;
    }

    // 记录执行日志
    logExecution($uid, $command);

    // 执行 Muip 指令
    $muipResult = executeMuipCommand($uid, $command, $muipConfig);

    // 返回 Muip 执行结果
    echo json_encode($muipResult);
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "不支持的请求方法"]);
}
