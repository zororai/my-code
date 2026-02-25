<?php

namespace App\Services;

use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Str;

class SyllabusPdfImportService
{
    /**
     * Import syllabus topics from PDF file
     *
     * @param string $pdfPath Path to the PDF file
     * @param int $subjectId Subject ID
     * @return array Array of topic data ready for database insertion
     */
    public function importFromPdf($pdfPath, $subjectId)
    {
        // Extract text from PDF
        $text = $this->extractTextFromPdf($pdfPath);

        // Normalize text
        $normalizedText = $this->normalizeText($text);

        // Parse topics from text
        $topics = $this->parseTopics($normalizedText, $subjectId);

        return $topics;
    }

    /**
     * Extract text from PDF file
     *
     * @param string $pdfPath
     * @return string
     */
    protected function extractTextFromPdf($pdfPath)
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($pdfPath);
        return $pdf->getText();
    }

    /**
     * Normalize text by removing excessive whitespace and normalizing line breaks
     *
     * @param string $text
     * @return string
     */
    protected function normalizeText($text)
    {
        // Normalize line breaks
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        // Remove excessive whitespace but preserve single line breaks
        $text = preg_replace('/[ \t]+/', ' ', $text);
        
        // Remove multiple consecutive line breaks (keep max 2)
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        return trim($text);
    }

    /**
     * Parse topics from normalized text
     *
     * @param string $text
     * @param int $subjectId
     * @return array
     */
    protected function parseTopics($text, $subjectId)
    {
        $topics = [];
        $orderIndex = 1;

        // Split text by TOPIC markers
        $blocks = preg_split('/TOPIC\s*\d*[:\-]*/i', $text);

        // Remove first element if empty (text before first TOPIC)
        if (isset($blocks[0]) && trim($blocks[0]) === '') {
            array_shift($blocks);
        }

        foreach ($blocks as $block) {
            $block = trim($block);
            
            if (empty($block)) {
                continue;
            }

            // Parse topic data from block
            $topicData = $this->parseTopicBlock($block, $subjectId, $orderIndex);

            if ($topicData) {
                $topics[] = $topicData;
                $orderIndex++;
            }
        }

        return $topics;
    }

    /**
     * Parse individual topic block
     *
     * @param string $block
     * @param int $subjectId
     * @param int $orderIndex
     * @return array|null
     */
    protected function parseTopicBlock($block, $subjectId, $orderIndex)
    {
        // Determine term from FORM markers
        $term = $this->determineTerm($block);

        if (!$term) {
            // Skip blocks without term information
            return null;
        }

        // Extract topic name (first line or detected title)
        $name = $this->extractTopicName($block);

        if (empty($name)) {
            $name = "Topic {$orderIndex}";
        }

        // Extract learning objectives
        $learningObjectives = $this->extractSection($block, 'LEARNING OBJECTIVES', 'CONTENT');

        // Extract content
        $content = $this->extractSection($block, 'CONTENT', 'SUGGESTED ACTIVITIES');

        // Extract suggested activities
        $activities = $this->extractSection($block, 'SUGGESTED ACTIVITIES', 'SUGGESTED RESOURCES');

        // Extract suggested resources
        $resources = $this->extractSection($block, 'SUGGESTED RESOURCES', null);

        // Build description from content, activities, and resources
        $description = $this->buildDescription($content, $activities, $resources);

        return [
            'subject_id' => $subjectId,
            'term' => $term,
            'name' => $name,
            'description' => $description,
            'learning_objectives' => $learningObjectives,
            'difficulty_level' => 'medium',
            'suggested_periods' => 4,
            'order_index' => $orderIndex,
            'is_active' => true,
        ];
    }

    /**
     * Determine term from FORM markers in block
     *
     * @param string $block
     * @return string|null
     */
    protected function determineTerm($block)
    {
        $formTermMap = [
            'FORM 1' => 'Term 1',
            'FORM 2' => 'Term 2',
            'FORM 3' => 'Term 3',
            'FORM 4' => 'Term 3',
        ];

        foreach ($formTermMap as $form => $term) {
            if (stripos($block, $form) !== false) {
                return $term;
            }
        }

        return null;
    }

    /**
     * Extract topic name from block (first line)
     *
     * @param string $block
     * @return string
     */
    protected function extractTopicName($block)
    {
        $lines = explode("\n", $block);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip FORM markers and empty lines
            if (empty($line) || stripos($line, 'FORM') !== false) {
                continue;
            }

            // Skip section headers
            if (preg_match('/^(LEARNING OBJECTIVES|CONTENT|SUGGESTED ACTIVITIES|SUGGESTED RESOURCES)/i', $line)) {
                continue;
            }

            // Return first valid line as topic name
            if (strlen($line) > 0 && strlen($line) < 200) {
                return $line;
            }
        }

        return '';
    }

    /**
     * Extract section content between two markers
     *
     * @param string $block
     * @param string $startMarker
     * @param string|null $endMarker
     * @return string|null
     */
    protected function extractSection($block, $startMarker, $endMarker = null)
    {
        if ($endMarker) {
            // Extract between two markers
            $pattern = '/' . preg_quote($startMarker, '/') . '[:\s]*(.*?)' . preg_quote($endMarker, '/') . '/is';
        } else {
            // Extract from marker to end
            $pattern = '/' . preg_quote($startMarker, '/') . '[:\s]*(.*)/is';
        }

        if (preg_match($pattern, $block, $matches)) {
            $content = trim($matches[1]);
            return !empty($content) ? $content : null;
        }

        return null;
    }

    /**
     * Build description from content, activities, and resources
     *
     * @param string|null $content
     * @param string|null $activities
     * @param string|null $resources
     * @return string
     */
    protected function buildDescription($content, $activities, $resources)
    {
        $parts = [];

        if ($content) {
            $parts[] = $content;
        }

        if ($activities) {
            $parts[] = "Suggested Activities:\n" . $activities;
        }

        if ($resources) {
            $parts[] = "Suggested Resources:\n" . $resources;
        }

        return implode("\n\n", $parts);
    }
}
