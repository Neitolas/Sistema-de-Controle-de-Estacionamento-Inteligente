# SPR-Demo: Sistema de Controle de Estacionamento Inteligente

> Sistema de gerenciamento de estacionamento desenvolvido com foco em Clean Code, SOLID, DRY, KISS e Object Calisthenics

## Tecnologias

- **PHP 8.3+** (Linguagem)
- **SQLite3** (banco de dados)
- **Composer** (gerenciador de dependências)
- **PSR-12** (padrão de codificação)

## Requisitos

- PHP = 8.3.6
- Composer com autoload PSR-4
- SQLite3 ( Usando o dbeaver apenas para visualização do projeto)
- Extensão PHP SQLite3 (`php-sqlite3`)

## Arquitetura

O projeto segue uma **arquitetura em camadas**, inspirada em Domain-Driven Design (DDD):

```
├── Application/          # Serviços de aplicação
│   └── ParkingService.php
├── Domain/              # Regras de negócio
│   ├── ParkingRecord.php
│   ├── IParkingRecordRepository.php
│   └── Pricing/
│       ├── IPricingStrategy.php
│       ├── AbstractPricingStrategy.php
│       ├── PricingStrategyFactory.php
│       ├── CarPricingStrategy.php
│       ├── MotorcyclePricingStrategy.php
│       └── TruckPricingStrategy.php
├── Infra/               # Infraestrutura e persistência
│   ├── SQLiteParkingRecordRepository.php
│   ├── busbus.sql
│   └── busbus.db
└── public/              # Camada de apresentação
    ├── index.php
    ├── style.css
    └── favicon.ico
```

## Princípios e Boas Práticas Aplicadas

### SOLID

- **SRP** - Cada classe tem uma única responsabilidade
- **OCP** - Extensível via Strategy Pattern (novas tarifas sem modificar código existente)
- **LSP** - Todas as estratégias são intercambiáveis
- **ISP** - Interfaces segregadas e focadas
- **DIP** - Dependências em abstrações, não em implementações concretas

### Clean Code & PSR-12

- `declare(strict_types=1)` em todos os arquivos
- Classes `final` quando apropriado
- Constructor property promotion
- Readonly properties
- Métodos pequenos e focados
- Nomes descritivos e semânticos
- Sem comentários (código auto-documentado)

### DRY, KISS & Object Calisthenics

- Extração de métodos privados para evitar duplicação
- Constantes para valores mágicos
- Níveis de indentação reduzidos
- Um nível de abstração por método

## Instalação e Configuração

### 1. Instalar dependências

```bash
composer install
```

### 2. Verificar extensões PHP necessárias

```bash
php -m | grep -i sqlite
```

Se não estiver instalado:

```bash
sudo apt install php-sqlite3
```

### 3. Criar banco de dados

```bash
cd Infra
sqlite3 busbus.db < busbus.sql
```

### 4. Iniciar servidor

Opção 1 - Via Composer:
```bash
composer start
```

Opção 2 - Via PHP direto:
```bash
php -S localhost:8000 -t public
```

### 5. Acessar aplicação

Abra seu navegador em: **http://localhost:8000**

## Funcionalidades

### Registrar Entrada
- Informar placa do veículo
- Selecionar tipo: Carro, Moto ou Caminhão
- Sistema registra data/hora de entrada automaticamente

### Registrar Saída
- Informar placa do veículo
- Sistema calcula automaticamente:
  - Tempo de permanência
  - Valor total a pagar

### Visualizar Veículos Estacionados
- Listagem de todos os veículos ativos
- Informações: Placa, Tipo e Hora de Entrada

### Relatório de Faturamento
- Total de veículos por tipo
- Faturamento acumulado por categoria

## Tarifas

As tarifas são calculadas por **hora cheia** (arredondamento para cima):

| Tipo de Veículo | Valor/Hora |
|-----------------|------------|
| Moto           | R$ 3,00    |
| Carro          | R$ 5,00    |
| Caminhão       | R$ 10,00   |

## Banco de Dados

**Tabela:** `busbus`

```sql
CREATE TABLE busbus (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    plate TEXT NOT NULL,
    vehicle_type TEXT NOT NULL CHECK(vehicle_type IN ('carro','moto','caminhao')),
    entry_time DATETIME NOT NULL,
    exit_time DATETIME,
    total_cost REAL DEFAULT 0
);
```

**Índices otimizados:**
- `idx_plate` - Busca por placa
- `idx_exit_time` - Filtro de veículos ativos
- `idx_open_entries` - Consultas de entradas abertas
- `idx_unique_active_plate` - Previne duplicatas de veículos ativos

## Scripts Disponíveis

```bash
# Iniciar servidor de desenvolvimento
composer start

# Regenerar autoloader
composer dump-autoload
```

## Extensibilidade

### Adicionar novo tipo de veículo

1. Criar classe de estratégia em `Domain/Pricing/`:
```php
final class ElectricCarPricingStrategy extends AbstractPricingStrategy
{
    private const HOURLY_RATE = 2.50;

    public function calculateCost(DateTimeImmutable $entryTime, DateTimeImmutable $exitTime): float
    {
        $hours = $this->calculateRoundedHours($entryTime, $exitTime);
        return $hours * self::HOURLY_RATE;
    }
}
```

2. Registrar na `PricingStrategyFactory`:
```php
private const STRATEGY_MAP = [
    'carro' => CarPricingStrategy::class,
    'moto' => MotorcyclePricingStrategy::class,
    'caminhao' => TruckPricingStrategy::class,
    'eletrico' => ElectricCarPricingStrategy::class, // Novo
];
```

3. Atualizar formulário HTML em `public/index.php`

## Licença

MIT
