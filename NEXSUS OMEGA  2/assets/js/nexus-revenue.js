// NEXUS Ω REVENUE SYSTEM JAVASCRIPT
class NexusRevenueSystem {
    constructor() {
        this.currentTab = 'dashboard';
        this.revenueData = {
            daily: 0,
            total: 0,
            engines: {
                trading: { status: 'offline', revenue: 0 },
                content: { status: 'offline', revenue: 0 },
                ecommerce: { status: 'offline', revenue: 0 },
                freelance: { status: 'offline', revenue: 0 },
                defi: { status: 'offline', revenue: 0 },
                nft: { status: 'offline', revenue: 0 },
                darkweb: { status: 'offline', revenue: 0 },
                hacking: { status: 'offline', revenue: 0 },
                venture: { status: 'offline', revenue: 0 },
                realestate: { status: 'offline', revenue: 0 }
            }
        };
        this.init();
    }

    init() {
        this.bindEvents();
        this.updateRevenueDisplay();
        this.startRevenueUpdates();
        this.loadDashboardStats(); // Load real data from backend
    }

    bindEvents() {
        // Navigation
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.openTab(e.target.textContent.toLowerCase().replace(' ', ''));
            });
        });

        // Dashboard Actions
        document.querySelectorAll('.super-btn, .action-btn, .engine-btn, .quick-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.handleAction(e.target);
            });
        });
    }

    openTab(tabName) {
        // Update navigation
        document.querySelectorAll('.nav-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');

        // Update content
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.getElementById(tabName).classList.add('active');

        this.currentTab = tabName;
    }

    handleAction(button) {
        const action = button.getAttribute('data-action') || button.textContent.toLowerCase().replace(/[^a-z]/g, '');
        const output = document.querySelector(`#${this.currentTab} .output`);

        if (output) {
            output.textContent = 'Processing request...';
            button.classList.add('loading');
        }

        // Make real API call
        this.makeAPICall(action)
            .then(response => {
                if (output) {
                    if (response.result && typeof response.result === 'object') {
                        output.textContent = JSON.stringify(response.result, null, 2);
                    } else {
                        output.textContent = response.result || 'Command executed successfully';
                    }
                }
                this.updateRevenueData(response);
                this.updateUI();
                // Reload dashboard stats after action
                this.loadDashboardStats();
                button.classList.remove('loading');
            })
            .catch(error => {
                if (output) {
                    output.textContent = 'Error: ' + error.message;
                }
                button.classList.remove('loading');
            });
    }

    async makeAPICall(action, params = {}) {
        try {
            const response = await fetch('nexus_controller.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    ...params
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API call failed:', error);
            return {
                success: false,
                error: error.message
            };
        }
    }

    updateRevenueData(response) {
        if (response.success && response.revenue_engines) {
            this.revenueData.daily = response.total_daily_revenue || 0;
            this.revenueData.total += response.total_daily_revenue || 0;

            Object.keys(response.revenue_engines).forEach(engine => {
                const engineData = response.revenue_engines[engine];
                const engineKey = engine.replace('_', '');

                if (this.revenueData.engines[engineKey]) {
                    this.revenueData.engines[engineKey].status = 'online';
                    this.revenueData.engines[engineKey].revenue = engineData.daily_profit ||
                                                                 engineData.daily_earnings ||
                                                                 engineData.daily_sales || 0;
                }
            });
        }
    }

    // Load dashboard stats from backend
    async loadDashboardStats() {
        try {
            const response = await this.makeAPICall('get_dashboard_stats');
            if (response.result) {
                const stats = response.result;
                this.revenueData.total = stats.total_revenue || 0;
                this.revenueData.daily = stats.daily_revenue || 0;

                // Update active engines count
                let activeCount = 0;
                Object.keys(this.revenueData.engines).forEach(engine => {
                    const engineName = engine.replace('ai', '_ai').replace('defi', '_defi');
                    const engineStats = stats.engines_report?.find(e => e.name === engineName);
                    if (engineStats && engineStats.status === 'active') {
                        this.revenueData.engines[engine].status = 'online';
                        this.revenueData.engines[engine].revenue = engineStats.daily_earnings || 0;
                        activeCount++;
                    }
                });

                // Update UI with real data
                this.updateRevenueDisplay();
                this.updateEngineCards();

                // Update dashboard widgets with real data
                this.updateDashboardWidgets(stats);
            }
        } catch (error) {
            console.error('Failed to load dashboard stats:', error);
        }
    }

    // Update dashboard widgets with backend data
    updateDashboardWidgets(backendStats = null) {
        if (backendStats) {
            // Update counters with real data
            if (backendStats.ai_processes !== undefined) {
                document.getElementById('ai-processes-counter').textContent = backendStats.ai_processes;
            }

            // Update hacking progress chart
            if (backendStats.hacking_progress && window.hackingChart) {
                const hp = backendStats.hacking_progress;
                window.hackingChart.data.datasets[0].data = [hp.targets, hp.exploits, hp.data, hp.success];
                window.hackingChart.update();
            }

            // Update system metrics
            if (backendStats.system_metrics) {
                const sm = backendStats.system_metrics;
                document.getElementById('cpu-fill').style.width = sm.cpu + '%';
                document.querySelector('#cpu-fill').nextElementSibling.textContent = sm.cpu + '%';
                document.getElementById('memory-fill').style.width = sm.memory + '%';
                document.querySelector('#memory-fill').nextElementSibling.textContent = sm.memory + '%';
                document.getElementById('network-fill').style.width = sm.network + '%';
                document.querySelector('#network-fill').nextElementSibling.textContent = sm.network + '%';
                document.getElementById('ai-fill').style.width = sm.ai_load + '%';
                document.querySelector('#ai-fill').nextElementSibling.textContent = sm.ai_load + '%';
            }

            // Update revenue trends chart
            if (backendStats.revenue_trends && window.revenueChart) {
                const trends = backendStats.revenue_trends;
                window.revenueChart.data.labels = trends.map(t => t.time);
                window.revenueChart.data.datasets[0].data = trends.map(t => t.revenue);
                window.revenueChart.update();
            }
        } else {
            // Fallback to simulated updates
            updateCounters();
            updateLiveCode();
            updateTerminal();
            updateDataStreams();
            updateSystemMetrics();
            updateCharts();
        }
    }

    updateUI() {
        this.updateRevenueDisplay();
        this.updateEngineCards();
    }

    updateRevenueDisplay() {
        const dailyEl = document.getElementById('daily-revenue');
        const totalEl = document.getElementById('total-revenue');
        const totalMetricEl = document.getElementById('total-revenue-metric');
        const activeEnginesEl = document.getElementById('active-engines');

        if (dailyEl) {
            dailyEl.textContent = '$' + this.revenueData.daily.toLocaleString();
        }
        if (totalEl) {
            totalEl.textContent = '$' + this.revenueData.total.toLocaleString();
        }
        if (totalMetricEl) {
            totalMetricEl.textContent = '$' + this.revenueData.total.toLocaleString();
        }
        if (activeEnginesEl) {
            const activeCount = Object.values(this.revenueData.engines).filter(e => e.status === 'online').length;
            activeEnginesEl.textContent = activeCount + '/10';
        }
    }

    updateEngineCards() {
        Object.keys(this.revenueData.engines).forEach(engine => {
            const card = document.getElementById(`${engine}-card`);
            if (card) {
                const statusEl = card.querySelector('.status');
                const revenueEl = card.querySelector('.revenue-amount');

                if (statusEl) {
                    statusEl.className = 'status ' + (this.revenueData.engines[engine].status === 'online' ? 'online' : 'offline');
                    statusEl.textContent = this.revenueData.engines[engine].status === 'online' ? '🟢 ONLINE' : '🔴 OFFLINE';
                }

                if (revenueEl) {
                    revenueEl.textContent = '$' + this.revenueData.engines[engine].revenue + '/day';
                }
            }
        });
    }

    startRevenueUpdates() {
        setInterval(() => {
            const activeEngines = Object.values(this.revenueData.engines).filter(e => e.status === 'online').length;
            if (activeEngines > 0) {
                // Scale revenue based on number of active engines
                const baseRevenue = Math.floor(Math.random() * 100) * activeEngines;
                const bonusRevenue = activeEngines >= 5 ? Math.floor(Math.random() * 500) : 0; // Empire bonus
                const totalIncrement = baseRevenue + bonusRevenue;

                this.revenueData.daily += totalIncrement;
                this.revenueData.total += totalIncrement;
                this.updateRevenueDisplay();
            }
        }, 3000); // Faster updates for more intense revenue generation
    }
}

