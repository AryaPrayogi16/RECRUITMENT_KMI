/**
 * ENHANCED DISC 3D Manager - FINAL FIXED VERSION
 * Menggunakan skala angka asli (segments) dan data real dari database
 * ✅ GRAFIK TETAP UTUH - HANYA MEMPERBAIKI DATA HANDLING
 * 
 * @author HR System Enhanced
 * @version 2.3.0 (FINAL FIXED)
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
        console.log('=== ENHANCED DISC 3D MANAGER INITIALIZATION (FINAL FIXED) ===');
        this.sanitizeData();
        this.updateUI();
        this.renderAllGraphs();
    }

    /**
     * ✅ IMPROVED: Sanitize data to ensure arrays are arrays and handle mixed data types
     */
    sanitizeData() {
        if (!this.data.analysis) {
            this.data.analysis = {};
        }

        // ✅ FIXED: Ensure all analysis arrays are actually arrays with better handling
        const analysisFields = [
            'allStrengths', 'allDevelopmentAreas', 'behavioralTendencies', 
            'communicationPreferences', 'motivators', 'stressIndicators',
            'workEnvironment', 'decisionMaking', 'leadershipStyle', 'conflictResolution'
        ];

        analysisFields.forEach(field => {
            if (this.data.analysis[field]) {
                if (Array.isArray(this.data.analysis[field])) {
                    // Already an array, keep as is
                    this.data.analysis[field] = this.data.analysis[field].filter(item => 
                        item && typeof item === 'string' && item.trim().length > 0
                    );
                } else if (typeof this.data.analysis[field] === 'string') {
                    // String data - try to intelligently split
                    const stringData = this.data.analysis[field].trim();
                    if (stringData.length > 0) {
                        // Try different delimiters
                        let splitResult = [];
                        if (stringData.includes(',')) {
                            splitResult = stringData.split(',');
                        } else if (stringData.includes(';')) {
                            splitResult = stringData.split(';');
                        } else if (stringData.includes('|')) {
                            splitResult = stringData.split('|');
                        } else if (stringData.includes('\n')) {
                            splitResult = stringData.split('\n');
                        } else {
                            // Single item
                            splitResult = [stringData];
                        }
                        
                        this.data.analysis[field] = splitResult
                            .map(item => item.trim())
                            .filter(item => item.length > 0);
                    } else {
                        this.data.analysis[field] = [];
                    }
                } else {
                    // Other data types, convert to empty array
                    this.data.analysis[field] = [];
                }
            } else {
                this.data.analysis[field] = [];
            }
        });

        // ✅ FIXED: Ensure text analysis fields are strings
        const textFields = [
            'detailedWorkStyle', 'detailedCommunicationStyle', 'publicSelfAnalysis',
            'privateSelfAnalysis', 'adaptationAnalysis'
        ];

        textFields.forEach(field => {
            if (this.data.analysis[field]) {
                if (typeof this.data.analysis[field] !== 'string') {
                    this.data.analysis[field] = String(this.data.analysis[field]);
                }
            } else {
                this.data.analysis[field] = '';
            }
        });

        // ✅ ENSURE: Numeric data is properly typed
        ['most', 'least', 'change'].forEach(graphType => {
            if (this.data[graphType]) {
                this.dimensions.forEach(dim => {
                    if (this.data[graphType][dim] !== undefined) {
                        this.data[graphType][dim] = parseInt(this.data[graphType][dim]) || (graphType === 'change' ? 0 : 1);
                    }
                });
            }
        });

        console.log('Data sanitized successfully:', this.data);
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
        
        // Update segment pattern - GUNAKAN SEGMENTS BUKAN PERCENTAGES
        if (this.data.most) {
            const pattern = `${this.data.most.D}-${this.data.most.I}-${this.data.most.S}-${this.data.most.C}`;
            this.updateElement('discSegmentPattern', pattern);
        }

        // Update completed date
        this.updateElement('discCompletedDate', this.data.session?.completedDate || 'N/A');

        // Update score cards for all graphs - TETAP MENGGUNAKAN SEGMENTS
        this.updateScoreCards();

        // Update session details
        this.updateSessionDetails();

        // ✅ UPDATED: Only update analysis if data exists
        this.updateComprehensiveAnalysis();
    }

    /**
     * Update score cards dengan segment values (TETAP SAMA - TIDAK DIUBAH)
     */
    updateScoreCards() {
        const graphTypes = ['most', 'least', 'change'];
        
        graphTypes.forEach(graphType => {
            this.dimensions.forEach(dim => {
                if (graphType === 'change') {
                    // CHANGE graph: gunakan change segments (bisa minus)
                    const value = this.data.change[dim] || 0;
                    this.updateElement(`${graphType}Score${dim}`, value > 0 ? `+${value}` : `${value}`);
                    this.updateElement(`${graphType}Segment${dim}`, value);
                } else {
                    // MOST & LEAST graphs: GUNAKAN SEGMENTS (1-7), BUKAN PERCENTAGES
                    const segment = this.data[graphType][dim] || 1;
                    const percentage = this.data.percentages?.[graphType]?.[dim] || 0;
                    
                    // TETAP: Tampilkan segment value, bukan percentage
                    this.updateElement(`${graphType}Score${dim}`, `${segment}`);
                    this.updateElement(`${graphType}Segment${dim}`, segment);
                    
                    // Optional: tampilkan percentage sebagai tooltip
                    const element = document.getElementById(`${graphType}Score${dim}`);
                    if (element) {
                        element.title = `${percentage.toFixed(1)}%`; // Percentage sebagai tooltip
                    }
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
     * ✅ IMPROVED: Update comprehensive analysis content with better data type handling
     */
    updateComprehensiveAnalysis() {
        if (!this.data.analysis) return;

        // ✅ IMPROVED: Only update if data exists and is valid array
        const updateIfValidArray = (elementId, dataArray, type) => {
            if (Array.isArray(dataArray) && dataArray.length > 0) {
                // Filter out empty or invalid items
                const validItems = dataArray.filter(item => 
                    item && typeof item === 'string' && item.trim().length > 0
                );
                
                if (validItems.length > 0) {
                    this.updateTraitTags(elementId, validItems, type);
                    return true;
                }
            }
            return false;
        };

        // Update all trait sections
        updateIfValidArray('discAllStrengthTags', this.data.analysis.allStrengths, 'strength');
        updateIfValidArray('discAllDevelopmentTags', this.data.analysis.allDevelopmentAreas, 'development');
        updateIfValidArray('discBehavioralTags', this.data.analysis.behavioralTendencies, 'behavioral');
        updateIfValidArray('discCommunicationTags', this.data.analysis.communicationPreferences, 'communication');
        updateIfValidArray('discMotivatorTags', this.data.analysis.motivators, 'motivator');
        updateIfValidArray('discStressTags', this.data.analysis.stressIndicators, 'stress');
        updateIfValidArray('discEnvironmentTags', this.data.analysis.workEnvironment, 'environment');
        updateIfValidArray('discDecisionTags', this.data.analysis.decisionMaking, 'decision');
        updateIfValidArray('discLeadershipTags', this.data.analysis.leadershipStyle, 'leadership');
        updateIfValidArray('discConflictTags', this.data.analysis.conflictResolution, 'conflict');

        // ✅ IMPROVED: Only update detailed descriptions if they exist and are meaningful strings
        const updateIfValidString = (elementId, text, forbiddenTexts = []) => {
            if (typeof text === 'string' && text.trim()) {
                const cleanText = text.trim();
                const isForbidden = forbiddenTexts.some(forbidden => 
                    cleanText.toLowerCase().includes(forbidden.toLowerCase())
                );
                
                if (!isForbidden && cleanText.length > 10) { // Minimum meaningful length
                    this.updateElement(elementId, cleanText);
                    return true;
                }
            }
            return false;
        };

        const forbiddenTexts = ['Belum tersedia', 'No data', 'Not available', 'N/A'];

        updateIfValidString('discDetailedWorkStyle', this.data.analysis.detailedWorkStyle, forbiddenTexts);
        updateIfValidString('discDetailedCommStyle', this.data.analysis.detailedCommunicationStyle, forbiddenTexts);
        updateIfValidString('discPublicSelfAnalysis', this.data.analysis.publicSelfAnalysis, forbiddenTexts);
        updateIfValidString('discPrivateSelfAnalysis', this.data.analysis.privateSelfAnalysis, forbiddenTexts);
        updateIfValidString('discAdaptationAnalysis', this.data.analysis.adaptationAnalysis, forbiddenTexts);
        
        // ✅ IMPROVED: Only update profile summary if exists and meaningful
        if (this.data.profile?.summary) {
            updateIfValidString('discProfileSummary', this.data.profile.summary, forbiddenTexts);
        }
    }

    /**
     * ✅ IMPROVED: Update trait tags with enhanced error handling and validation
     */
    updateTraitTags(containerId, traits, type) {
        const container = document.getElementById(containerId);
        if (!container) {
            console.warn(`Container ${containerId} not found`);
            return;
        }

        if (!Array.isArray(traits) || traits.length === 0) {
            console.warn(`Invalid traits data for ${containerId}:`, traits);
            return;
        }

        container.innerHTML = '';
        
        // Filter and process traits
        const validTraits = traits.filter(trait => 
            trait && typeof trait === 'string' && trait.trim().length > 0
        );

        validTraits.forEach(trait => {
            const cleanTrait = trait.trim();
            if (cleanTrait.length > 0) {
                const tag = document.createElement('span');
                tag.className = `disc-trait-tag ${type}`;
                tag.textContent = cleanTrait;
                tag.title = cleanTrait; // Tooltip for long text
                container.appendChild(tag);
            }
        });
    }

    /**
     * ✅ TETAP SAMA: Render all three graphs simultaneously - TIDAK DIUBAH
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

        console.log('All DISC graphs rendered successfully (using real database segments - FINAL FIXED)');
    }

    /**
     * ✅ TETAP SAMA: Render a single DISC graph - TIDAK DIUBAH
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
     * ✅ TETAP SAMA: Draw a single DISC graph - TIDAK DIUBAH
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
            
            // Draw bar based on graph type - TETAP MENGGUNAKAN SEGMENTS
            if (graphType === 'change') {
                this.drawChangeBar(svg, x, barWidth, this.data.change[dim], this.colors[index]);
            } else {
                // TETAP: Gunakan segment values untuk MOST dan LEAST
                this.drawRegularBar(svg, x, barWidth, this.data[graphType][dim], this.colors[index]);
            }

            // Draw segment text (TETAP SAMA)
            this.drawSegmentText(svg, x, barWidth, dim, index, graphType);
        });

        // Draw connecting line for all graphs (including CHANGE)
        this.drawConnectingLine(svg, graphType);
    }

    /**
     * ✅ TETAP SAMA: Draw graph background - TIDAK DIUBAH
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
     * ✅ TETAP SAMA: Draw grid lines - TIDAK DIUBAH
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
     * ✅ TETAP SAMA: Draw column background and header - TIDAK DIUBAH
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
     * ✅ TETAP SAMA: Draw regular bar (for MOST/LEAST graphs) - TIDAK DIUBAH
     */
    drawRegularBar(svg, x, barWidth, segmentValue, color) {
        // TETAP: Gunakan segment value (1-7) langsung
        const validSegment = Math.max(1, Math.min(7, parseInt(segmentValue) || 1));
        const barHeight = (validSegment / 7) * 280;
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
        const pointY = 30 + (280 - ((validSegment - 1) * 40 + 20));
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
     * ✅ TETAP SAMA: Draw change bar (for CHANGE graph) - TIDAK DIUBAH
     */
    drawChangeBar(svg, x, barWidth, value, color) {
        // TETAP: Ensure value is a valid number
        const validValue = parseInt(value) || 0;
        const centerY = 170; // Middle of the graph (skala 0)
        const barHeight = Math.abs(validValue) * 35; // Scale untuk change graph (35px per unit)

        let barY;
        if (validValue >= 0) {
            // Nilai positif: bar naik ke atas dari center
            barY = centerY - barHeight;
        } else {
            // Nilai negatif: bar turun ke bawah dari center
            barY = centerY;
        }

        // Jika barHeight > 0 (ada nilai), gambar bar
        if (barHeight > 0) {
            const bar = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            bar.setAttribute('width', barWidth - 10);
            bar.setAttribute('height', barHeight);
            bar.setAttribute('x', x + 5);
            bar.setAttribute('y', barY);
            bar.setAttribute('fill', validValue >= 0 ? color : '#dc2626');
            bar.setAttribute('opacity', '0.8');
            bar.setAttribute('stroke', validValue >= 0 ? color : '#dc2626');
            bar.setAttribute('stroke-width', '1');
            svg.appendChild(bar);
        }

        // Tambahkan point indicator di posisi yang tepat
        const pointY = centerY + (validValue * -35); // Negatif karena SVG Y terbalik
        const point = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        point.setAttribute('cx', x + (barWidth / 2));
        point.setAttribute('cy', pointY);
        point.setAttribute('r', '5');
        point.setAttribute('fill', validValue >= 0 ? color : '#dc2626');
        point.setAttribute('stroke', 'white');
        point.setAttribute('stroke-width', '2');
        svg.appendChild(point);

        // Tambahkan garis dari center ke point untuk clarity
        if (validValue !== 0) {
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', x + (barWidth / 2));
            line.setAttribute('y1', centerY);
            line.setAttribute('x2', x + (barWidth / 2));
            line.setAttribute('y2', pointY);
            line.setAttribute('stroke', validValue >= 0 ? color : '#dc2626');
            line.setAttribute('stroke-width', '3');
            line.setAttribute('opacity', '0.6');
            svg.appendChild(line);
        }
    }

    /**
     * ✅ TETAP SAMA: Draw segment text below columns - TIDAK DIUBAH
     */
    drawSegmentText(svg, x, barWidth, dimension, index, graphType) {
        const segmentText = document.createElementNS('http://www.w3.org/2000/svg', 'text');
        segmentText.setAttribute('x', x + (barWidth / 2));
        segmentText.setAttribute('y', '330');
        segmentText.setAttribute('fill', this.colors[index]);
        segmentText.setAttribute('font-size', '12');
        segmentText.setAttribute('font-weight', 'bold');
        segmentText.setAttribute('text-anchor', 'middle');
        
        if (graphType === 'change') {
            const value = parseInt(this.data.change[dimension]) || 0;
            segmentText.textContent = value > 0 ? `+${value}` : `${value}`;
        } else {
            // TETAP: Tampilkan segment value, bukan percentage
            const segmentValue = parseInt(this.data[graphType][dimension]) || 1;
            segmentText.textContent = `${segmentValue}`;
            
            // Optional: Buat tooltip untuk percentage
            const percentage = this.data.percentages?.[graphType]?.[dimension];
            if (percentage) {
                const title = document.createElementNS('http://www.w3.org/2000/svg', 'title');
                title.textContent = `${percentage.toFixed(1)}%`;
                segmentText.appendChild(title);
            }
        }
        
        svg.appendChild(segmentText);
    }

    /**
     * ✅ TETAP SAMA: Draw connecting line between points - TIDAK DIUBAH
     */
    drawConnectingLine(svg, graphType) {
        const points = [];

        this.dimensions.forEach((dim, index) => {
            const x = 60 + (index * 70) + 25; // Center of bar
            
            let y;
            if (graphType === 'change') {
                // CHANGE graph: gunakan center (170) + offset berdasarkan value
                const value = parseInt(this.data.change[dim]) || 0;
                y = 170 + (value * -35); // Negatif karena SVG Y terbalik
            } else {
                // MOST/LEAST graph: gunakan segment value
                const segmentValue = parseInt(this.data[graphType][dim]) || 1;
                y = 30 + (280 - ((segmentValue - 1) * 40 + 20));
            }
            
            points.push(`${x},${y}`);
        });

        const path = document.createElementNS('http://www.w3.org/2000/svg', 'polyline');
        path.setAttribute('points', points.join(' '));
        
        // Warna berbeda untuk CHANGE graph
        if (graphType === 'change') {
            path.setAttribute('stroke', '#7c3aed');
            path.setAttribute('stroke-width', '4');
            path.setAttribute('stroke-dasharray', '5,5');
        } else {
            path.setAttribute('stroke', '#4f46e5');
            path.setAttribute('stroke-width', '3');
        }
        
        path.setAttribute('fill', 'none');
        path.setAttribute('opacity', '0.8');
        svg.appendChild(path);
    }

    /**
     * Update DOM element text content
     */
    updateElement(elementId, content) {
        const element = document.getElementById(elementId);
        if (element && content !== null && content !== undefined) {
            element.textContent = String(content);
        }
    }

    /**
     * ✅ UPDATED: Default data for template - with better fallbacks
     */
    getDefaultData() {
        return {
            most: { D: 1, I: 1, S: 1, C: 1 },
            least: { D: 1, I: 1, S: 1, C: 1 },
            change: { D: 0, I: 0, S: 0, C: 0 },
            percentages: {
                most: { D: 0, I: 0, S: 0, C: 0 },
                least: { D: 0, I: 0, S: 0, C: 0 }
            },
            profile: {
                primary: 'D',
                secondary: 'I',
                primaryLabel: 'Unknown Type',
                secondaryLabel: 'Unknown',
                primaryPercentage: 0,
                summary: 'Belum tersedia'
            },
            analysis: {
                allStrengths: [],
                allDevelopmentAreas: [],
                behavioralTendencies: [],
                communicationPreferences: [],
                motivators: [],
                stressIndicators: [],
                workEnvironment: [],
                decisionMaking: [],
                leadershipStyle: [],
                conflictResolution: [],
                detailedWorkStyle: 'Belum tersedia analisis gaya kerja',
                detailedCommunicationStyle: 'Belum tersedia analisis gaya komunikasi',
                publicSelfAnalysis: 'Belum tersedia analisis diri publik',
                privateSelfAnalysis: 'Belum tersedia analisis diri pribadi',
                adaptationAnalysis: 'Belum tersedia analisis adaptasi'
            },
            session: {
                testCode: 'N/A',
                completedDate: 'N/A',
                duration: 'N/A'
            }
        };
    }

    /**
     * Load data from Laravel/PHP backend
     */
    loadFromLaravel(laravelData) {
        if (!laravelData) {
            console.warn('No Laravel data provided, using minimal default data');
            return;
        }

        this.data = laravelData;
        this.sanitizeData();
        this.updateUI();
        this.renderAllGraphs();
        console.log('Loaded REAL data from Laravel database (FINAL FIXED):', laravelData);
    }

    /**
     * Destroy the manager and clean up
     */
    destroy() {
        console.log('Enhanced DISC 3D Manager destroyed (FINAL FIXED)');
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
    
    console.log('Enhanced DISC 3D Manager initialized successfully (FINAL FIXED for data types)');
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
        // Initialize with minimal default data
        console.log('No real DISC data available, using minimal defaults');
        initializeDisc3D();
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { Disc3DManager, initializeDisc3D };
}