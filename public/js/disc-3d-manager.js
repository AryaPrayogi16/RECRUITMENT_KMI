/**
 * ENHANCED DISC 3D Manager
 * Displays all graphs simultaneously with complete trait analysis
 * 
 * @author HR System Enhanced
 * @version 2.0.0
 */

class Disc3DManager {
    constructor(discData = null) {
        this.data = discData || this.getDefaultData();
        this.colors = ['#dc2626', '#f59e0b', '#10b981', '#3b82f6']; // D, I, S, C
        this.dimensions = ['D', 'I', 'S', 'C'];
        this.dimensionLabels = {
            'D': 'Dominance',
            'I': 'Influence', 
            'S': 'Steadiness',
            'C': 'Conscientiousness'
        };
        
        this.init();
    }

    /**
     * Initialize the DISC 3D Manager
     */
    init() {
        console.log('=== ENHANCED DISC 3D MANAGER INITIALIZATION ===');
        this.updateUI();
        this.renderAllGraphs();
    }

    /**
     * Update UI elements with current data
     */
    updateUI() {
        if (!this.data || !this.data.profile) return;

        // Update header summary
        this.updateElement('discTypeCode', this.data.profile.primary + this.data.profile.secondary);
        this.updateElement('discPrimaryType', this.data.profile.primaryLabel || 'Unknown Type');
        this.updateElement('discSecondaryInfo', `Sekunder: ${this.data.profile.secondaryLabel || 'Unknown'}`);
        this.updateElement('discPrimaryPercentage', this.data.profile.primaryPercentage || '0');
        
        // Update segment pattern
        if (this.data.most) {
            const pattern = `${this.data.most.D}-${this.data.most.I}-${this.data.most.S}-${this.data.most.C}`;
            this.updateElement('discSegmentPattern', pattern);
        }

        // Update completed date
        this.updateElement('discCompletedDate', this.data.session?.completedDate || 'N/A');

        // Update score cards for all graphs
        this.updateScoreCards();

        // Update session details
        this.updateSessionDetails();

        // Update comprehensive analysis content
        this.updateComprehensiveAnalysis();
    }

    /**
     * Update score cards with current data for all three graphs
     */
    updateScoreCards() {
        const graphTypes = ['most', 'least', 'change'];
        
        graphTypes.forEach(graphType => {
            this.dimensions.forEach(dim => {
                if (graphType === 'change') {
                    const value = this.data.change[dim] || 0;
                    this.updateElement(`${graphType}Score${dim}`, value > 0 ? `+${value}` : `${value}`);
                    this.updateElement(`${graphType}Segment${dim}`, value);
                } else {
                    const percentage = this.data.percentages[graphType][dim] || 0;
                    const segment = this.data[graphType][dim] || 1;
                    
                    this.updateElement(`${graphType}Score${dim}`, `${percentage.toFixed(1)}%`);
                    this.updateElement(`${graphType}Segment${dim}`, segment);
                }
            });
        });
    }

    /**
     * Update session details
     */
    updateSessionDetails() {
        if (!this.data.session) return;

        this.updateElement('discTestCode', this.data.session.testCode || 'N/A');
        this.updateElement('discTestDate', this.data.session.completedDate || 'N/A');
        this.updateElement('discTestDuration', this.data.session.duration || 'N/A');
    }

