<?php
class Database {
    private $pdo;
    private $config;

    public function __construct() {
        $this->config = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'dbname' => getenv('DB_NAME') ?: 'puppctel_nexusdb',
            'user' => getenv('DB_USER') ?: 'puppctel_nexususer',
            'pass' => getenv('DB_PASS') ?: 'u7Trr1bhtY)T',
            'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        ];

        $this->connect();
        $this->createTables();
    }

    private function connect() {
        $dsn = "mysql:host={$this->config['host']};dbname={$this->config['dbname']};charset={$this->config['charset']}";
        try {
            $this->pdo = new PDO($dsn, $this->config['user'], $this->config['pass'], $this->config['options']);
        } catch (PDOException $e) {
            // Fallback to SQLite if MySQL fails
            $this->createSQLiteFallback();
        }
    }

    private function createSQLiteFallback() {
        $dsn = "sqlite:" . __DIR__ . "/../nexus_omega.db";
        $this->pdo = new PDO($dsn);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function createTables() {
        $tables = [
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                username VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                api_key VARCHAR(64) UNIQUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL,
                is_active BOOLEAN DEFAULT TRUE
            )",

            "CREATE TABLE IF NOT EXISTS revenue_engines (
                id INT AUTO_INCREMENT PRIMARY KEY,
                engine_name VARCHAR(50) UNIQUE NOT NULL,
                status VARCHAR(20) DEFAULT 'inactive',
                daily_earnings DECIMAL(10,2) DEFAULT 0,
                total_earnings DECIMAL(10,2) DEFAULT 0,
                last_activity TIMESTAMP NULL,
                config JSON
            )",

            "CREATE TABLE IF NOT EXISTS trading_operations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                engine_id INT,
                operation_type VARCHAR(50) NOT NULL,
                symbol VARCHAR(20),
                amount DECIMAL(15,8),
                price DECIMAL(15,8),
                profit DECIMAL(10,2),
                status VARCHAR(20),
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (engine_id) REFERENCES revenue_engines(id)
            )",

            "CREATE TABLE IF NOT EXISTS content_articles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(500) NOT NULL,
                content TEXT,
                word_count INT,
                published_url VARCHAR(500),
                earnings DECIMAL(8,2) DEFAULT 0,
                published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                status VARCHAR(20) DEFAULT 'published'
            )",

            "CREATE TABLE IF NOT EXISTS ecommerce_stores (
                id INT AUTO_INCREMENT PRIMARY KEY,
                store_name VARCHAR(255) NOT NULL,
                store_url VARCHAR(500),
                platform VARCHAR(50),
                daily_revenue DECIMAL(8,2) DEFAULT 0,
                products_count INT DEFAULT 0,
                status VARCHAR(20) DEFAULT 'active',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",

            "CREATE TABLE IF NOT EXISTS freelance_jobs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                platform VARCHAR(50) NOT NULL,
                job_title VARCHAR(500) NOT NULL,
                budget DECIMAL(8,2),
                status VARCHAR(20) DEFAULT 'applied',
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                completed_at TIMESTAMP NULL
            )",

            "CREATE TABLE IF NOT EXISTS revenue_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                engine VARCHAR(50) NOT NULL,
                transaction_type VARCHAR(50) NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        ];

        foreach ($tables as $table) {
            try {
                $this->pdo->exec($table);
            } catch (Exception $e) {
                error_log("Table creation error: " . $e->getMessage());
            }
        }

        // Insert default revenue engines
        $this->insertDefaultEngines();
    }

    private function insertDefaultEngines() {
        $engines = [
            'quantum_trading' => 'Quantum Trading',
            'content_empire' => 'Content Empire',
            'ecommerce_empire' => 'E-commerce Empire',
            'ai_freelancing' => 'AI Freelancing',
            'venture_capital' => 'Venture Capital',
            'real_estate_ai' => 'Real Estate AI'
        ];

        $stmt = $this->pdo->prepare("INSERT IGNORE INTO revenue_engines (engine_name, status) VALUES (?, 'inactive')");
        foreach ($engines as $engine) {
            $stmt->execute([$engine]);
        }
    }

    public function updateEngineStatus($engineName, $status) {
        $stmt = $this->pdo->prepare("UPDATE revenue_engines SET status = ?, last_activity = NOW() WHERE engine_name = ?");
        $stmt->execute([$status, $engineName]);
    }

    public function logRevenueTransaction($engine, $type, $amount, $description) {
        $stmt = $this->pdo->prepare("INSERT INTO revenue_transactions (engine, transaction_type, amount, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$engine, $type, $amount, $description]);
    }

    public function logTradingOperation($type, $symbol, $amount, $price, $profit, $status) {
        $stmt = $this->pdo->prepare("INSERT INTO trading_operations (operation_type, symbol, amount, price, profit, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$type, $symbol, $amount, $price, $profit, $status]);
    }

    public function getEngineStats($engineName) {
        $stmt = $this->pdo->prepare("SELECT * FROM revenue_engines WHERE engine_name = ?");
        $stmt->execute([$engineName]);
        return $stmt->fetch();
    }

    public function getTotalRevenue() {
        $stmt = $this->pdo->query("SELECT SUM(amount) as total FROM revenue_transactions WHERE transaction_type = 'revenue'");
        return $stmt->fetch()['total'] ?? 0;
    }
}
?>