// Initialize the system when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.nexusRevenue = new NexusRevenueSystem();

    // Initialize dashboard widgets
    initializeDashboard();
});

// Initialize dashboard widgets
function initializeDashboard() {
    // Initialize charts
    initializeCharts();

    // Start live updates with backend data
    setInterval(() => {
        if (window.nexusRevenue) {
            window.nexusRevenue.loadDashboardStats();
        } else {
            updateDashboardWidgets(); // Fallback
        }
    }, 5000); // Update every 5 seconds with real data
}

// Initialize Chart.js charts
function initializeCharts() {
    // Revenue Trends Chart
    const revenueCtx = document.getElementById('revenue-chart').getContext('2d');
    window.revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Revenue ($)',
                data: [],
                borderColor: '#00ff00',
                backgroundColor: 'rgba(0, 255, 0, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#00ff00'
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#00ff88'
                    },
                    grid: {
                        color: '#00ff00'
                    }
                },
                y: {
                    ticks: {
                        color: '#00ff88',
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: '#00ff00'
                    }
                }
            }
        }
    });

    // Hacking Progress Chart
    const hackingCtx = document.getElementById('hacking-chart').getContext('2d');
    window.hackingChart = new Chart(hackingCtx, {
        type: 'bar',
        data: {
            labels: ['Targets', 'Exploits', 'Data', 'Success'],
            datasets: [{
                label: 'Hacking Progress',
                data: [0, 0, 0, 0],
                backgroundColor: [
                    '#00ff00',
                    '#ff0000',
                    '#00ffff',
                    '#ffaa00'
                ],
                borderColor: '#00ff00',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#00ff00'
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#00ff88'
                    },
                    grid: {
                        color: '#00ff00'
                    }
                },
                y: {
                    ticks: {
                        color: '#00ff88'
                    },
                    grid: {
                        color: '#00ff00'
                    }
                }
            }
        }
    });

    // Crypto Prices Chart
    const cryptoCtx = document.getElementById('crypto-chart').getContext('2d');
    window.cryptoChart = new Chart(cryptoCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'BTC/USD',
                    data: [],
                    borderColor: '#ffaa00',
                    backgroundColor: 'rgba(255, 170, 0, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
                },
                {
                    label: 'ETH/USD',
                    data: [],
                    borderColor: '#00ffff',
                    backgroundColor: 'rgba(0, 255, 255, 0.1)',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#00ff00'
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#00ff88'
                    },
                    grid: {
                        color: '#00ff00'
                    }
                },
                y: {
                    ticks: {
                        color: '#00ff88',
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: '#00ff00'
                    }
                }
            }
        }
    });
}