    /**
     * Update comprehensive analysis content with all traits
     */
    updateComprehensiveAnalysis() {
        if (!this.data.analysis) return;

        // Update all strengths
        if (this.data.analysis.allStrengths) {
            this.updateTraitTags('discAllStrengthTags', this.data.analysis.allStrengths, 'strength');
        }

        // Update all development areas
        if (this.data.analysis.allDevelopmentAreas) {
            this.updateTraitTags('discAllDevelopmentTags', this.data.analysis.allDevelopmentAreas, 'development');
        }

        // Update behavioral tendencies
        if (this.data.analysis.behavioralTendencies) {
            this.updateTraitTags('discBehavioralTags', this.data.analysis.behavioralTendencies, 'behavioral');
        }

        // Update communication preferences
        if (this.data.analysis.communicationPreferences) {
            this.updateTraitTags('discCommunicationTags', this.data.analysis.communicationPreferences, 'communication');
        }

        // Update motivators
        if (this.data.analysis.motivators) {
            this.updateTraitTags('discMotivatorTags', this.data.analysis.motivators, 'motivator');
        }

        // Update stress indicators
        if (this.data.analysis.stressIndicators) {
            this.updateTraitTags('discStressTags', this.data.analysis.stressIndicators, 'stress');
        }

        // Update work environment preferences
        if (this.data.analysis.workEnvironment) {
            this.updateTraitTags('discEnvironmentTags', this.data.analysis.workEnvironment, 'environment');
        }

        // Update decision making style
        if (this.data.analysis.decisionMaking) {
            this.updateTraitTags('discDecisionTags', this.data.analysis.decisionMaking, 'decision');
        }

        // Update leadership style
        if (this.data.analysis.leadershipStyle) {
            this.updateTraitTags('discLeadershipTags', this.data.analysis.leadershipStyle, 'leadership');
        }

        // Update conflict resolution style
        if (this.data.analysis.conflictResolution) {
            this.updateTraitTags('discConflictTags', this.data.analysis.conflictResolution, 'conflict');
        }

        // Update detailed descriptions
        this.updateElement('discDetailedWorkStyle', this.data.analysis.detailedWorkStyle || 'Belum tersedia');
        this.updateElement('discDetailedCommStyle', this.data.analysis.detailedCommunicationStyle || 'Belum tersedia');
        this.updateElement('discPublicSelfAnalysis', this.data.analysis.publicSelfAnalysis || 'Belum tersedia');
        this.updateElement('discPrivateSelfAnalysis', this.data.analysis.privateSelfAnalysis || 'Belum tersedia');
        this.updateElement('discAdaptationAnalysis', this.data.analysis.adaptationAnalysis || 'Belum tersedia');
        
        // Update profile summary
        this.updateElement('discProfileSummary', this.data.profile.summary || 'Belum tersedia');
    }

    /**
     * Update trait tags with enhanced styling
     * @param {string} containerId - Container element ID
     * @param {Array} traits - Array of trait strings
     * @param {string} type - Type of traits
     */
    updateTraitTags(containerId, traits, type) {
        const container = document.getElementById(containerId);
        if (!container || !Array.isArray(traits)) return;

        container.innerHTML = '';
        traits.forEach(trait => {
            const tag = document.createElement('span');
            tag.className = `disc-trait-tag ${type}`;
            tag.textContent = trait;
            container.appendChild(tag);
        });
    }

    /**
     * Render all three graphs simultaneously
     */
    renderAllGraphs() {
        const graphTypes = [
            { type: 'most', title: 'MOST (Topeng/Publik)', containerId: 'discMostGraph' },
            { type: 'least', title: 'LEAST (Inti/Pribadi)', containerId: 'discLeastGraph' },
            { type: 'change', title: 'CHANGE (Adaptasi)', containerId: 'discChangeGraph' }
        ];

        graphTypes.forEach(graph => {
            this.renderSingleGraph(graph.containerId, graph.type, graph.title);
        });

        console.log('All DISC graphs rendered successfully');
    }

    /**
     * Render a single DISC graph
     * @param {string} containerId - Container element ID
     * @param {string} graphType - Type of graph
     * @param {string} title - Graph title
     */
    renderSingleGraph(containerId, graphType, title) {
        const container = document.getElementById(containerId);
        if (!container) {
            console.error(`DISC graph container ${containerId} not found`);
            return;
        }

        // Clear previous content
        container.innerHTML = '';

        // Create graph wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'disc-graph-wrapper';

        // Create title
        const titleElement = document.createElement('h4');
        titleElement.className = 'disc-graph-title';
        titleElement.textContent = title;
        wrapper.appendChild(titleElement);

        // Create SVG
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('width', '100%');
        svg.setAttribute('height', '350');
        svg.setAttribute('viewBox', '0 0 400 350');
        svg.style.background = '#ffffff';

        // Draw graph
        this.drawSingleGraph(svg, graphType);

        wrapper.appendChild(svg);
        container.appendChild(wrapper);
    }

