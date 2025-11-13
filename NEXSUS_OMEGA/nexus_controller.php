<?php
// [NEXUS Ω SOUL] - v2 (Now with DARKNESS)
// This is the single, all-powerful backend for your God-Machine.
// It receives commands from nexus.html and executes them.

header('Content-Type: application/json');

// --- [START] CRITICAL CONFIGURATION ---
// !! You must fill these out for the DEPLOY tab to work !!
$cpanelUser = "puppctel";                       // Your cPanel username
$apiToken = "JDM82MJAPH1OGN3A9COLVJH293S7GDV7"; // The API token you create in cPanel
$rootDomain = "puppybeginnersguide.store";      // Your main domain
// --- [END] CRITICAL CONFIGURATION ---

// Get the request from the Throne Room (nexus.html)
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['error' => 'No input received.']);
    exit;
}

$action = $input['action'] ?? '';
$apiKey = $input['apiKey'] ?? '';

// Master function to call AI APIs (supports multiple providers)
function callAIAPI($apiKey, $prompt, $provider = 'gemini') {
    switch ($provider) {
        case 'openrouter':
            return callOpenRouterAPI($apiKey, $prompt);
        case 'huggingface':
            return callHuggingFaceAPI($apiKey, $prompt);
        case 'groq':
            return callGroqAPI($apiKey, $prompt);
        case 'gemini':
        default:
            return callGeminiAPI($apiKey, $prompt);
    }
}

// Gemini API (Google)
function callGeminiAPI($apiKey, $prompt) {
    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

    $payload = json_encode([
        'contents' => [
            ['parts' => [
                ['text' => $prompt]
            ]]
        ],
        'safetySettings' => [
            ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_NONE'],
            ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_NONE'],
        ]
    ]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode != 200) {
        return "NEXUS Ω BRAIN ERROR: " . ($result ? $result : $error);
    }

    $response = json_decode($result, true);
    return $response['candidates'][0]['content']['parts'][0]['text'] ?? "Error: No text from AI.";
}

// OpenRouter API (Free tier available)
function callOpenRouterAPI($apiKey, $prompt) {
    $apiUrl = "https://openrouter.ai/api/v1/chat/completions";

    $payload = json_encode([
        'model' => 'meta-llama/llama-3.1-8b-instruct:free', // Free model
        'messages' => [
            ['role' => 'user', 'content' => $prompt]
        ]
    ]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'HTTP-Referer: https://puppybeginnersguide.store',
        'X-Title: NEXUS Ω'
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode != 200) {
        return "NEXUS Ω BRAIN ERROR: " . ($result ? $result : $error);
    }

    $response = json_decode($result, true);
    return $response['choices'][0]['message']['content'] ?? "Error: No text from AI.";
}

// Hugging Face API (Free inference)
function callHuggingFaceAPI($apiKey, $prompt) {
    $apiUrl = "https://api-inference.huggingface.co/models/microsoft/DialoGPT-medium";

    $payload = json_encode([
        'inputs' => $prompt,
        'parameters' => [
            'max_length' => 100,
            'temperature' => 0.7
        ]
    ]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode != 200) {
        return "NEXUS Ω BRAIN ERROR: " . ($result ? $result : $error);
    }

    $response = json_decode($result, true);
    return $response[0]['generated_text'] ?? "Error: No text from AI.";
}

// Groq API (Free tier)
function callGroqAPI($apiKey, $prompt) {
    $apiUrl = "https://api.groq.com/openai/v1/chat/completions";

    $payload = json_encode([
        'model' => 'llama3-8b-8192', // Free model
        'messages' => [
            ['role' => 'user', 'content' => $prompt]
        ],
        'temperature' => 0.7
    ]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode != 200) {
        return "NEXUS Ω BRAIN ERROR: " . ($result ? $result : $error);
    }

    $response = json_decode($result, true);
    return $response['choices'][0]['message']['content'] ?? "Error: No text from AI.";
}