// Update dashboard widgets
function updateDashboardWidgets() {
    // Update counters
    updateCounters();

    // Update live code
    updateLiveCode();

    // Update terminal
    updateTerminal();

    // Update data streams
    updateDataStreams();

    // Update system metrics
    updateSystemMetrics();

    // Update charts
    updateCharts();
}

// Update counters
function updateCounters() {
    const totalRevenue = parseFloat(document.getElementById('total-revenue').textContent.replace('$', '').replace(',', '')) || 0;
    const dailyRevenue = parseFloat(document.getElementById('daily-revenue').textContent.replace('$', '').replace(',', '')) || 0;

    document.getElementById('total-revenue-counter').textContent = '$' + totalRevenue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('daily-revenue-counter').textContent = '$' + dailyRevenue.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    // Count active engines
    const activeEngines = document.querySelectorAll('.status.online').length;
    document.getElementById('active-engines-counter').textContent = activeEngines + '/10';

    // Simulate AI processes
    const aiProcesses = Math.floor(Math.random() * 50) + 10;
    document.getElementById('ai-processes-counter').textContent = aiProcesses.toString();
}

// Update live code display
function updateLiveCode() {
    const codeLines = [
        '> Initializing NEXUS Ω Revenue Engine...',
        '> Loading AI Models: GPT-4, Claude, Custom Neural Networks',
        '> Connecting to 50+ APIs and Databases...',
        '> Scanning for Revenue Opportunities...',
        '> System Ready - Awaiting Commands'
    ];

    const randomLine = codeLines[Math.floor(Math.random() * codeLines.length)];
    document.getElementById('live-code').textContent = randomLine;
}