    /**
     * Draw a single DISC graph
     * @param {SVGElement} svg - SVG element
     * @param {string} graphType - Type of graph
     */
    drawSingleGraph(svg, graphType) {
        // Draw background
        this.drawGraphBackground(svg);
        
        // Draw grid lines
        this.drawGridLines(svg, graphType);
        
        // Draw bars for each dimension
        this.dimensions.forEach((dim, index) => {
            const x = 60 + (index * 70);
            const barWidth = 50;

            // Draw column
            this.drawColumn(svg, x, barWidth, dim, index, graphType);
            
            // Draw bar based on graph type
            if (graphType === 'change') {
                this.drawChangeBar(svg, x, barWidth, this.data.change[dim], this.colors[index]);
            } else {
                this.drawRegularBar(svg, x, barWidth, this.data[graphType][dim], this.colors[index]);
            }

            // Draw percentage text
            this.drawPercentageText(svg, x, barWidth, dim, index, graphType);
        });

        // Draw connecting line for regular graphs
        if (graphType !== 'change') {
            this.drawConnectingLine(svg, graphType);
        }
    }

    /**
     * Draw graph background
     * @param {SVGElement} svg - SVG element
     */
    drawGraphBackground(svg) {
        const bg = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        bg.setAttribute('width', '350');
        bg.setAttribute('height', '280');
        bg.setAttribute('x', '25');
        bg.setAttribute('y', '30');
        bg.setAttribute('fill', '#f8fafc');
        bg.setAttribute('stroke', '#e2e8f0');
        bg.setAttribute('stroke-width', '2');
        svg.appendChild(bg);
    }

    /**
     * Draw grid lines
     * @param {SVGElement} svg - SVG element
     * @param {string} graphType - Type of graph
     */
    drawGridLines(svg, graphType) {
        if (graphType === 'change') {
            // Change graph: -4 to +4 scale
            for (let i = -4; i <= 4; i++) {
                const y = 170 + (i * -35); // Center at 170, scale by 35px per unit
                
                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.setAttribute('x1', '25');
                line.setAttribute('x2', '375');
                line.setAttribute('y1', y);
                line.setAttribute('y2', y);
                line.setAttribute('stroke', i === 0 ? '#374151' : '#e2e8f0');
                line.setAttribute('stroke-width', i === 0 ? '2' : '1');
                line.setAttribute('stroke-dasharray', i === 0 ? 'none' : '3,3');
                svg.appendChild(line);

                // Scale labels
                const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                label.setAttribute('x', '15');
                label.setAttribute('y', y + 3);
                label.setAttribute('font-size', '12');
                label.setAttribute('fill', '#6b7280');
                label.textContent = i > 0 ? `+${i}` : i;
                svg.appendChild(label);
            }
        } else {
            // Regular graph: 1-7 scale
            for (let i = 1; i <= 7; i++) {
                const y = 30 + (280 - ((i-1) * 40));
                
                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.setAttribute('x1', '25');
                line.setAttribute('x2', '375');
                line.setAttribute('y1', y);
                line.setAttribute('y2', y);
                line.setAttribute('stroke', '#e2e8f0');
                line.setAttribute('stroke-dasharray', '3,3');
                svg.appendChild(line);

                // Scale labels
                const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                label.setAttribute('x', '15');
                label.setAttribute('y', y + 3);
                label.setAttribute('font-size', '12');
                label.setAttribute('fill', '#6b7280');
                label.textContent = i;
                svg.appendChild(label);
            }
        }
    }