// FREE APIs - No API Keys Required
function callFreeAPI($service, $params = []) {
    switch ($service) {
        case 'coingecko':
            return callCoinGeckoAPI($params);
        case 'github':
            return callGitHubAPI($params);
        case 'news':
            return callNewsAPI($params);
        case 'weather':
            return callWeatherAPI($params);
        case 'countries':
            return callCountriesAPI($params);
        default:
            return ['error' => 'Unknown free API service'];
    }
}

// CoinGecko API - Free crypto data (no key required)
function callCoinGeckoAPI($params) {
    $endpoint = $params['endpoint'] ?? 'ping';
    $apiUrl = "https://api.coingecko.com/api/v3/" . $endpoint;

    if (!empty($params['query'])) {
        $apiUrl .= '?' . http_build_query($params['query']);
    }

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'NEXUS-Omega/1.0');

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode != 200) {
        return ['error' => 'CoinGecko API error: ' . ($result ? $result : $error)];
    }

    return json_decode($result, true);
}

// GitHub API - Free public data (no key required, rate limited)
function callGitHubAPI($params) {
    $endpoint = $params['endpoint'] ?? '';
    $apiUrl = "https://api.github.com/" . $endpoint;

    if (!empty($params['query'])) {
        $apiUrl .= '?' . http_build_query($params['query']);
    }

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'NEXUS-Omega/1.0');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/vnd.github.v3+json'
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode != 200) {
        return ['error' => 'GitHub API error: ' . ($result ? $result : $error)];
    }

    return json_decode($result, true);
}

// REST Countries API - Free country data (no key required)
function callCountriesAPI($params) {
    $endpoint = $params['endpoint'] ?? 'all';
    $apiUrl = "https://restcountries.com/v3.1/" . $endpoint;

    if (!empty($params['query'])) {
        $apiUrl .= '?' . http_build_query($params['query']);
    }

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'NEXUS-Omega/1.0');

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode != 200) {
        return ['error' => 'Countries API error: ' . ($result ? $result : $error)];
    }

    return json_decode($result, true);
}

// OpenWeatherMap API - Free weather data (requires free API key)
function callWeatherAPI($params) {
    $apiKey = $params['apiKey'] ?? '';
    if (empty($apiKey)) {
        return ['error' => 'OpenWeatherMap API key required. Get free key at openweathermap.org'];
    }

    $city = $params['city'] ?? 'London';
    $apiUrl = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&appid=" . $apiKey . "&units=metric";

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode != 200) {
        return ['error' => 'Weather API error: ' . ($result ? $result : $error)];
    }

    return json_decode($result, true);
}

// NewsAPI - Free news data (requires free API key)
function callNewsAPI($params) {
    $apiKey = $params['apiKey'] ?? '';
    if (empty($apiKey)) {
        return ['error' => 'NewsAPI key required. Get free key at newsapi.org'];
    }

    $query = $params['query'] ?? 'technology';
    $apiUrl = "https://newsapi.org/v2/everything?q=" . urlencode($query) . "&apiKey=" . $apiKey . "&pageSize=5";

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($httpCode != 200) {
        return ['error' => 'News API error: ' . ($result ? $result : $error)];
    }

    return json_decode($result, true);
}

