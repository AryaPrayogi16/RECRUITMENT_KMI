/**
 * Enhanced Kraeplin Chart Module
 * Handles all Kraeplin test result visualization with better error handling
 */

class KraeplinChart {
    constructor(testResult) {
        this.testResult = testResult || window.kraeplinTestData;
        this.currentChart = null;
        this.chartData = null;
        this.chartConfigs = null;
        
        // Validate data availability
        if (!this.testResult) {
            console.error('No Kraeplin test data available');
            this.showError('Data tidak tersedia');
            return;
        }
        
        // Parse and validate chart data
        try {
            this.chartData = this.parseChartData();
            this.chartConfigs = this.buildChartConfigs();
        } catch (error) {
            console.error('Error parsing Kraeplin data:', error);
            this.showError('Error parsing data: ' + error.message);
            return;
        }
    }

    parseChartData() {
        console.log('=== PARSING KRAEPLIN DATA ===');
        console.log('Raw test result:', this.testResult);
        
        let correctCount = this.testResult.column_correct_count;
        let answeredCount = this.testResult.column_answered_count;
        let avgTime = this.testResult.column_avg_time;
        let accuracy = this.testResult.column_accuracy;
        
        // Enhanced parsing with better error handling
        try {
            // Parse JSON strings if needed
            if (typeof correctCount === 'string') {
                correctCount = JSON.parse(correctCount);
            }
            if (typeof answeredCount === 'string') {
                answeredCount = JSON.parse(answeredCount);
            }
            if (typeof avgTime === 'string') {
                avgTime = JSON.parse(avgTime);
            }
            if (typeof accuracy === 'string') {
                accuracy = JSON.parse(accuracy);
            }
        } catch (parseError) {
            console.error('Error parsing JSON data:', parseError);
            throw new Error('Invalid JSON data format');
        }
        
        // Validate arrays
        if (!Array.isArray(correctCount)) correctCount = Array(32).fill(0);
        if (!Array.isArray(answeredCount)) answeredCount = Array(32).fill(0);
        if (!Array.isArray(avgTime)) avgTime = Array(32).fill(0);
        if (!Array.isArray(accuracy)) accuracy = Array(32).fill(0);
        
        // Ensure arrays have 32 elements
        correctCount = this.ensureArrayLength(correctCount, 32);
        answeredCount = this.ensureArrayLength(answeredCount, 32);
        avgTime = this.ensureArrayLength(avgTime, 32);
        accuracy = this.ensureArrayLength(accuracy, 32);
        
        const parsedData = {
            labels: Array.from({length: 32}, (_, i) => String(i + 1)),
            correctCount: correctCount,
            answeredCount: answeredCount,
            avgTime: avgTime,
            accuracy: accuracy
        };
        
        console.log('Parsed data:', parsedData);
        return parsedData;
    }

    ensureArrayLength(arr, length) {
        if (arr.length === length) return arr;
        
        const result = new Array(length).fill(0);
        for (let i = 0; i < Math.min(arr.length, length); i++) {
            result[i] = arr[i] || 0;
        }
        return result;
    }