    /**
     * Draw column background and header
     * @param {SVGElement} svg - SVG element
     * @param {number} x - X position
     * @param {number} barWidth - Width of bar
     * @param {string} dimension - Dimension letter
     * @param {number} index - Index for color
     * @param {string} graphType - Type of graph
     */
    drawColumn(svg, x, barWidth, dimension, index, graphType) {
        // Column background
        const col = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        col.setAttribute('width', barWidth);
        col.setAttribute('height', '280');
        col.setAttribute('x', x);
        col.setAttribute('y', '30');
        col.setAttribute('fill', 'white');
        col.setAttribute('stroke', '#e2e8f0');
        svg.appendChild(col);

        // Dimension header
        const header = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        header.setAttribute('width', barWidth);
        header.setAttribute('height', '25');
        header.setAttribute('x', x);
        header.setAttribute('y', '5');
        header.setAttribute('fill', this.colors[index]);
        svg.appendChild(header);

        // Header text
        const headerText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        headerText.setAttribute('x', x + (barWidth / 2));
        headerText.setAttribute('y', '20');
        headerText.setAttribute('fill', 'white');
        headerText.setAttribute('font-size', '14');
        headerText.setAttribute('font-weight', 'bold');
        headerText.setAttribute('text-anchor', 'middle');
        headerText.textContent = dimension;
        svg.appendChild(headerText);
    }

    /**
     * Draw regular bar (for MOST/LEAST graphs)
     */
    drawRegularBar(svg, x, barWidth, value, color) {
        const barHeight = (value / 7) * 280;
        const barY = 310 - barHeight;

        // Bar
        const bar = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        bar.setAttribute('width', barWidth - 10);
        bar.setAttribute('height', barHeight);
        bar.setAttribute('x', x + 5);
        bar.setAttribute('y', barY);
        bar.setAttribute('fill', color);
        bar.setAttribute('opacity', '0.7');
        svg.appendChild(bar);

        // Score point
        const pointY = 30 + (280 - ((value - 1) * 40 + 20));
        const point = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        point.setAttribute('cx', x + (barWidth / 2));
        point.setAttribute('cy', pointY);
        point.setAttribute('r', '6');
        point.setAttribute('fill', color);
        point.setAttribute('stroke', 'white');
        point.setAttribute('stroke-width', '2');
        svg.appendChild(point);
    }

    /**
     * Draw change bar (for CHANGE graph)
     */
    drawChangeBar(svg, x, barWidth, value, color) {
        const centerY = 170; // Middle of the graph
        const barHeight = Math.abs(value) * 35; // Scale for change graph

        let barY;
        if (value >= 0) {
            barY = centerY - barHeight;
        } else {
            barY = centerY;
        }

        const bar = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        bar.setAttribute('width', barWidth - 10);
        bar.setAttribute('height', barHeight);
        bar.setAttribute('x', x + 5);
        bar.setAttribute('y', barY);
        bar.setAttribute('fill', value >= 0 ? color : '#dc2626');
        bar.setAttribute('opacity', '0.7');
        svg.appendChild(bar);
    }

    /**
     * Draw percentage text below columns
     */
    drawPercentageText(svg, x, barWidth, dimension, index, graphType) {
        const percentText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        percentText.setAttribute('x', x + (barWidth / 2));
        percentText.setAttribute('y', '330');
        percentText.setAttribute('fill', this.colors[index]);
        percentText.setAttribute('font-size', '12');
        percentText.setAttribute('font-weight', 'bold');
        percentText.setAttribute('text-anchor', 'middle');
        
        if (graphType === 'change') {
            const value = this.data.change[dimension];
            percentText.textContent = value > 0 ? `+${value}` : `${value}`;
        } else {
            const percentage = this.data.percentages[graphType][dimension];
            percentText.textContent = `${percentage.toFixed(1)}%`;
        }
        
        svg.appendChild(percentText);
    }

    /**
     * Draw connecting line between points
     */
    drawConnectingLine(svg, graphType) {
        const points = [];

        this.dimensions.forEach((dim, index) => {
            const x = 60 + (index * 70) + 25; // Center of bar
            const value = this.data[graphType][dim];
            const y = 30 + (280 - ((value - 1) * 40 + 20));
            points.push(`${x},${y}`);
        });

        const path = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
        path.setAttribute('points', points.join(' '));
        path.setAttribute('stroke', '#4f46e5');
        path.setAttribute('stroke-width', '3');
        path.setAttribute('fill', 'none');
        path.setAttribute('opacity', '0.8');
        svg.appendChild(path);
    }

