<?php

declare(strict_types=1);

namespace App\Infra;

use App\Domain\IParkingRecordRepository;
use App\Domain\ParkingRecord;
use DateTimeImmutable;
use InvalidArgumentException;
use PDO;

final class SQLiteParkingRecordRepository implements IParkingRecordRepository
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function saveEntry(ParkingRecord $record): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO busbus (plate, vehicle_type, entry_time)
             VALUES (:plate, :vehicle_type, :entry_time)'
        );

        $stmt->execute([
            'plate' => $record->getPlate(),
            'vehicle_type' => $record->getVehicleType(),
            'entry_time' => $record->getEntryTime()->format(self::DATE_FORMAT),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function updateExit(ParkingRecord $record): void
    {
        $this->validateRecordHasId($record);

        $stmt = $this->pdo->prepare(
            'UPDATE busbus
             SET exit_time = :exit_time, total_cost = :total_cost
             WHERE id = :id'
        );

        $stmt->execute([
            'exit_time' => $record->getExitTime()->format(self::DATE_FORMAT),
            'total_cost' => $record->getTotalCost(),
            'id' => $record->getId(),
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM busbus WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function findActiveByPlate(string $plate): ?ParkingRecord
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM busbus
             WHERE plate = :plate AND exit_time IS NULL
             LIMIT 1'
        );

        $stmt->execute(['plate' => strtoupper($plate)]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->hydrate($data) : null;
    }

    public function findAllActive(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM busbus
             WHERE exit_time IS NULL
             ORDER BY entry_time DESC'
        );

        return $this->hydrateCollection($stmt);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT * FROM busbus
             ORDER BY entry_time DESC'
        );

        return $this->hydrateCollection($stmt);
    }

    private function hydrate(array $data): ParkingRecord
    {
        return ParkingRecord::reconstituteFromDatabase(
            $data['id'],
            $data['plate'],
            $data['vehicle_type'],
            new DateTimeImmutable($data['entry_time']),
            $data['exit_time'] ? new DateTimeImmutable($data['exit_time']) : null,
            $data['total_cost']
        );
    }

    private function hydrateCollection($stmt): array
    {
        $records = [];

        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $records[] = $this->hydrate($data);
        }

        return $records;
    }

    private function validateRecordHasId(ParkingRecord $record): void
    {
        if ($record->getId() === null) {
            throw new InvalidArgumentException('Não é possível atualizar um registro sem ID.');
        }
    }
}