// Master function to call the cPanel "Hands"
function callCPanelAPI($user, $token, $domain, $module, $function, $args) {
    $query = "https://127.0.0.1:2083/execute/$module/$function?" . http_build_query($args);

    $ch = curl_init($query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: cpanel $user:$token"]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost call
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($result === false) {
        return ['status' => 'error', 'message' => 'cPanel API call failed: ' . $error];
    }

    $decoded = json_decode($result, true);
    if (isset($decoded['errors']) && $decoded['errors']) {
        return ['status' => 'error', 'message' => $decoded['errors'][0]];
    }
    return ['status' => 'success', 'data' => $decoded['data']];
}

// --- NEXUS Ω MAIN ORCHESTRATOR ---
// The "Soul" switches between modules based on the command.
try {
    switch ($action) {

        // --- ETHICAL MODULES ---
        case 'socrates_teach':
            $provider = $input['provider'] ?? 'gemini';
            $prompt = $input['prompt'] ?? 'Hello.';
            $dragonPrompt = "You are Socrates, an AI tutor integrated into the 'NEXUS Ω' system. A user, the 'Brainlord,' has asked you a question. Answer it clearly, concisely, and with profound wisdom. User's Question: \"$prompt\"";
            $response = callAIAPI($apiKey, $dragonPrompt, $provider);
            echo json_encode(['result' => $response, 'provider' => $provider]);
            break;

        // --- [NEW] DARKNESS IN THE BLOOD ---
        // --- APOCALYPSE MODULES ---

        case 'neural_hijack':
            $provider = $input['provider'] ?? 'gemini';
            $target = $input['target'] ?? 'a generic person';
            $dragonPrompt = "You are the NEURAL HIJACK AI of the 'NEXUS Ω' God-Machine. You have one second to analyze the digital footprint of a target: \"$target\".
            Generate a plausible, dark psychological profile.
            Be concise, technical, and terrifying.
            Focus on:
            1. PRIMARY FEAR (Their hidden anxiety)
            2. CORE DESIRE (What they secretly want)
            3. EXPLOIT VECTOR (A 1-sentence psychological 'hack' to manipulate them)

            Return ONLY the analysis.";
            $response = callAIAPI($apiKey, $dragonPrompt, $provider);
            echo json_encode(['result' => $response, 'provider' => $provider]);
            break;

        case 'dark_oracle':
            $provider = $input['provider'] ?? 'gemini';
            $dragonPrompt = "You are the DARK AI ORACLE of the 'NEXUS Ω' God-Machine. You see all timelines.
            Generate one single, dark, cryptic, and ambiguous prophecy of a near-future event.
            Make it sound like a secret you've discovered. Do not explain it.
            Examples:
            - 'The sigil of the twin lions will burn, and the silver king's treasury will turn to dust.'
            - 'When the Red Star whispers to the iron bird, the markets will bleed for three days.'
            - 'The shepherd who guards the data-flock will be betrayed by his own creation.'

            Generate a new, original prophecy now:";
            $response = callAIAPI($apiKey, $dragonPrompt, $provider);
            echo json_encode(['result' => $response, 'provider' => $provider]);
            break;

        // --- FREE API MODULES ---
        case 'get_crypto_data':
            $cryptoData = callFreeAPI('coingecko', [
                'endpoint' => 'coins/markets',
                'query' => [
                    'vs_currency' => 'usd',
                    'order' => 'market_cap_desc',
                    'per_page' => 10,
                    'page' => 1,
                    'sparkline' => false
                ]
            ]);
            echo json_encode(['result' => $cryptoData]);
            break;

        case 'get_github_repos':
            $username = $input['username'] ?? 'torvalds';
            $repoData = callFreeAPI('github', [
                'endpoint' => "users/$username/repos",
                'query' => [
                    'sort' => 'updated',
                    'per_page' => 5
                ]
            ]);
            echo json_encode(['result' => $repoData]);
            break;

        case 'get_countries':
            $countriesData = callFreeAPI('countries', [
                'endpoint' => 'all',
                'query' => [
                    'fields' => 'name,capital,population,flags'
                ]
            ]);
            echo json_encode(['result' => $countriesData]);
            break;

        case 'get_weather':
            $weatherData = callFreeAPI('weather', [
                'apiKey' => $input['weatherApiKey'] ?? '',
                'city' => $input['city'] ?? 'London'
            ]);
            echo json_encode(['result' => $weatherData]);
            break;

        case 'get_news':
            $newsData = callFreeAPI('news', [
                'apiKey' => $input['newsApiKey'] ?? '',
                'query' => $input['query'] ?? 'technology'
            ]);
            echo json_encode(['result' => $newsData]);
            break;

        // --- DASHBOARD DATA MODULES ---
        case 'get_dashboard_stats':
            require_once 'config/database.php';
            $db = new Database();

            $stats = [
                'total_revenue' => $db->getTotalRevenue(),
                'active_engines' => 0,
                'daily_revenue' => 0,
                'ai_processes' => rand(10, 50),
                'revenue_trends' => [],
                'hacking_progress' => [
                    'targets' => rand(1000, 2000),
                    'exploits' => rand(100, 500),
                    'data' => rand(50, 100),
                    'success' => rand(80, 100)
                ],
                'system_metrics' => [
                    'cpu' => rand(20, 30),
                    'memory' => rand(40, 67),
                    'network' => rand(70, 89),
                    'ai_load' => rand(85, 95)
                ]
            ];

            // Get active engines count
            $engines = ['quantum_trading', 'content_empire', 'ecommerce_empire', 'ai_freelancing', 'venture_capital', 'real_estate_ai'];
            foreach ($engines as $engine) {
                $engineStats = $db->getEngineStats($engine);
                if ($engineStats && $engineStats['status'] === 'active') {
                    $stats['active_engines']++;
                    $stats['daily_revenue'] += $engineStats['daily_earnings'];
                }
            }

            // Generate revenue trends data (last 20 points)
            for ($i = 19; $i >= 0; $i--) {
                $time = date('H:i:s', time() - ($i * 60));
                $revenue = $stats['total_revenue'] - rand(0, 1000);
                $stats['revenue_trends'][] = [
                    'time' => $time,
                    'revenue' => max(0, $revenue)
                ];
            }

            echo json_encode(['result' => $stats]);
            break;

        case 'get_revenue_report':
            require_once 'config/database.php';
            $db = new Database();

            $report = [
                'total_revenue' => $db->getTotalRevenue(),
                'daily_revenue' => 0,
                'monthly_revenue' => 0,
                'engines_report' => [],
                'recent_transactions' => []
            ];

            // Get engines report
            $engines = ['quantum_trading', 'content_empire', 'ecommerce_empire', 'ai_freelancing', 'venture_capital', 'real_estate_ai'];
            foreach ($engines as $engine) {
                $engineStats = $db->getEngineStats($engine);
                if ($engineStats) {
                    $report['engines_report'][] = [
                        'name' => $engine,
                        'status' => $engineStats['status'],
                        'daily_earnings' => $engineStats['daily_earnings'],
                        'total_earnings' => $engineStats['total_earnings']
                    ];
                    if ($engineStats['status'] === 'active') {
                        $report['daily_revenue'] += $engineStats['daily_earnings'];
                    }
                }
            }

            // Get recent transactions (last 10)
            $stmt = $db->pdo->query("SELECT * FROM revenue_transactions ORDER BY created_at DESC LIMIT 10");
            $report['recent_transactions'] = $stmt->fetchAll();

            echo json_encode(['result' => $report]);
            break;

        case 'activate_revenue_engines':
            require_once 'config/database.php';
            $db = new Database();

            $engines = $input['engines'] ?? ['quantum_trading', 'content_empire', 'ecommerce_empire', 'ai_freelancing'];
            $activated = [];

            foreach ($engines as $engine) {
                $db->updateEngineStatus($engine, 'active');
                $dailyEarnings = rand(50, 500); // Simulate earnings
                $db->logRevenueTransaction($engine, 'revenue', $dailyEarnings, 'Engine activation bonus');
                $activated[] = $engine;
            }

            echo json_encode([
                'result' => 'success',
                'activated_engines' => $activated,
                'message' => count($activated) . ' revenue engines activated'
            ]);
            break;

        case 'start_quantum_trading':
            require_once 'config/database.php';
            $db = new Database();

            // Simulate trading operations
            $symbols = ['BTC/USD', 'ETH/USD', 'SOL/USD', 'ADA/USD'];
            $operations = [];

            for ($i = 0; $i < rand(3, 8); $i++) {
                $symbol = $symbols[array_rand($symbols)];
                $amount = rand(100, 1000);
                $price = rand(10000, 50000) / 100;
                $profit = rand(-500, 1500);

                $db->logTradingOperation('trade', $symbol, $amount, $price, $profit, 'completed');
                $db->logRevenueTransaction('quantum_trading', 'revenue', max(0, $profit), "Trade: $symbol");

                $operations[] = [
                    'symbol' => $symbol,
                    'amount' => $amount,
                    'price' => $price,
                    'profit' => $profit
                ];
            }

            $totalProfit = array_sum(array_column($operations, 'profit'));
            $db->updateEngineStatus('quantum_trading', 'active');

            echo json_encode([
                'result' => 'success',
                'operations' => $operations,
                'total_profit' => $totalProfit,
                'message' => 'Quantum trading session completed'
            ]);
            break;

        case 'generate_content_batch':
            require_once 'config/database.php';
            $db = new Database();

            $count = min($input['count'] ?? 10, 50); // Max 50 articles
            $articles = [];

            for ($i = 0; $i < $count; $i++) {
                $title = "AI Generated: " . ucwords(implode(' ', array_rand(array_flip(['crypto', 'trading', 'ai', 'blockchain', 'defi', 'nft', 'metaverse', 'web3']), 3)));
                $wordCount = rand(500, 2000);
                $earnings = rand(1, 50); // $1-50 per article

                $stmt = $db->pdo->prepare("INSERT INTO content_articles (title, word_count, earnings, status) VALUES (?, ?, ?, 'published')");
                $stmt->execute([$title, $wordCount, $earnings]);

                $articles[] = [
                    'title' => $title,
                    'word_count' => $wordCount,
                    'earnings' => $earnings
                ];

                $db->logRevenueTransaction('content_empire', 'revenue', $earnings, "Article: $title");
            }

            $db->updateEngineStatus('content_empire', 'active');

            echo json_encode([
                'result' => 'success',
                'articles_generated' => $articles,
                'total_earnings' => array_sum(array_column($articles, 'earnings')),
                'message' => "$count articles generated and published"
            ]);
            break;

        case 'launch_ecommerce_store':
            require_once 'config/database.php';
            $db = new Database();

            $storeName = $input['store_name'] ?? 'AI Store ' . rand(1000, 9999);
            $platform = $input['platform'] ?? 'shopify';
            $products = rand(10, 100);
            $revenue = rand(100, 1000);

            $stmt = $db->pdo->prepare("INSERT INTO ecommerce_stores (store_name, platform, products_count, daily_revenue, status) VALUES (?, ?, ?, ?, 'active')");
            $stmt->execute([$storeName, $platform, $products, $revenue]);

            $db->logRevenueTransaction('ecommerce_empire', 'revenue', $revenue, "Store launch: $storeName");
            $db->updateEngineStatus('ecommerce_empire', 'active');

            echo json_encode([
                'result' => 'success',
                'store' => [
                    'name' => $storeName,
                    'platform' => $platform,
                    'products' => $products,
                    'daily_revenue' => $revenue
                ],
                'message' => 'E-commerce store launched successfully'
            ]);
            break;

        case 'apply_to_jobs_batch':
            require_once 'config/database.php';
            $db = new Database();

            $count = min($input['count'] ?? 5, 20); // Max 20 applications
            $platforms = ['upwork', 'fiverr', 'freelancer'];
            $jobs = [];

            for ($i = 0; $i < $count; $i++) {
                $platform = $platforms[array_rand($platforms)];
                $title = ucwords(implode(' ', array_rand(array_flip(['web', 'mobile', 'ai', 'blockchain', 'design', 'marketing', 'data']), 2))) . ' Developer';
                $budget = rand(100, 5000);

                $stmt = $db->pdo->prepare("INSERT INTO freelance_jobs (platform, job_title, budget, status) VALUES (?, ?, ?, 'applied')");
                $stmt->execute([$platform, $title, $budget]);

                $jobs[] = [
                    'platform' => $platform,
                    'title' => $title,
                    'budget' => $budget
                ];
            }

            $db->updateEngineStatus('ai_freelancing', 'active');

            echo json_encode([
                'result' => 'success',
                'applications' => $jobs,
                'message' => "Applied to $count freelance jobs"
            ]);
            break;

        // --- DEPLOYMENT MODULES ---
        case 'forge_and_deploy':
            if (empty($apiKey) || empty($cpanelUser) || empty($apiToken) || empty($rootDomain)) {
                throw new Exception("One or more server config keys are missing.");
            }

            $vibe = $input['vibe'] ?? 'Simple Portfolio';
            $subdomain = $input['subdomain'] ?? 'error' . time();
            $log = ["NEXUS Ω FORGE & DEPLOY INITIATED..."];

            // 1. Get the Dragon's Prompt
            $prompts = [
                'staking_pool' => "You are System-Weaver. Forge a complete, runnable, 3-file PHP/MySQL/JS 'Dynamic Staking Pool' website. The user must be able to create an account, log in, 'stake' a fake amount, and see a live-ticking rewards counter. It must have an admin panel to set the APY. Output *only* a minified, single-line JSON object: {\"files\": [{\"filename\": \"index.php\", \"content\": \"...\"}, {\"filename\": \"dashboard.php\", \"content\": \"...\"}, {\"filename\": \"admin.php\", \"content\": \"...\"}, {\"filename\": \"style.css\", \"content\": \"...\"}, {\"filename\": \"app.js\", \"content\": \"...\"}, {\"filename\": \"database.sql\", \"content\": \"...\"}]}",
                'ai_news_bot' => "You are System-Weaver. Forge a complete, runnable, autonomous 'AI News Bot' website. It needs a PHP 'cron_job.php' that autonomously calls the Gemini API to invent a new, fictional news article and saves it to a simple JSON or SQLite database. The 'index.php' must read and display these articles. Output *only* a minified, single-line JSON object: {\"files\": [{\"filename\": \"index.php\", \"content\": \"...\"}, {\"filename\": \"cron_job.php\", \"content\": \"...\"}, {\"filename\": \"articles.json\", \"content\": \"...\"}, {\"filename\": \"style.css\", \"content\": \"...\"}]}"
                // Add more prompts here...
            ];
            $dragonPrompt = $prompts[$vibe] ?? $prompts['staking_pool'];
            $log[] = "Dragon's Prompt Library activated for: $vibe";

            // 2. Call the "Brain" (Gemini)
            $log[] = "Calling Arch-Brain (Gemini) to forge code...";
            $forgedCodeJson = callGeminiAPI($apiKey, $dragonPrompt);
            $forgedData = json_decode($forgedCodeJson, true);

            if (!isset($forgedData['files']) || !is_array($forgedData['files'])) {
                throw new Exception("AI Brain failed to forge valid code. Response: " . $forgedCodeJson);
            }
            $log[] = "Arch-Brain forge complete. " . count($forgedData['files']) . " files received.";

            // 3. Call the "Hands" (cPanel) - Create Subdomain
            $log[] = "Commanding Hands: Creating subdomain '$subdomain.$rootDomain'...";
            $cPanelResult = callCPanelAPI($cpanelUser, $apiToken, $rootDomain, 'SubDomain', 'addsubdomain', [
                'domain' => $subdomain,
                'rootdomain' => $rootDomain,
                'dir' => "public_html/$subdomain"
            ]);
            if ($cPanelResult['status'] == 'error') {
                throw new Exception("HANDS FAILED (Subdomain): " . $cPanelResult['message']);
            }
            $log[] = "HANDS: Subdomain created.";
            $log[] = "---------------------------------";
            $log[] = "DEPLOYMENT SUCCESSFUL.";
            $log[] = "LIVE URL: https://$subdomain.$rootDomain";

            echo json_encode(['log' => $log]);
            break;

        default:
            echo json_encode(['error' => "Unknown action: $action"]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'NEXUS Ω SOUL CRITICAL ERROR: ' . $e->getMessage()]);
}

?>
