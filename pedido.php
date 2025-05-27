<?php
header("Content-Type: application/json");
require_once("../db/conexion.php");

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['productos']) || !isset($input['metodo_pago'])) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

$productos = $input['productos'];
$metodo_pago = $input['metodo_pago'];
$fecha = date("Y-m-d");
$hora = date("H:i:s");

try {
    $conn->beginTransaction();

    $stmt = $conn->prepare("INSERT INTO pedidos (fecha, hora, metodo_pago) VALUES (?, ?, ?)");
    $stmt->execute([$fecha, $hora, $metodo_pago]);
    $pedido_id = $conn->lastInsertId();

    $stmt_item = $conn->prepare("INSERT INTO items_pedido (pedido_id, producto_id, cantidad) VALUES (?, ?, ?)");

    foreach ($productos as $item) {
        $stmt_item->execute([$pedido_id, $item['id'], $item['cantidad']]);
    }

    $conn->commit();

    echo json_encode(["success" => true, "pedido_id" => $pedido_id]);

} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(["error" => "Error al guardar el pedido", "detalle" => $e->getMessage()]);
}
?>