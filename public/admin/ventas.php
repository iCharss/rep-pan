<?php
session_start();
include '../../app/config.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->query("SELECT id, nombre_cliente, total, cart_data, status, created_at, payment_id FROM compras ORDER BY created_at DESC");
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ventas - Reppan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #00796b;
            padding: 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        .btn-verde {
            background-color: #43a047;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn-verde:hover {
            background-color: #388e3c;
        }

        table {
            width: 95%;
            margin: 30px auto;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }

        th {
            background-color: #00796b;
            color: white;
        }

        pre {
            background-color: #f9f9f9;
            padding: 10px;
            margin: 0;
            border-radius: 5px;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 13px;
        }

        .status-approved {
            color: green;
            font-weight: bold;
        }

        .status-pending {
            color: orange;
            font-weight: bold;
        }

        .status-rejected {
            color: red;
            font-weight: bold;
        }

        .mensaje-vacio {
            text-align: center;
            font-size: 18px;
            margin: 50px auto;
            color: #555;
        }
    </style>
</head>
<body>
    <header>
        <h1>Ventas Realizadas</h1>
        <a href="admin.php" class="btn-verde"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
    </header>

    <?php if (empty($resultado)): ?>
        <p class="mensaje-vacio">No se registran ventas.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Carrito</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Payment ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resultado as $venta): ?>
                <tr>
                    <td><?= $venta['id'] ?></td>
                    <td><?= htmlspecialchars($venta['nombre_cliente']) ?></td>
                    <td>$<?= number_format($venta['total'], 2) ?></td>
                    <td>
                        <?php
                            $cart = json_decode($venta['cart_data'], true);
                            if (is_array($cart)) {
                                echo '<pre>' . json_encode($cart, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '</pre>';
                            } else {
                                echo '<em>No disponible</em>';
                            }
                        ?>
                    </td>
                    <td class="status-<?= $venta['status'] ?>"><?= ucfirst($venta['status']) ?></td>
                    <td><?= date("d/m/Y H:i", strtotime($venta['created_at'])) ?></td>
                    <td><?= htmlspecialchars($venta['payment_id']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