// Update terminal output
function updateTerminal() {
    const terminalLines = [
        'All systems operational - Revenue generation active',
        'Quantum trading algorithms initialized',
        'Content generation AI models loaded',
        'E-commerce automation bots deployed',
        'Freelance army recruitment active',
        'DeFi protocols connected',
        'NFT marketplaces scanned',
        'Dark web networks accessed',
        'Zero-day exploits deployed',
        'Venture capital opportunities analyzed',
        'Real estate databases searched'
    ];

    const randomStatus = terminalLines[Math.floor(Math.random() * terminalLines.length)];
    document.getElementById('terminal-status').textContent = randomStatus;
}

// Update data streams
function updateDataStreams() {
    // Simulate crypto prices
    const btcPrice = 43250 + (Math.random() - 0.5) * 1000;
    const ethPrice = 2650 + (Math.random() - 0.5) * 200;

    document.getElementById('btc-price').textContent = '$' + btcPrice.toFixed(2);
    document.getElementById('eth-price').textContent = '$' + ethPrice.toFixed(2);

    // Simulate hacking stats
    const targetsHacked = Math.floor(Math.random() * 2000) + 1000;
    const dataStolen = (Math.random() * 5 + 1).toFixed(1);
    const activeBots = Math.floor(Math.random() * 1000) + 500;
    const successRate = (Math.random() * 10 + 85).toFixed(1);

    document.getElementById('targets-hacked').textContent = targetsHacked.toLocaleString();
    document.getElementById('data-stolen').textContent = dataStolen + 'TB';
    document.getElementById('active-bots').textContent = activeBots.toLocaleString();
    document.getElementById('success-rate').textContent = successRate + '%';
}

// Update system metrics
function updateSystemMetrics() {
    const cpuUsage = Math.floor(Math.random() * 30) + 20;
    const memoryUsage = Math.floor(Math.random() * 30) + 40;
    const networkUsage = Math.floor(Math.random() * 20) + 70;
    const aiLoad = Math.floor(Math.random() * 10) + 85;

    document.getElementById('cpu-fill').style.width = cpuUsage + '%';
    document.querySelector('#cpu-fill').nextElementSibling.textContent = cpuUsage + '%';

    document.getElementById('memory-fill').style.width = memoryUsage + '%';
    document.querySelector('#memory-fill').nextElementSibling.textContent = memoryUsage + '%';

    document.getElementById('network-fill').style.width = networkUsage + '%';
    document.querySelector('#network-fill').nextElementSibling.textContent = networkUsage + '%';

    document.getElementById('ai-fill').style.width = aiLoad + '%';
    document.querySelector('#ai-fill').nextElementSibling.textContent = aiLoad + '%';
}

// Update charts
function updateCharts() {
    const now = new Date();
    const timeString = now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();

    // Update revenue chart
    if (window.revenueChart) {
        const revenueData = window.revenueChart.data.datasets[0].data;
        const revenueLabels = window.revenueChart.data.labels;

        if (revenueLabels.length > 20) {
            revenueLabels.shift();
            revenueData.shift();
        }

        revenueLabels.push(timeString);
        const currentRevenue = parseFloat(document.getElementById('total-revenue').textContent.replace('$', '').replace(',', '')) || 0;
        revenueData.push(currentRevenue);

        window.revenueChart.update();
    }

    // Update hacking chart
    if (window.hackingChart) {
        const hackingData = [
            Math.floor(Math.random() * 2000) + 1000, // Targets
            Math.floor(Math.random() * 500) + 100,   // Exploits
            Math.floor(Math.random() * 100) + 50,    // Data
            Math.floor(Math.random() * 100) + 80     // Success
        ];
        window.hackingChart.data.datasets[0].data = hackingData;
        window.hackingChart.update();
    }

    // Update crypto chart
    if (window.cryptoChart) {
        const cryptoLabels = window.cryptoChart.data.labels;
        const btcData = window.cryptoChart.data.datasets[0].data;
        const ethData = window.cryptoChart.data.datasets[1].data;

        if (cryptoLabels.length > 20) {
            cryptoLabels.shift();
            btcData.shift();
            ethData.shift();
        }

        cryptoLabels.push(timeString);
        btcData.push(43250 + (Math.random() - 0.5) * 1000);
        ethData.push(2650 + (Math.random() - 0.5) * 200);

        window.cryptoChart.update();
    }
}

