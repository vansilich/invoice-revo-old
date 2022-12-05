<?php

header('Content-Type: application/json');
$postData = file_get_contents('php://input');

$log_file = uniqid(date("Y-m-d-H-i-s") . '_') . '.log';
file_put_contents(__DIR__ . "/logs/updates/requests/" . $log_file, $postData);

$data = json_decode($postData, true);

require_once __DIR__ . '/Src/init.php';
$mysqli = $DbService->getConnection();

if (isset($data['super_is_paid'])) {
    $result = array();

    foreach ($data['data'] as $item) {
        $sql = 'UPDATE `pdf_uploads` 
                SET 
                    `InvoiceId` = "' . $item['InvoiceId'] . '",
                    `paid_percent` = "' . $item['paid_percent'] . '",
                    `paid_date` = "' . $item['paid_date'] . '",
                    `order_amount` = "' . $item['order_amount'] . '",
                    `paid_amount` = "' . $item['paid_amount'] . '",
                    `paid_detail` = "' . str_replace('"', '\"', serialize($item['paid_detail'])) . '"';

        if(strtotime($item['contract_date']) > 0){
            $sql .= ', `contract_date` = "' . $item['contract_date'] . '"';
        }

        $sql .= ' WHERE `order_id` = "' . $item['order_id'] . '"';
        $result[$item['order_id']] = $mysqli->query($sql) !== false ? 1 : 0;

        file_put_contents(__DIR__ . "/logs/updates/results/" . $log_file, $sql . PHP_EOL . PHP_EOL, FILE_APPEND);
    }

    echo json_encode(array('result' => $result));

} else {
    
    $sql = 'UPDATE `pdf_uploads` SET `is_paid` = 1 WHERE `order_id` = "' . $data['order_id'] . '"';
    $result = $mysqli->query($sql);

    file_put_contents(__DIR__ . "/logs/updates/results/" . $log_file, $sql . PHP_EOL . PHP_EOL, FILE_APPEND);
    
    echo json_encode(array('result' => $result));
}



?>