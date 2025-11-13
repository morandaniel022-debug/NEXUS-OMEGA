<?php
class Database {
    private $pdo;
    private $config;

    public function __construct() {
        $this->config = [
            'host' => getenv('DB_HOST') ?: 'localhost',
            'dbname' => getenv('DB_NAME') ?: 'nexus_omega',
            'user' => getenv('DB_USER') ?: 'root',
            'pass' => getenv('DB_PASS') ?: '',
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
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT UNIQUE NOT NULL,
                username TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                api_key TEXT UNIQUE,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                last_login DATETIME,
                is_active BOOLEAN DEFAULT 1
            )",

            "CREATE TABLE IF NOT EXISTS revenue_engines (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                engine_name TEXT UNIQUE NOT NULL,
                status TEXT DEFAULT 'inactive',
                daily_earnings REAL DEFAULT 0,
                total_earnings REAL DEFAULT 0,
                last_activity DATETIME,
                config TEXT
            )",

            "CREATE TABLE IF NOT EXISTS trading_operations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                engine_id INTEGER,
                operation_type TEXT NOT NULL,
                symbol TEXT,
                amount REAL,
                price REAL,
                profit REAL,
                status TEXT,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (engine_id) REFERENCES revenue_engines(id)
            )",

            "CREATE TABLE IF NOT EXISTS content_articles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                content TEXT,
                word_count INTEGER,
                published_url TEXT,
                earnings REAL DEFAULT 0,
                published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                status TEXT DEFAULT 'published'
            )",

            "CREATE TABLE IF NOT EXISTS ecommerce_stores (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                store_name TEXT NOT NULL,
                store_url TEXT,
                platform TEXT,
                daily_revenue REAL DEFAULT 0,
                products_count INTEGER DEFAULT 0,
                status TEXT DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )",

            "CREATE TABLE IF NOT EXISTS freelance_jobs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                platform TEXT NOT NULL,
                job_title TEXT NOT NULL,
                budget REAL,
                status TEXT DEFAULT 'applied',
                applied_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                completed_at DATETIME
            )",

            "CREATE TABLE IF NOT EXISTS revenue_transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                engine TEXT NOT NULL,
                transaction_type TEXT NOT NULL,
                amount REAL NOT NULL,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
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

        $stmt = $this->pdo->prepare("INSERT OR IGNORE INTO revenue_engines (engine_name, status) VALUES (?, 'inactive')");
        foreach ($engines as $engine) {
            $stmt->execute([$engine]);
        }
    }

    public function updateEngineStatus($engineName, $status) {
        $stmt = $this->pdo->prepare("UPDATE revenue_engines SET status = ?, last_activity = datetime('now') WHERE engine_name = ?");
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
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getRevenueByEngine() {
        $stmt = $this->pdo->query("SELECT engine, SUM(amount) as total FROM revenue_transactions WHERE transaction_type = 'revenue' GROUP BY engine");
        return $stmt->fetchAll();
    }

    public function getRecentTransactions($limit = 10) {
        $stmt = $this->pdo->prepare("SELECT * FROM revenue_transactions ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
?>