// Global functions for HTML onclick handlers
function openTab(tabName) {
    if (window.nexusRevenue) {
        window.nexusRevenue.openTab(tabName);
    }
}

function activateAllRevenueEngines() {
    if (window.nexusRevenue) {
        const button = document.querySelector('.super-btn');
        if (button) window.nexusRevenue.handleAction(button);
    }
}

function getRevenueReport() {
    if (window.nexusRevenue) {
        const button = document.querySelector('.action-btn');
        if (button) window.nexusRevenue.handleAction(button);
    }
}

// New Empire Functions
function deployLiquidityPools() {
    if (window.nexusRevenue) {
        const button = document.querySelector('#defi .action-btn');
        if (button) window.nexusRevenue.handleAction(button);
    }
}

function executeFlashLoans() {
    if (window.nexusRevenue) {
        const buttons = document.querySelectorAll('#defi .action-btn');
        if (buttons[1]) window.nexusRevenue.handleAction(buttons[1]);
    }
}

function stakeGovernanceTokens() {
    if (window.nexusRevenue) {
        const buttons = document.querySelectorAll('#defi .action-btn');
        if (buttons[2]) window.nexusRevenue.handleAction(buttons[2]);
    }
}

function executeNFTFlips() {
    if (window.nexusRevenue) {
        const button = document.querySelector('#nft .action-btn');
        if (button) window.nexusRevenue.handleAction(button);
    }
}

function createNFTCollection() {
    if (window.nexusRevenue) {
        const buttons = document.querySelectorAll('#nft .action-btn');
        if (buttons[1]) window.nexusRevenue.handleAction(buttons[1]);
    }
}

function stakeNFTs() {
    if (window.nexusRevenue) {
        const buttons = document.querySelectorAll('#nft .action-btn');
        if (buttons[2]) window.nexusRevenue.handleAction(buttons[2]);
    }
}

function tradeStolenData() {
    if (window.nexusRevenue) {
        const button = document.querySelector('#darkweb .action-btn');
        if (button) window.nexusRevenue.handleAction(button);
    }
}

function launderCryptocurrency() {
    if (window.nexusRevenue) {
        const buttons = document.querySelectorAll('#darkweb .action-btn');
        if (buttons[1]) window.nexusRevenue.handleAction(buttons[1]);
    }
}

function operateDarkMarkets() {
    if (window.nexusRevenue) {
        const buttons = document.querySelectorAll('#darkweb .action-btn');
        if (buttons[2]) window.nexusRevenue.handleAction(buttons[2]);
    }
}

function provideAnonymousServices() {
    if (window.nexusRevenue) {
        const buttons = document.querySelectorAll('#darkweb .action-btn');
        if (buttons[3]) window.nexusRevenue.handleAction(buttons[3]);
    }
}

function deployZeroDayExploits() {
    if (window.nexusRevenue) {
        const button = document.querySelector('#hacking .action-btn');
        if (button) window.nexusRevenue.handleAction(button);
    }
}

function executeSocialEngineering() {
    if (window.nexusRevenue) {
        const buttons = document.querySelectorAll('#hacking .action-btn');
        if (buttons[1]) window.nexusRevenue.handleAction(buttons[1]);
    }
}

function breakCryptography() {
    if (window.nexusRevenue) {
        const buttons = document.querySelectorAll('#hacking .action-btn');
        if (buttons[2]) window.nexusRevenue.handleAction(buttons[2]);
    }
}

function deployNeuralAttacks() {
    if (window.nexusRevenue) {
        const buttons = document.querySelectorAll('#hacking .action-btn');
        if (buttons[3]) window.nexusRevenue.handleAction(buttons[3]);
    }
}

// NEXUS AI Functions with Provider Selection
function socratesTeach() {
    const apiKey = document.getElementById('api-key').value;
    const provider = document.getElementById('ai-provider').value;
    const prompt = document.getElementById('socrates-prompt').value;
    const output = document.getElementById('socrates-output');

    if (!apiKey) {
        output.textContent = 'ERROR: Please enter your API key first.';
        return;
    }

    if (!prompt.trim()) {
        output.textContent = 'ERROR: Please enter a question for Socrates.';
        return;
    }

    output.textContent = `Socrates is contemplating your question using ${provider.toUpperCase()}...`;

    fetch('nexus_controller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'socrates_teach',
            apiKey: apiKey,
            provider: provider,
            prompt: prompt
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.result) {
            output.textContent = data.result;
        } else if (data.error) {
            output.textContent = 'ERROR: ' + data.error;
        }
    })
    .catch(error => {
        output.textContent = 'ERROR: ' + error.message;
    });
}