    buildChartConfigs() {
        if (!this.chartData) {
            throw new Error('Chart data not available');
        }

        return {
            combined: {
                title: 'Analisis Performa Lengkap (3 in 1)',
                datasets: [
                    {
                        label: 'Jawaban Benar',
                        data: this.chartData.correctCount,
                        borderColor: '#1e40af',
                        backgroundColor: 'rgba(30, 64, 175, 0.1)',
                        yAxisID: 'y',
                        tension: 0.4,
                        pointRadius: 4,
                        borderWidth: 3
                    },
                    {
                        label: 'Soal Dijawab',
                        data: this.chartData.answeredCount,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.1)',
                        yAxisID: 'y',
                        tension: 0.4,
                        pointRadius: 4,
                        borderWidth: 3
                    },
                    {
                        label: 'Rata-rata Waktu (detik)',
                        data: this.chartData.avgTime,
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        yAxisID: 'y1',
                        tension: 0.4,
                        pointRadius: 4,
                        borderWidth: 3
                    }
                ],
                scales: {
                    x: {
                        title: { 
                            display: true, 
                            text: 'Kolom Soal (1-32)',
                            font: { size: 14, weight: 'bold' }
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Jumlah Soal (0-26)' },
                        min: 0,
                        max: 26
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Waktu (detik)' },
                        min: 0,
                        max: 15,
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            },
            accuracy: {
                title: 'Tingkat Akurasi per Kolom',
                datasets: [
                    {
                        label: 'Akurasi (%)',
                        data: this.chartData.accuracy,
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.2)',
                        tension: 0.4,
                        pointRadius: 5,
                        borderWidth: 3,
                        fill: true
                    }
                ],
                scales: {
                    x: {
                        title: { 
                            display: true, 
                            text: 'Kolom Soal (1-32)',
                            font: { size: 14, weight: 'bold' }
                        }
                    },
                    y: {
                        title: { display: true, text: 'Akurasi (%)' },
                        min: 0,
                        max: 100
                    }
                }
            },
            speed: {
                title: 'Kecepatan Pengerjaan per Kolom',
                datasets: [
                    {
                        label: 'Soal Dijawab',
                        data: this.chartData.answeredCount,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.2)',
                        tension: 0.4,
                        pointRadius: 5,
                        borderWidth: 3,
                        fill: true
                    }
                ],
                scales: {
                    x: {
                        title: { 
                            display: true, 
                            text: 'Kolom Soal (1-32)',
                            font: { size: 14, weight: 'bold' }
                        }
                    },
                    y: {
                        title: { display: true, text: 'Jumlah Soal Dijawab (0-26)' },
                        min: 0,
                        max: 26
                    }
                }
            },
            time: {
                title: 'Rata-rata Waktu per Soal per Kolom',
                datasets: [
                    {
                        label: 'Waktu Rata-rata (detik)',
                        data: this.chartData.avgTime,
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.2)',
                        tension: 0.4,
                        pointRadius: 5,
                        borderWidth: 3,
                        fill: true
                    }
                ],
                scales: {
                    x: {
                        title: { 
                            display: true, 
                            text: 'Kolom Soal (1-32)',
                            font: { size: 14, weight: 'bold' }
                        }
                    },
                    y: {
                        title: { display: true, text: 'Waktu (detik)' },
                        min: 0,
                        max: 15
                    }
                }
            }
        };
    }

    createChart(type) {
        const canvas = document.getElementById('kraeplinChart');
        if (!canvas) {
            console.error('Kraeplin chart canvas not found');
            this.showError('Canvas element not found');
            return;
        }
        
        // Destroy existing chart
        if (this.currentChart) {
            this.currentChart.destroy();
            this.currentChart = null;
        }
        
        const config = this.chartConfigs[type];
        if (!config) {
            console.error('Chart config not found for type:', type);
            this.showError('Chart configuration not found');
            return;
        }
        
        try {
            const ctx = canvas.getContext('2d');
            
            this.currentChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.chartData.labels,
                    datasets: config.datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: config.title,
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: config.scales
                }
            });
            
            console.log(`Kraeplin chart '${type}' created successfully`);
            
        } catch (error) {
            console.error('Error creating chart:', error);
            this.showError('Error creating chart: ' + error.message);
        }
    }

    showError(message) {
        const loadingEl = document.getElementById('chartLoading');
        const containerEl = document.getElementById('chartContainer');
        
        if (loadingEl) {
            loadingEl.innerHTML = `
                <div style="text-align: center; padding: 60px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #f59e0b; margin-bottom: 15px;"></i>
                    <h4 style="color: #dc2626; margin-bottom: 10px;">Error Loading Chart</h4>
                    <p style="color: #6b7280; margin: 0;">${message}</p>
                </div>
            `;
            loadingEl.style.display = 'block';
        }
        
        if (containerEl) {
            containerEl.style.display = 'none';
        }
    }

    initTabSwitching() {
        const tabs = document.querySelectorAll('.chart-tab');
        if (tabs.length === 0) {
            console.warn('No chart tabs found');
            return;
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Update active tab
                tabs.forEach(t => {
                    t.classList.remove('active');
                    t.style.background = '#f8fafc';
                    t.style.color = '#6b7280';
                });
                
                tab.classList.add('active');
                tab.style.background = '#4f46e5';
                tab.style.color = 'white';
                
                // Create new chart
                const chartType = tab.dataset.chart;
                if (chartType) {
                    this.createChart(chartType);
                }
            });
        });
    }

    initialize() {
        console.log('=== KRAEPLIN CHART INITIALIZATION ===');
        
        // Check if Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded');
            this.showError('Chart.js library not loaded');
            return;
        }
        
        // Check if we have valid data
        if (!this.chartData || !this.chartConfigs) {
            console.error('Invalid chart data or configs');
            this.showError('Chart data not available');
            return;
        }
        
        // Initialize tab switching
        this.initTabSwitching();

        // Show chart with delay for better UX
        setTimeout(() => {
            const loadingEl = document.getElementById('chartLoading');
            const containerEl = document.getElementById('chartContainer');
            
            if (loadingEl) loadingEl.style.display = 'none';
            if (containerEl) containerEl.style.display = 'block';
            
            // Create initial chart
            this.createChart('combined');
        }, 800);
    }

    destroy() {
        if (this.currentChart) {
            this.currentChart.destroy();
            this.currentChart = null;
        }
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on a page with Kraeplin data
    if (window.kraeplinTestData || document.getElementById('kraeplinChart')) {
        console.log('Auto-initializing Kraeplin chart...');
        
        // Wait a bit more for Chart.js to be fully loaded
        setTimeout(() => {
            window.kraeplinChartInstance = new KraeplinChart();
            if (window.kraeplinChartInstance) {
                window.kraeplinChartInstance.initialize();
            }
        }, 1000);
    }
});

// Export for manual initialization
window.KraeplinChart = KraeplinChart;