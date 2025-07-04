<?php

namespace App\Services;

class KraeplinChartGenerator
{
    // Method utama untuk chart gabungan - diperbaiki
    public static function generateChart($candidate)
    {
        $kraeplinResult = $candidate->kraeplinTestResult;
        if (!$kraeplinResult) return '';
        
        // Decode data dengan cara yang sama seperti di JavaScript
        $correctCounts = [];
        $answeredCounts = [];
        $avgTimes = []; // Gunakan column_avg_time, bukan column_response_times
        
        if (is_string($kraeplinResult->column_correct_count)) {
            $correctCounts = json_decode($kraeplinResult->column_correct_count, true) ?? [];
        } elseif (is_array($kraeplinResult->column_correct_count)) {
            $correctCounts = $kraeplinResult->column_correct_count;
        }
        
        if (is_string($kraeplinResult->column_answered_count)) {
            $answeredCounts = json_decode($kraeplinResult->column_answered_count, true) ?? [];
        } elseif (is_array($kraeplinResult->column_answered_count)) {
            $answeredCounts = $kraeplinResult->column_answered_count;
        }
        
        // PERBAIKAN: Gunakan column_avg_time, bukan column_response_times
        if (is_string($kraeplinResult->column_avg_time)) {
            $avgTimes = json_decode($kraeplinResult->column_avg_time, true) ?? [];
        } elseif (is_array($kraeplinResult->column_avg_time)) {
            $avgTimes = $kraeplinResult->column_avg_time;
        }
        
        // Jika tidak ada avg_time, buat estimasi
        if (empty($avgTimes)) {
            $averageTime = $kraeplinResult->average_response_time ?? 7.5;
            for ($i = 0; $i < 32; $i++) {
                $answered = $answeredCounts[$i] ?? 0;
                $avgTimes[$i] = $answered > 0 ? $averageTime : 0;
            }
        }
        
        if (empty($correctCounts) && empty($answeredCounts)) {
            return '';
        }
        
        // Generate SVG
        $width = 500;
        $height = 200;
        $marginLeft = 35;
        $marginTop = 20;
        $marginRight = 25;
        $marginBottom = 40;
        $chartWidth = $width - $marginLeft - $marginRight;
        $chartHeight = $height - $marginTop - $marginBottom;
        
        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        
        // Background
        $svg .= '<rect width="' . $width . '" height="' . $height . '" fill="white" stroke="#e5e7eb" stroke-width="1"/>';
        
        // Grid lines untuk questions (0-26)
        for ($i = 0; $i <= 26; $i += 5) {
            $y = $marginTop + ($i / 26 * $chartHeight);
            $svg .= '<line x1="' . $marginLeft . '" y1="' . $y . '" x2="' . ($width - $marginRight) . '" y2="' . $y . '" stroke="#f1f1f1" stroke-width="0.5"/>';
        }
        
        // Left Y-axis labels (Questions)
        for ($i = 0; $i <= 26; $i += 5) {
            $y = $marginTop + ((26 - $i) / 26 * $chartHeight);
            $svg .= '<text x="' . ($marginLeft - 5) . '" y="' . ($y + 3) . '" font-size="7" text-anchor="end" fill="#666">' . $i . '</text>';
        }
        
        // Right Y-axis labels (Time in seconds)
        for ($i = 0; $i <= 15; $i += 5) {
            $y = $marginTop + ((15 - $i) / 15 * $chartHeight);
            $svg .= '<text x="' . ($width - $marginRight + 5) . '" y="' . ($y + 3) . '" font-size="7" text-anchor="start" fill="#ef4444">' . $i . '</text>';
        }
        
        // Data points and lines
        $maxColumns = 32;
        $stepX = $chartWidth / ($maxColumns - 1);
        
        // Correct answers line (blue)
        if (!empty($correctCounts)) {
            $points = [];
            for ($i = 0; $i < $maxColumns; $i++) {
                $x = $marginLeft + ($i * $stepX);
                $value = $correctCounts[$i] ?? 0;
                $y = $marginTop + ((26 - $value) / 26 * $chartHeight);
                $points[] = $x . ',' . $y;
                
                $svg .= '<circle cx="' . $x . '" cy="' . $y . '" r="1.5" fill="#2563eb"/>';
            }
            
            if (count($points) > 1) {
                $svg .= '<polyline points="' . implode(' ', $points) . '" fill="none" stroke="#2563eb" stroke-width="1.5"/>';
            }
        }
        
        // Answered questions line (green)
        if (!empty($answeredCounts)) {
            $points = [];
            for ($i = 0; $i < $maxColumns; $i++) {
                $x = $marginLeft + ($i * $stepX);
                $value = $answeredCounts[$i] ?? 0;
                $y = $marginTop + ((26 - $value) / 26 * $chartHeight);
                $points[] = $x . ',' . $y;
                
                $svg .= '<circle cx="' . $x . '" cy="' . $y . '" r="1.5" fill="#16a34a"/>';
            }
            
            if (count($points) > 1) {
                $svg .= '<polyline points="' . implode(' ', $points) . '" fill="none" stroke="#16a34a" stroke-width="1.5"/>';
            }
        }
        
        // Average time line (red, mapped to right axis)
        if (!empty($avgTimes)) {
            $points = [];
            for ($i = 0; $i < $maxColumns; $i++) {
                $x = $marginLeft + ($i * $stepX);
                $value = min($avgTimes[$i] ?? 0, 15); // Cap at 15 seconds
                $y = $marginTop + ((15 - $value) / 15 * $chartHeight);
                $points[] = $x . ',' . $y;
                
                $svg .= '<circle cx="' . $x . '" cy="' . $y . '" r="1.5" fill="#ef4444"/>';
            }
            
            if (count($points) > 1) {
                $svg .= '<polyline points="' . implode(' ', $points) . '" fill="none" stroke="#ef4444" stroke-width="1.5"/>';
            }
        }
        
        // X-axis labels
        for ($i = 0; $i < $maxColumns; $i++) {
            $x = $marginLeft + ($i * $stepX);
            $label = $i + 1;
            
            if ($i % 8 == 0 || $i == $maxColumns - 1) {
                $svg .= '<text x="' . $x . '" y="' . ($height - 15) . '" font-size="7" text-anchor="middle" fill="#666">' . $label . '</text>';
            }
        }
        
        // Legend
        $legendY = $height - 5;
        $svg .= '<text x="' . ($width / 2 - 80) . '" y="' . $legendY . '" font-size="7" fill="#2563eb">● Benar</text>';
        $svg .= '<text x="' . ($width / 2 - 20) . '" y="' . $legendY . '" font-size="7" fill="#16a34a">● Dijawab</text>';
        $svg .= '<text x="' . ($width / 2 + 40) . '" y="' . $legendY . '" font-size="7" fill="#ef4444">● Waktu</text>';
        
        $svg .= '</svg>';
        
        return $svg;
    }