function neuralHijack() {
    const apiKey = document.getElementById('api-key').value;
    const provider = document.getElementById('ai-provider').value;
    const target = document.getElementById('neural-target').value;
    const output = document.getElementById('neural-output');

    if (!apiKey) {
        output.textContent = 'ERROR: Please enter your API key first.';
        return;
    }

    if (!target.trim()) {
        output.textContent = 'ERROR: Please enter a target for neural hijack.';
        return;
    }

    output.textContent = `Analyzing digital footprint with ${provider.toUpperCase()}... Hijacking neural pathways...`;

    fetch('nexus_controller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'neural_hijack',
            apiKey: apiKey,
            provider: provider,
            target: target
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.result) {
            output.textContent = data.result;
        } else if (data.error) {
            output.textContent = 'ERROR: ' + data.error;
        }
    })
    .catch(error => {
        output.textContent = 'ERROR: ' + error.message;
    });
}

function darkOracle() {
    const apiKey = document.getElementById('api-key').value;
    const provider = document.getElementById('ai-provider').value;
    const output = document.getElementById('oracle-output');

    if (!apiKey) {
        output.textContent = 'ERROR: Please enter your API key first.';
        return;
    }

    output.textContent = `Consulting the Dark Oracle via ${provider.toUpperCase()}... Peering into forbidden timelines...`;

    fetch('nexus_controller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'dark_oracle',
            apiKey: apiKey,
            provider: provider
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.result) {
            output.textContent = data.result;
        } else if (data.error) {
            output.textContent = 'ERROR: ' + data.error;
        }
    })
    .catch(error => {
        output.textContent = 'ERROR: ' + error.message;
    });
}

// FREE API Functions - No API Keys Required
function getCryptoData() {
    const output = document.getElementById('crypto-output');
    output.textContent = 'Fetching live crypto data...';

    fetch('nexus_controller.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_crypto_data' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.result && Array.isArray(data.result)) {
            let html = '<h3>Top 10 Cryptocurrencies</h3>';
            data.result.forEach(coin => {
                html += `
                    <div class="crypto-item">
                        <img src="${coin.image}" alt="${coin.name}" width="32">
                        <strong>${coin.name} (${coin.symbol.toUpperCase()})</strong><br>
                        Price: $${coin.current_price.toLocaleString()}<br>
                        Market Cap: $${coin.market_cap.toLocaleString()}<br>
                        24h Change: <span style="color: ${coin.price_change_percentage_24h >= 0 ? 'green' : 'red'}">${coin.price_change_percentage_24h.toFixed(2)}%</span>
                    </div>
                `;
            });
            output.innerHTML = html;
        } else {
            output.textContent = 'Error fetching crypto data';
        }
    })
    .catch(error => {
        output.textContent = 'Error: ' + error.message;
    });
}

function getGitHubRepos() {
    const username = document.getElementById('github-username').value || 'torvalds';
    const output = document.getElementById('github-output');
    output.textContent = `Fetching GitHub repos for ${username}...`;

    fetch('nexus_controller.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'get_github_repos',
            username: username
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.result && Array.isArray(data.result)) {
            let html = `<h3>Latest Repos by ${username}</h3>`;
            data.result.forEach(repo => {
                html += `
                    <div class="repo-item">
                        <strong><a href="${repo.html_url}" target="_blank">${repo.name}</a></strong><br>
                        ${repo.description || 'No description'}<br>
                        ⭐ ${repo.stargazers_count} | 🍴 ${repo.forks_count}<br>
                        Language: ${repo.language || 'N/A'}
                    </div>
                `;
            });
            output.innerHTML = html;
        } else {
            output.textContent = 'Error fetching GitHub data';
        }
    })
    .catch(error => {
        output.textContent = 'Error: ' + error.message;
    });
}