    /**
     * Update DOM element text content
     */
    updateElement(elementId, content) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = content;
        }
    }

    /**
     * Get enhanced default data with comprehensive analysis
     */
    getDefaultData() {
        return {
            most: { D: 6, I: 5, S: 2, C: 3 },
            least: { D: 3, I: 2, S: 6, C: 4 },
            change: { D: 3, I: 3, S: -4, C: -1 },
            percentages: {
                most: { D: 75.2, I: 62.8, S: 28.5, C: 45.1 },
                least: { D: 38.4, I: 24.6, S: 78.3, C: 55.7 }
            },
            profile: {
                primary: 'D',
                secondary: 'I',
                primaryLabel: 'Decisive Influencer',
                secondaryLabel: 'Inspiring Leader',
                primaryPercentage: 75.2,
                summary: 'Tipe DI menunjukkan kepribadian yang kuat dalam kepemimpinan dan pengaruh. Individu dengan profil ini cenderung berorientasi pada hasil, memiliki kemampuan komunikasi yang baik, dan mampu memotivasi orang lain untuk mencapai tujuan bersama.'
            },
            analysis: {
                // Comprehensive strengths from all dimensions
                allStrengths: [
                    'Kepemimpinan Natural', 'Pengambilan Keputusan Cepat', 'Orientasi Hasil Tinggi', 
                    'Komunikasi Persuasif', 'Kemampuan Memotivasi', 'Keberanian Mengambil Risiko',
                    'Inisiatif Tinggi', 'Fokus pada Pencapaian', 'Kemampuan Delegasi',
                    'Daya Juang Tinggi', 'Visioner', 'Energi Tinggi'
                ],
                
                // Comprehensive development areas
                allDevelopmentAreas: [
                    'Kesabaran dalam Proses', 'Perhatian pada Detail', 'Konsistensi Follow-up',
                    'Mendengarkan Feedback', 'Fleksibilitas Metode', 'Empati yang Lebih Dalam',
                    'Manajemen Stres', 'Kontrol Emosi', 'Delegasi yang Efektif'
                ],

                // Behavioral tendencies
                behavioralTendencies: [
                    'Mengambil Kendali Situasi', 'Berbicara Langsung pada Inti', 'Membuat Keputusan Cepat',
                    'Fokus pada Hasil Akhir', 'Mendorong Perubahan', 'Berani Konfrontasi',
                    'Multitasking Efektif', 'Networking Aktif', 'Kompetitif'
                ],

                // Communication preferences
                communicationPreferences: [
                    'Komunikasi Langsung', 'Presentasi yang Dinamis', 'Diskusi Berorientasi Solusi',
                    'Feedback yang Konstruktif', 'Meeting yang Efisien', 'Laporan Ringkas',
                    'Brainstorming Aktif', 'Negosiasi Asertif'
                ],

                // Motivators
                motivators: [
                    'Pencapaian Target', 'Pengakuan Prestasi', 'Tantangan Baru',
                    'Otoritas dan Tanggung Jawab', 'Kompetisi Sehat', 'Perubahan dan Inovasi',
                    'Hasil yang Terukur', 'Pengaruh pada Keputusan'
                ],

                // Stress indicators
                stressIndicators: [
                    'Ketidakpastian Berkepanjangan', 'Proses yang Terlalu Lambat', 'Micromanagement',
                    'Rutinitas yang Monoton', 'Konflik Interpersonal', 'Kekurangan Informasi',
                    'Deadline yang Tidak Realistis', 'Perubahan Mendadak'
                ],

                // Work environment preferences
                workEnvironment: [
                    'Lingkungan Dinamis', 'Tim yang Responsif', 'Budaya Meritokrasi',
                    'Struktur yang Fleksibel', 'Akses pada Manajemen Senior', 'Resource yang Memadai',
                    'Teknologi Terkini', 'Ruang untuk Inovasi'
                ],

                // Decision making style
                decisionMaking: [
                    'Berdasarkan Data dan Intuisi', 'Cepat dan Tegas', 'Mempertimbangkan Dampak',
                    'Melibatkan Stakeholder Kunci', 'Fokus pada ROI', 'Berani Mengambil Risiko Terkalkulasi'
                ],

                // Leadership style
                leadershipStyle: [
                    'Transformational Leadership', 'Delegasi Efektif', 'Coaching dan Mentoring',
                    'Setting Ekspektasi Tinggi', 'Leading by Example', 'Inspirational Communication'
                ],

                // Conflict resolution style
                conflictResolution: [
                    'Pendekatan Langsung', 'Fokus pada Solusi', 'Win-Win Solution',
                    'Mediasi Objektif', 'Komunikasi Terbuka', 'Escalation Jika Diperlukan'
                ],

                // Detailed descriptions
                detailedWorkStyle: 'Bekerja dengan tempo tinggi dan fokus pada hasil. Menyukai lingkungan yang dinamis dengan kebebasan untuk mengambil keputusan. Efektif dalam situasi yang membutuhkan kepemimpinan dan inisiatif. Dapat bekerja di bawah tekanan dan deadline ketat. Memiliki kemampuan multitasking yang baik dan dapat mengkoordinasi berbagai proyek secara bersamaan.',
                
                detailedCommunicationStyle: 'Komunikasi yang langsung, jelas, dan persuasif. Mampu menyampaikan visi dan memotivasi tim. Efektif dalam presentasi dan public speaking. Dapat beradaptasi dengan berbagai audience. Menyukai diskusi yang fokus pada solusi dan action plan. Memberikan feedback yang konstruktif dan langsung pada point.',
                
                publicSelfAnalysis: 'Di lingkungan publik, menampilkan sosok yang percaya diri, tegas, dan berorientasi pada hasil. Terlihat sebagai pemimpin natural yang dapat mempengaruhi dan memotivasi orang lain. Komunikatif dan ekspresif dalam interaksi sosial.',
                
                privateSelfAnalysis: 'Secara pribadi, lebih reflektif dan mempertimbangkan berbagai aspek sebelum mengambil keputusan. Memiliki sisi yang lebih sabar dan stabil dibanding yang ditampilkan di publik. Menghargai harmoni dan stabilitas dalam hubungan personal.',
                
                adaptationAnalysis: 'Mengalami tekanan untuk tampil lebih dominan dan ekspresif di lingkungan kerja dibanding kepribadian alami. Adaptasi ini dapat menyebabkan kelelahan jika dilakukan terus-menerus. Perlu keseimbangan antara tuntutan peran dan kebutuhan personal.'
            },
            session: {
                testCode: 'DISC3D_20240115_001',
                completedDate: '15 Jan 2024',
                duration: '18 menit 45 detik'
            }
        };
    }

    /**
     * Load data from Laravel/PHP backend
     */
    loadFromLaravel(laravelData) {
        if (!laravelData) {
            console.warn('No Laravel data provided, using default data');
            return;
        }

        this.data = laravelData;
        this.updateUI();
        this.renderAllGraphs();
        console.log('Loaded data from Laravel:', laravelData);
    }

    /**
     * Destroy the manager and clean up
     */
    destroy() {
        console.log('Enhanced DISC 3D Manager destroyed');
    }
}

/**
 * Global function to initialize Enhanced DISC 3D Manager
 */
function initializeDisc3D(discData = null) {
    // Check if we're in the right page
    if (!document.getElementById('disc-section')) {
        console.log('DISC section not found, skipping initialization');
        return null;
    }

    // Initialize the enhanced manager
    const manager = new Disc3DManager(discData);
    
    // Store reference globally for debugging
    window.disc3DManager = manager;
    
    console.log('Enhanced DISC 3D Manager initialized successfully');
    return manager;
}

/**
 * Initialize when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    // Check if DISC data is available from Laravel
    if (typeof window.discData !== 'undefined') {
        initializeDisc3D(window.discData);
    } else {
        // Initialize with enhanced default data
        initializeDisc3D();
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { Disc3DManager, initializeDisc3D };
}