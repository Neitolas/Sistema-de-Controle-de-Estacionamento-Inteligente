<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../vendor/autoload.php';

use App\Application\ParkingService;
use App\Infra\SQLiteParkingRecordRepository;
use App\Domain\Pricing\PricingStrategyFactory;

$dbPath = __DIR__ . '/../Infra/busbus.db';
$pdo = new PDO("sqlite:$dbPath");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$repository = new SQLiteParkingRecordRepository($pdo);
$pricingFactory = new PricingStrategyFactory();
$service = new ParkingService($repository, $pricingFactory);

$message = $_SESSION['message'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['message'], $_SESSION['error']);

$requestMethod = $_SERVER['REQUEST_METHOD'];

if ($requestMethod === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'entry') {
            $plate = strtoupper(trim($_POST['plate'] ?? ''));
            $type = strtolower(trim($_POST['type'] ?? ''));

            if (empty($plate) || empty($type)) {
                throw new Exception("Placa e tipo de veículo são obrigatórios.");
            }

            $service->registerEntry($plate, $type);
            $_SESSION['message'] = "Entrada de veículo ($type) com placa $plate registrada com sucesso!";

        } elseif (isset($_POST['action']) && $_POST['action'] === 'exit') {
            $plate = strtoupper(trim($_POST['plate'] ?? ''));

            if (empty($plate)) {
                throw new Exception("Placa é obrigatória para a saída.");
            }

            $record = $service->registerExit($plate);
            $repository->delete($record->getId());

            $_SESSION['message'] = "Saída de veículo com placa $plate registrada. Custo total: R$ " . number_format($record->getTotalCost(), 2, ',', '.');
        }

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

if ($requestMethod === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);

    try {
        $id = (int) ($_DELETE['id'] ?? 0);

        if ($id <= 0) {
            throw new Exception("ID é obrigatório para deletar.");
        }

        $repository->delete($id);

        echo json_encode(['success' => true, 'message' => 'Registro deletado com sucesso.']);
        exit;

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

$records = $repository->findAllActive();
$report = $service->generateReport();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPR-Demo - Controle de Estacionamento</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Controle de Estacionamento Inteligente (SPR-Demo)</h1>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <h2>Registrar Entrada</h2>
        <form method="POST">
            <input type="hidden" name="action" value="entry">
            <input type="text" name="plate" placeholder="Placa do Veículo (Ex: ABC1234)" required>
            <select name="type" required>
                <option value="">Selecione o Tipo de Veículo</option>
                <option value="carro">Carro</option>
                <option value="moto">Moto</option>
                <option value="caminhao">Caminhão</option>
            </select>
            <button type="submit">Registrar Entrada</button>
        </form>

        <h2>Registrar Saída</h2>
        <form method="POST">
            <input type="hidden" name="action" value="exit">
            <input type="text" name="plate" placeholder="Placa do Veículo (Ex: ABC1234)" required>
            <button type="submit">Registrar Saída</button>
        </form>

        <h2>Veículos Estacionados</h2>
        <?php if (empty($records)): ?>
            <p>Nenhum veículo estacionado no momento.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Tipo</th>
                        <th>Hora de Entrada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?= htmlspecialchars($record->getPlate()) ?></td>
                            <td><?= htmlspecialchars($record->getVehicleType()) ?></td>
                            <td><?= htmlspecialchars($record->getEntryTime()->format('d/m/Y H:i:s')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>Relatório de Faturamento</h2>
        <div class="report-box">
            <?php foreach ($report as $type => $data): ?>
                <div class="report-item">
                    <h3><?= ucfirst($type) ?></h3>
                    <p>Total de Veículos: <strong><?= $data['total_vehicles'] ?></strong></p>
                    <p>Faturamento Total: <strong>R$ <?= number_format($data['total_cost'], 2, ',', '.') ?></strong></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