function getCountries() {
    const output = document.getElementById('countries-output');
    output.textContent = 'Fetching country data...';

    fetch('nexus_controller.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'get_countries' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.result && Array.isArray(data.result)) {
            let html = '<h3>World Countries</h3>';
            // Show first 10 countries as example
            data.result.slice(0, 10).forEach(country => {
                html += `
                    <div class="country-item">
                        <img src="${country.flags?.png}" alt="${country.name?.common}" width="32">
                        <strong>${country.name?.common}</strong><br>
                        Capital: ${country.capital?.[0] || 'N/A'}<br>
                        Population: ${country.population?.toLocaleString() || 'N/A'}
                    </div>
                `;
            });
            html += '<p>... and ' + (data.result.length - 10) + ' more countries</p>';
            output.innerHTML = html;
        } else {
            output.textContent = 'Error fetching countries data';
        }
    })
    .catch(error => {
        output.textContent = 'Error: ' + error.message;
    });
}

function getWeather() {
    const city = document.getElementById('weather-city').value || 'London';
    const apiKey = document.getElementById('weather-api-key').value;
    const output = document.getElementById('weather-output');

    if (!apiKey) {
        output.textContent = 'Please enter your OpenWeatherMap API key (get free at openweathermap.org)';
        return;
    }

    output.textContent = `Getting weather for ${city}...`;

    fetch('nexus_controller.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'get_weather',
            weatherApiKey: apiKey,
            city: city
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.result && !data.result.error) {
            const weather = data.result;
            output.innerHTML = `
                <h3>Weather in ${weather.name}</h3>
                <div class="weather-item">
                    <strong>${weather.weather[0].main}</strong>: ${weather.weather[0].description}<br>
                    Temperature: ${weather.main.temp}°C<br>
                    Feels like: ${weather.main.feels_like}°C<br>
                    Humidity: ${weather.main.humidity}%<br>
                    Wind: ${weather.wind.speed} m/s
                </div>
            `;
        } else {
            output.textContent = data.result?.error || 'Error fetching weather data';
        }
    })
    .catch(error => {
        output.textContent = 'Error: ' + error.message;
    });
}

function getNews() {
    const query = document.getElementById('news-query').value || 'technology';
    const apiKey = document.getElementById('news-api-key').value;
    const output = document.getElementById('news-output');

    if (!apiKey) {
        output.textContent = 'Please enter your NewsAPI key (get free at newsapi.org)';
        return;
    }

    output.textContent = `Fetching news about ${query}...`;

    fetch('nexus_controller.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'get_news',
            newsApiKey: apiKey,
            query: query
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.result && data.result.articles) {
            let html = `<h3>Latest ${query} News</h3>`;
            data.result.articles.forEach(article => {
                html += `
                    <div class="news-item">
                        <strong><a href="${article.url}" target="_blank">${article.title}</a></strong><br>
                        ${article.description || 'No description'}<br>
                        <small>Source: ${article.source.name} | ${new Date(article.publishedAt).toLocaleDateString()}</small>
                    </div>
                `;
            });
            output.innerHTML = html;
        } else {
            output.textContent = data.result?.error || 'Error fetching news data';
        }
    })
    .catch(error => {
        output.textContent = 'Error: ' + error.message;
    });
}

function forgeAndDeploy() {
    const apiKey = document.getElementById('api-key').value;
    const vibe = document.getElementById('forge-vibe').value;
    const subdomain = document.getElementById('forge-subdomain').value;
    const output = document.getElementById('forge-output');

    if (!apiKey) {
        output.textContent = 'ERROR: Please enter your Gemini API key first.';
        return;
    }

    if (!subdomain.trim()) {
        output.textContent = 'ERROR: Please enter a subdomain name.';
        return;
    }

    output.textContent = 'Initializing NEXUS Ω Forge & Deploy sequence...\n\nStep 1: Activating AI Brain\nStep 2: Forging website code\nStep 3: Deploying to server\n\nPlease wait...';

    fetch('nexus_controller.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'forge_and_deploy',
            apiKey: apiKey,
            vibe: vibe,
            subdomain: subdomain
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.log) {
            output.textContent = data.log.join('\n');
        } else if (data.error) {
            output.textContent = 'DEPLOYMENT FAILED:\n\n' + data.error;
        }
    })
    .catch(error => {
        output.textContent = 'DEPLOYMENT FAILED:\n\n' + error.message;
    });
}

// Add some visual effects
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('mouseenter', () => {
            btn.style.boxShadow = '0 0 20px rgba(0, 255, 136, 0.3)';
        });
        btn.addEventListener('mouseleave', () => {
            btn.style.boxShadow = '';
        });
    });
});
