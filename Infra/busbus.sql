CREATE TABLE IF NOT EXISTS busbus (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    plate TEXT NOT NULL,
    vehicle_type TEXT NOT NULL CHECK(vehicle_type IN ('carro','moto','caminhao')),
    entry_time DATETIME NOT NULL,
    exit_time DATETIME,
    total_cost REAL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_plate ON busbus (plate);
CREATE INDEX IF NOT EXISTS idx_exit_time ON busbus (exit_time);
CREATE INDEX IF NOT EXISTS idx_open_entries
    ON busbus (exit_time)
    WHERE exit_time IS NULL;

CREATE UNIQUE INDEX IF NOT EXISTS idx_unique_active_plate
    ON busbus (plate)
    WHERE exit_time IS NULL;

SELECT * FROM  busbus;CREATE TABLE IF NOT EXISTS busbus (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    plate TEXT NOT NULL,
    vehicle_type TEXT NOT NULL CHECK(vehicle_type IN ('carro','moto','caminhao')),
    entry_time DATETIME NOT NULL,
    exit_time DATETIME,
    total_cost REAL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_plate ON busbus (plate);
CREATE INDEX IF NOT EXISTS idx_exit_time ON busbus (exit_time);
CREATE INDEX IF NOT EXISTS idx_open_entries
    ON busbus (exit_time)
    WHERE exit_time IS NULL;

CREATE UNIQUE INDEX IF NOT EXISTS idx_unique_active_plate
    ON busbus (plate)
    WHERE exit_time IS NULL;

SELECT * FROM  busbus;