    // Method untuk chart kecepatan - diperbaiki
    public static function generateSpeedChart($candidate)
    {
        $kraeplinResult = $candidate->kraeplinTestResult;
        if (!$kraeplinResult) return '';
        
        // PERBAIKAN: Gunakan column_avg_time, bukan column_response_times
        $avgTimes = [];
        if (is_string($kraeplinResult->column_avg_time)) {
            $avgTimes = json_decode($kraeplinResult->column_avg_time, true) ?? [];
        } elseif (is_array($kraeplinResult->column_avg_time)) {
            $avgTimes = $kraeplinResult->column_avg_time;
        }
        
        // Jika tidak ada data avg_time, buat estimasi
        if (empty($avgTimes)) {
            $answeredCounts = [];
            if (is_string($kraeplinResult->column_answered_count)) {
                $answeredCounts = json_decode($kraeplinResult->column_answered_count, true) ?? [];
            } elseif (is_array($kraeplinResult->column_answered_count)) {
                $answeredCounts = $kraeplinResult->column_answered_count;
            }
            
            $averageTime = $kraeplinResult->average_response_time ?? 7.5;
            
            for ($i = 0; $i < 32; $i++) {
                $answered = $answeredCounts[$i] ?? 0;
                $avgTimes[$i] = $answered > 0 ? $averageTime : 0;
            }
        }

        // Generate SVG
        $width = 500;
        $height = 180;
        $marginLeft = 35;
        $marginTop = 20;
        $marginRight = 20;
        $marginBottom = 35;
        $chartWidth = $width - $marginLeft - $marginRight;
        $chartHeight = $height - $marginTop - $marginBottom;
        
        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        
        // Background
        $svg .= '<rect width="' . $width . '" height="' . $height . '" fill="white" stroke="#e5e7eb" stroke-width="1"/>';
        
        // Grid lines (0-15 seconds)
        for ($i = 0; $i <= 15; $i += 3) {
            $y = $marginTop + ((15 - $i) / 15 * $chartHeight);
            $svg .= '<line x1="' . $marginLeft . '" y1="' . $y . '" x2="' . ($width - $marginRight) . '" y2="' . $y . '" stroke="#f1f1f1" stroke-width="0.5"/>';
        }
        
        // Y-axis labels
        for ($i = 0; $i <= 15; $i += 3) {
            $y = $marginTop + ((15 - $i) / 15 * $chartHeight);
            $svg .= '<text x="' . ($marginLeft - 5) . '" y="' . ($y + 3) . '" font-size="7" text-anchor="end" fill="#666">' . $i . '</text>';
        }
        
        // Data area and line
        $maxColumns = 32;
        $stepX = $chartWidth / ($maxColumns - 1);
        
        $points = [];
        $areaPoints = [];
        
        for ($i = 0; $i < $maxColumns; $i++) {
            $x = $marginLeft + ($i * $stepX);
            $value = min($avgTimes[$i] ?? 0, 15); // Cap at 15 seconds
            $y = $marginTop + ((15 - $value) / 15 * $chartHeight);
            
            $points[] = $x . ',' . $y;
            $areaPoints[] = $x . ',' . $y;
            
            // Draw point
            $svg .= '<circle cx="' . $x . '" cy="' . $y . '" r="1.5" fill="#ef4444"/>';
        }
        
        // Create area path
        if (count($areaPoints) > 0) {
            $areaPoints[] = ($marginLeft + (($maxColumns - 1) * $stepX)) . ',' . ($marginTop + $chartHeight);
            $areaPoints[] = $marginLeft . ',' . ($marginTop + $chartHeight);
            
            $svg .= '<polygon points="' . implode(' ', $areaPoints) . '" fill="#ef4444" fill-opacity="0.3"/>';
        }
        
        // Draw line
        if (count($points) > 1) {
            $svg .= '<polyline points="' . implode(' ', $points) . '" fill="none" stroke="#ef4444" stroke-width="1.5"/>';
        }
        
        // X-axis labels
        for ($i = 0; $i < $maxColumns; $i++) {
            $x = $marginLeft + ($i * $stepX);
            $label = $i + 1;
            
            if ($i % 8 == 0 || $i == $maxColumns - 1) {
                $svg .= '<text x="' . $x . '" y="' . ($height - 15) . '" font-size="7" text-anchor="middle" fill="#666">' . $label . '</text>';
            }
        }
        
        $svg .= '</svg>';
        
        return $svg;
    }

