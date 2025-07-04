<?php

namespace App\Services;

class DiscChartGenerator
{
    public static function generateChart($candidate)
    {
        // Use the correct DISC 3D relationship
        $discResult = $candidate->disc3DTestResult;
        if (!$discResult) return '';
        
        $width = 600; // Reduced from 700 to 600
        $height = 220; // Reduced from 280 to 220
        $chartWidth = 180; // Reduced from 210 to 180
        $chartHeight = 140; // Reduced from 180 to 140
        $marginTop = 35;
        
        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        
        // Background
        $svg .= '<rect width="' . $width . '" height="' . $height . '" fill="white" stroke="#e5e7eb" stroke-width="1"/>';
        
        // Main Title
        $svg .= '<text x="300" y="18" font-size="11" font-weight="bold" text-anchor="middle" fill="#1f2937">Grafik DISC 3D - Segment Values</text>';
        
        // Helper function to draw regular DISC chart (MOST & LEAST)
        $drawRegularChart = function($startX, $title, $dVal, $iVal, $sVal, $cVal, $dPerc, $iPerc, $sPerc, $cPerc) use (&$svg, $chartHeight, $marginTop, $chartWidth) {
            // Chart background
            $svg .= '<rect x="' . $startX . '" y="' . $marginTop . '" width="' . $chartWidth . '" height="' . $chartHeight . '" fill="#fafafa" stroke="#e5e7eb" stroke-width="0.5"/>';
            
            // Title
            $svg .= '<text x="' . ($startX + $chartWidth/2) . '" y="' . ($marginTop - 6) . '" font-size="9" font-weight="bold" text-anchor="middle" fill="#1f2937">' . $title . '</text>';
            
            // Grid lines for 1-7 scale
            for ($i = 1; $i <= 7; $i++) {
                $y = $marginTop + (($i - 1) / 6 * ($chartHeight - 25));
                $svg .= '<line x1="' . ($startX + 25) . '" y1="' . $y . '" x2="' . ($startX + $chartWidth - 8) . '" y2="' . $y . '" stroke="#f1f1f1" stroke-width="0.5"/>';
                
                $svg .= '<text x="' . ($startX + 20) . '" y="' . ($y + 2) . '" font-size="6" text-anchor="end" fill="#666">' . (8 - $i) . '</text>';
            }
            
            // Bars and values
            $dimensions = [
                ['D', $dVal, '#dc2626', $dPerc],
                ['I', $iVal, '#ea580c', $iPerc],
                ['S', $sVal, '#16a34a', $sPerc],
                ['C', $cVal, '#2563eb', $cPerc]
            ];
            
            $barWidth = 24; // Reduced from 28
            $barSpacing = 30; // Reduced from 35
            
            foreach ($dimensions as $index => $dim) {
                [$label, $value, $color, $percentage] = $dim;
                $barHeight = ($value / 7) * ($chartHeight - 25);
                $x = $startX + 35 + ($index * $barSpacing);
                $y = $marginTop + ($chartHeight - 25) - $barHeight;
                
                // Bar
                $svg .= '<rect x="' . $x . '" y="' . $y . '" width="' . $barWidth . '" height="' . $barHeight . '" fill="' . $color . '" opacity="0.8"/>';
                
                // Point marker
                $pointY = $marginTop + ($chartHeight - 25) - (($value - 1) / 6 * ($chartHeight - 25));
                $svg .= '<circle cx="' . ($x + $barWidth/2) . '" cy="' . $pointY . '" r="2" fill="' . $color . '" stroke="white" stroke-width="1"/>';
                
                // Dimension label
                $svg .= '<text x="' . ($x + $barWidth/2) . '" y="' . ($marginTop + $chartHeight - 14) . '" font-size="8" font-weight="bold" text-anchor="middle" fill="#333">' . $label . '</text>';
                
                // FIXED: Display segment value instead of percentage
                $svg .= '<text x="' . ($x + $barWidth/2) . '" y="' . ($marginTop + $chartHeight - 6) . '" font-size="7" font-weight="bold" text-anchor="middle" fill="' . $color . '">' . $value . '</text>';
                
                // Percentage as smaller subtitle
                $svg .= '<text x="' . ($x + $barWidth/2) . '" y="' . ($marginTop + $chartHeight + 3) . '" font-size="5" text-anchor="middle" fill="#666">(' . number_format($percentage, 1) . '%)</text>';
            }
            
            // Connecting line
            $points = [];
            foreach ($dimensions as $index => $dim) {
                [$label, $value, $color, $percentage] = $dim;
                $x = $startX + 35 + ($index * $barSpacing) + $barWidth/2;
                $pointY = $marginTop + ($chartHeight - 25) - (($value - 1) / 6 * ($chartHeight - 25));
                $points[] = $x . ',' . $pointY;
            }
            $svg .= '<polyline points="' . implode(' ', $points) . '" stroke="#4f46e5" stroke-width="2" fill="none" opacity="0.8"/>';
        };
        
        // Helper function to draw CHANGE chart (with negative values)
        $drawChangeChart = function($startX, $title, $dVal, $iVal, $sVal, $cVal) use (&$svg, $chartHeight, $marginTop, $chartWidth) {
            // Chart background
            $svg .= '<rect x="' . $startX . '" y="' . $marginTop . '" width="' . $chartWidth . '" height="' . $chartHeight . '" fill="#fafafa" stroke="#e5e7eb" stroke-width="0.5"/>';
            
            // Title
            $svg .= '<text x="' . ($startX + $chartWidth/2) . '" y="' . ($marginTop - 6) . '" font-size="9" font-weight="bold" text-anchor="middle" fill="#1f2937">' . $title . '</text>';
            
            // Grid lines for -4 to +4 scale
            $centerY = $marginTop + ($chartHeight - 25) / 2;
            for ($i = -4; $i <= 4; $i++) {
                $y = $centerY + ($i * -($chartHeight - 25) / 8);
                $isCenter = ($i == 0);
                $svg .= '<line x1="' . ($startX + 25) . '" y1="' . $y . '" x2="' . ($startX + $chartWidth - 8) . '" y2="' . $y . '" stroke="' . ($isCenter ? '#374151' : '#f1f1f1') . '" stroke-width="' . ($isCenter ? '1' : '0.5') . '"/>';
                
                $label = $i > 0 ? '+' . $i : (string)$i;
                $svg .= '<text x="' . ($startX + 20) . '" y="' . ($y + 2) . '" font-size="6" text-anchor="end" fill="#666">' . $label . '</text>';
            }
            
            // Bars and values for CHANGE
            $dimensions = [
                ['D', $dVal, '#dc2626'],
                ['I', $iVal, '#ea580c'], 
                ['S', $sVal, '#16a34a'],
                ['C', $cVal, '#2563eb']
            ];
            
            $barWidth = 24; // Reduced from 28
            $barSpacing = 30; // Reduced from 35
            
            foreach ($dimensions as $index => $dim) {
                [$label, $value, $color] = $dim;
                $x = $startX + 35 + ($index * $barSpacing);
                
                // FIXED: Draw bars correctly for negative values
                $barHeight = abs($value) * (($chartHeight - 25) / 8);
                
                if ($value >= 0) {
                    // Positive: bar goes up from center
                    $barY = $centerY - $barHeight;
                    $barColor = $color;
                } else {
                    // Negative: bar goes down from center  
                    $barY = $centerY;
                    $barColor = '#dc2626'; // Red for negative
                }
                
                // Draw bar if there's a value
                if ($barHeight > 0) {
                    $svg .= '<rect x="' . $x . '" y="' . $barY . '" width="' . $barWidth . '" height="' . $barHeight . '" fill="' . $barColor . '" opacity="0.8" stroke="' . $barColor . '" stroke-width="0.5"/>';
                }
                
                // Point marker at exact position
                $pointY = $centerY + ($value * -(($chartHeight - 25) / 8));
                $svg .= '<circle cx="' . ($x + $barWidth/2) . '" cy="' . $pointY . '" r="2" fill="' . ($value >= 0 ? $color : '#dc2626') . '" stroke="white" stroke-width="1"/>';
                
                // Line from center to point for clarity
                if ($value != 0) {
                    $svg .= '<line x1="' . ($x + $barWidth/2) . '" y1="' . $centerY . '" x2="' . ($x + $barWidth/2) . '" y2="' . $pointY . '" stroke="' . ($value >= 0 ? $color : '#dc2626') . '" stroke-width="1.5" opacity="0.6"/>';
                }
                
                // Dimension label
                $svg .= '<text x="' . ($x + $barWidth/2) . '" y="' . ($marginTop + $chartHeight - 14) . '" font-size="8" font-weight="bold" text-anchor="middle" fill="#333">' . $label . '</text>';
                
                // FIXED: Display actual change value (with + for positive)
                $displayValue = $value > 0 ? '+' . $value : (string)$value;
                $svg .= '<text x="' . ($x + $barWidth/2) . '" y="' . ($marginTop + $chartHeight - 6) . '" font-size="7" font-weight="bold" text-anchor="middle" fill="' . ($value >= 0 ? $color : '#dc2626') . '">' . $displayValue . '</text>';
            }
            
            // Connecting line for change values
            $points = [];
            foreach ($dimensions as $index => $dim) {
                [$label, $value, $color] = $dim;
                $x = $startX + 35 + ($index * $barSpacing) + $barWidth/2;
                $pointY = $centerY + ($value * -(($chartHeight - 25) / 8));
                $points[] = $x . ',' . $pointY;
            }
            $svg .= '<polyline points="' . implode(' ', $points) . '" stroke="#7c3aed" stroke-width="2" stroke-dasharray="2,2" fill="none" opacity="0.8"/>';
        };
        
        // Draw MOST chart
        $drawRegularChart(10, 'MOST (Publik)', 
            $discResult->most_d_segment ?? 1,
            $discResult->most_i_segment ?? 1,
            $discResult->most_s_segment ?? 1,
            $discResult->most_c_segment ?? 1,
            $discResult->most_d_percentage ?? 0,
            $discResult->most_i_percentage ?? 0,
            $discResult->most_s_percentage ?? 0,
            $discResult->most_c_percentage ?? 0
        );
        
        // Draw LEAST chart
        $drawRegularChart(205, 'LEAST (Pribadi)', 
            $discResult->least_d_segment ?? 1,
            $discResult->least_i_segment ?? 1,
            $discResult->least_s_segment ?? 1,
            $discResult->least_c_segment ?? 1,
            $discResult->least_d_percentage ?? 0,
            $discResult->least_i_percentage ?? 0,
            $discResult->least_s_percentage ?? 0,
            $discResult->least_c_percentage ?? 0
        );
        
        // Draw CHANGE chart (with negative values)
        $drawChangeChart(400, 'CHANGE (Adaptasi)', 
            $discResult->change_d_segment ?? 0,
            $discResult->change_i_segment ?? 0,
            $discResult->change_s_segment ?? 0,
            $discResult->change_c_segment ?? 0
        );
        
        // Add legend at bottom
        $legendY = $height - 15;
        $svg .= '<text x="80" y="' . $legendY . '" font-size="6" fill="#666">D = Dominance</text>';
        $svg .= '<text x="180" y="' . $legendY . '" font-size="6" fill="#666">I = Influence</text>';
        $svg .= '<text x="280" y="' . $legendY . '" font-size="6" fill="#666">S = Steadiness</text>';
        $svg .= '<text x="380" y="' . $legendY . '" font-size="6" fill="#666">C = Conscientiousness</text>';
        
        // Note about segments vs percentages
        $noteY = $height - 5;
        $svg .= '<text x="300" y="' . $noteY . '" font-size="5" text-anchor="middle" fill="#9ca3af">Segment values (1-7), persentase sebagai referensi. CHANGE dapat negatif.</text>';
        
        $svg .= '</svg>';
        
        return $svg;
    }
}