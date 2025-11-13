<?php
// NEXUS Ω REVENUE CONFIGURATION
return [
    'trading' => [
        'starting_capital' => getenv('STARTING_TRADING_CAPITAL') ?: 1000,
        'max_risk_per_trade' => getenv('MAX_RISK_PER_TRADE') ?: 0.02,
        'exchanges' => ['binance', 'kraken', 'alpaca', 'coinbase'],
        'strategies' => ['arbitrage', 'market_making', 'momentum', 'scalping'],
        'auto_reinvest' => getenv('AUTO_REINVEST_PERCENT') ?: 0.8,
        'min_profit_threshold' => 0.005, // 0.5%
        'max_daily_trades' => 100
    ],

    'content' => [
        'daily_target' => getenv('CONTENT_DAILY_TARGET') ?: 50,
        'platforms' => ['adsense', 'media_net', 'ezoic', 'youtube', 'blog'],
        'topics' => [
            'Bitcoin Price Prediction', 'AI Technology Trends', 'Cryptocurrency News',
            'Stock Market Analysis', 'Real Estate Investing', 'Digital Marketing Tips',
            'Python Programming Tutorial', 'Web Development Trends', 'Mobile App Development',
            'E-commerce Strategies', 'Personal Finance Tips', 'Health and Wellness'
        ],
        'min_word_count' => 800,
        'max_word_count' => 2500,
        'seo_score_target' => 70,
        'auto_publish' => true
    ],

    'ecommerce' => [
        'store_target' => getenv('ECOMMERCE_STORE_TARGET') ?: 10,
        'platforms' => ['shopify', 'woocommerce', 'amazon'],
        'product_categories' => [
            'Electronics', 'Fashion', 'Home & Garden', 'Sports', 'Books',
            'Health & Beauty', 'Automotive', 'Toys', 'Food', 'Pet Supplies'
        ],
        'min_margin' => 0.3, // 30%
        'max_stores_per_platform' => 5,
        'auto_optimize' => true,
        'marketing_budget_percent' => 0.1 // 10% of revenue
    ],

    'freelance' => [
        'platforms' => ['upwork', 'fiverr', 'freelancer'],
        'skills' => [
            'Web Development', 'Mobile App Development', 'AI Programming',
            'Data Analysis', 'Content Writing', 'Digital Marketing',
            'Graphic Design', 'Video Editing', 'SEO Optimization'
        ],
        'min_hourly_rate' => 25,
        'max_hourly_rate' => 150,
        'auto_apply' => true,
        'response_target_percent' => 0.15, // 15%
        'completion_rate_target' => 0.95 // 95%
    ],

    'venture_capital' => [
        'investment_range' => [10000, 100000],
        'industries' => [
            'AI/ML', 'Blockchain', 'Fintech', 'Healthtech', 'Edtech',
            'E-commerce', 'SaaS', 'IoT', 'Clean Energy', 'Space Tech'
        ],
        'stages' => ['pre-seed', 'seed', 'series-a'],
        'min_roi_expectation' => 5.0, // 5x return
        'max_portfolio_companies' => 20,
        'auto_invest' => false // Manual approval required
    ],

    'real_estate' => [
        'investment_range' => [50000, 500000],
        'property_types' => ['residential', 'commercial', 'industrial', 'land'],
        'locations' => ['primary', 'secondary', 'tertiary'],
        'min_cap_rate' => 0.06, // 6%
        'max_loan_to_value' => 0.75, // 75%
        'auto_purchase' => false, // Manual approval required
        'rental_yield_target' => 0.08 // 8%
    ],

    'apis' => [
        'free_apis' => [
            'huggingface' => [
                'key' => getenv('HUGGINGFACE_API_KEY'),
                'models' => ['text-generation', 'sentiment-analysis', 'summarization']
            ],
            'openrouter' => [
                'key' => getenv('OPENROUTER_API_KEY'),
                'models' => ['gpt-3.5-turbo', 'claude-2', 'palm-2']
            ]
        ],
        'premium_apis' => [
            'openai' => getenv('OPENAI_API_KEY'),
            'anthropic' => getenv('ANTHROPIC_API_KEY'),
            'binance' => [
                'key' => getenv('BINANCE_API_KEY'),
                'secret' => getenv('BINANCE_SECRET_KEY')
            ]
        ]
    ],

    'automation' => [
        'enabled' => true,
        'check_interval' => 300, // 5 minutes
        'max_concurrent_operations' => 10,
        'error_retry_attempts' => 3,
        'notification_email' => getenv('NOTIFICATION_EMAIL'),
        'maintenance_mode' => false
    ],

    'security' => [
        'encryption_key' => getenv('ENCRYPTION_KEY'),
        'jwt_secret' => getenv('JWT_SECRET'),
        'rate_limiting' => [
            'max_requests_per_minute' => 60,
            'max_requests_per_hour' => 1000
        ],
        'ip_whitelist' => [], // Add allowed IPs
        'two_factor_required' => false
    ],

    'reporting' => [
        'daily_reports' => true,
        'weekly_reports' => true,
        'monthly_reports' => true,
        'export_formats' => ['csv', 'pdf', 'json'],
        'dashboard_refresh_interval' => 30 // seconds
    ]
];
?>