    // Method lainnya tetap sama...
    public static function generateAccuracyChart($candidate)
    {
        $kraeplinResult = $candidate->kraeplinTestResult;
        if (!$kraeplinResult) return '';
        
        // Decode data
        $correctCounts = [];
        $answeredCounts = [];
        
        if (is_string($kraeplinResult->column_correct_count)) {
            $correctCounts = json_decode($kraeplinResult->column_correct_count, true) ?? [];
        } elseif (is_array($kraeplinResult->column_correct_count)) {
            $correctCounts = $kraeplinResult->column_correct_count;
        }
        
        if (is_string($kraeplinResult->column_answered_count)) {
            $answeredCounts = json_decode($kraeplinResult->column_answered_count, true) ?? [];
        } elseif (is_array($kraeplinResult->column_answered_count)) {
            $answeredCounts = $kraeplinResult->column_answered_count;
        }
        
        if (empty($correctCounts) || empty($answeredCounts)) {
            return '';
        }

        // Calculate accuracy percentages
        $accuracyPercentages = [];
        for ($i = 0; $i < 32; $i++) {
            $correct = $correctCounts[$i] ?? 0;
            $answered = $answeredCounts[$i] ?? 0;
            $accuracyPercentages[$i] = $answered > 0 ? ($correct / $answered) * 100 : 0;
        }

        // Generate SVG
        $width = 500;
        $height = 180;
        $marginLeft = 35;
        $marginTop = 20;
        $marginRight = 20;
        $marginBottom = 35;
        $chartWidth = $width - $marginLeft - $marginRight;
        $chartHeight = $height - $marginTop - $marginBottom;
        
        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        
        // Background
        $svg .= '<rect width="' . $width . '" height="' . $height . '" fill="white" stroke="#e5e7eb" stroke-width="1"/>';
        
        // Grid lines (0-100%)
        for ($i = 0; $i <= 100; $i += 25) {
            $y = $marginTop + ((100 - $i) / 100 * $chartHeight);
            $svg .= '<line x1="' . $marginLeft . '" y1="' . $y . '" x2="' . ($width - $marginRight) . '" y2="' . $y . '" stroke="#f1f1f1" stroke-width="0.5"/>';
        }
        
        // Y-axis labels
        for ($i = 0; $i <= 100; $i += 25) {
            $y = $marginTop + ((100 - $i) / 100 * $chartHeight);
            $svg .= '<text x="' . ($marginLeft - 5) . '" y="' . ($y + 3) . '" font-size="7" text-anchor="end" fill="#666">' . $i . '</text>';
        }
        
        // Data area and line
        $maxColumns = 32;
        $stepX = $chartWidth / ($maxColumns - 1);
        
        $points = [];
        $areaPoints = [];
        
        for ($i = 0; $i < $maxColumns; $i++) {
            $x = $marginLeft + ($i * $stepX);
            $value = $accuracyPercentages[$i];
            $y = $marginTop + ((100 - $value) / 100 * $chartHeight);
            
            $points[] = $x . ',' . $y;
            $areaPoints[] = $x . ',' . $y;
            
            // Draw point
            $svg .= '<circle cx="' . $x . '" cy="' . $y . '" r="1.5" fill="#10b981"/>';
        }
        
        // Create area path
        if (count($areaPoints) > 0) {
            $areaPoints[] = ($marginLeft + (($maxColumns - 1) * $stepX)) . ',' . ($marginTop + $chartHeight);
            $areaPoints[] = $marginLeft . ',' . ($marginTop + $chartHeight);
            
            $svg .= '<polygon points="' . implode(' ', $areaPoints) . '" fill="#10b981" fill-opacity="0.3"/>';
        }
        
        // Draw line
        if (count($points) > 1) {
            $svg .= '<polyline points="' . implode(' ', $points) . '" fill="none" stroke="#10b981" stroke-width="1.5"/>';
        }
        
        // X-axis labels
        for ($i = 0; $i < $maxColumns; $i++) {
            $x = $marginLeft + ($i * $stepX);
            $label = $i + 1;
            
            if ($i % 8 == 0 || $i == $maxColumns - 1) {
                $svg .= '<text x="' . $x . '" y="' . ($height - 15) . '" font-size="7" text-anchor="middle" fill="#666">' . $label . '</text>';
            }
        }
        
        $svg .= '</svg>';
        
        return $svg;
    }

    public static function generateAnsweredChart($candidate)
    {
        $kraeplinResult = $candidate->kraeplinTestResult;
        if (!$kraeplinResult) return '';
        
        // Decode data
        $answeredCounts = [];
        
        if (is_string($kraeplinResult->column_answered_count)) {
            $answeredCounts = json_decode($kraeplinResult->column_answered_count, true) ?? [];
        } elseif (is_array($kraeplinResult->column_answered_count)) {
            $answeredCounts = $kraeplinResult->column_answered_count;
        }
        
        if (empty($answeredCounts)) {
            return '';
        }

        // Generate SVG
        $width = 500;
        $height = 180;
        $marginLeft = 35;
        $marginTop = 20;
        $marginRight = 20;
        $marginBottom = 35;
        $chartWidth = $width - $marginLeft - $marginRight;
        $chartHeight = $height - $marginTop - $marginBottom;
        
        $svg = '<svg width="' . $width . '" height="' . $height . '" xmlns="http://www.w3.org/2000/svg">';
        
        // Background
        $svg .= '<rect width="' . $width . '" height="' . $height . '" fill="white" stroke="#e5e7eb" stroke-width="1"/>';
        
        // Grid lines (0-26)
        for ($i = 0; $i <= 26; $i += 5) {
            $y = $marginTop + ((26 - $i) / 26 * $chartHeight);
            $svg .= '<line x1="' . $marginLeft . '" y1="' . $y . '" x2="' . ($width - $marginRight) . '" y2="' . $y . '" stroke="#f1f1f1" stroke-width="0.5"/>';
        }
        
        // Y-axis labels
        for ($i = 0; $i <= 26; $i += 5) {
            $y = $marginTop + ((26 - $i) / 26 * $chartHeight);
            $svg .= '<text x="' . ($marginLeft - 5) . '" y="' . ($y + 3) . '" font-size="7" text-anchor="end" fill="#666">' . $i . '</text>';
        }
        
        // Data area and line
        $maxColumns = 32;
        $stepX = $chartWidth / ($maxColumns - 1);
        
        $points = [];
        $areaPoints = [];
        
        for ($i = 0; $i < $maxColumns; $i++) {
            $x = $marginLeft + ($i * $stepX);
            $value = $answeredCounts[$i] ?? 0;
            $y = $marginTop + ((26 - $value) / 26 * $chartHeight);
            
            $points[] = $x . ',' . $y;
            $areaPoints[] = $x . ',' . $y;
            
            // Draw point
            $svg .= '<circle cx="' . $x . '" cy="' . $y . '" r="1.5" fill="#6366f1"/>';
        }
        
        // Create area path
        if (count($areaPoints) > 0) {
            $areaPoints[] = ($marginLeft + (($maxColumns - 1) * $stepX)) . ',' . ($marginTop + $chartHeight);
            $areaPoints[] = $marginLeft . ',' . ($marginTop + $chartHeight);
            
            $svg .= '<polygon points="' . implode(' ', $areaPoints) . '" fill="#6366f1" fill-opacity="0.3"/>';
        }
        
        // Draw line
        if (count($points) > 1) {
            $svg .= '<polyline points="' . implode(' ', $points) . '" fill="none" stroke="#6366f1" stroke-width="1.5"/>';
        }
        
        // X-axis labels
        for ($i = 0; $i < $maxColumns; $i++) {
            $x = $marginLeft + ($i * $stepX);
            $label = $i + 1;
            
            if ($i % 8 == 0 || $i == $maxColumns - 1) {
                $svg .= '<text x="' . $x . '" y="' . ($height - 15) . '" font-size="7" text-anchor="middle" fill="#666">' . $label . '</text>';
            }
        }
        
        $svg .= '</svg>';
        
        return $svg;
    }
}