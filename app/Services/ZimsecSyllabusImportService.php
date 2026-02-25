<?php

namespace App\Services;

use App\SyllabusTopic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class ZimsecSyllabusImportService
{
    protected $parser;
    protected $subjectId;
    protected $term;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Import syllabus topics from ZIMSEC PDF
     *
     * @param string $pdfPath
     * @param int $subjectId
     * @param string $term
     * @return array
     */
    public function importFromPdf($pdfPath, $subjectId, $term = 'Term 1')
    {
        $this->subjectId = $subjectId;
        $this->term = $term;

        try {
            $pdf = $this->parser->parseFile($pdfPath);
            $text = $pdf->getText();
            
            $topics = $this->parseTopics($text);
            
            return DB::transaction(function () use ($topics) {
                $imported = 0;
                $errors = [];

                foreach ($topics as $topicData) {
                    try {
                        SyllabusTopic::create([
                            'subject_id' => $this->subjectId,
                            'name' => $topicData['name'],
                            'description' => $topicData['description'],
                            'learning_objectives' => $topicData['learning_objectives'],
                            'term' => $this->term,
                            'difficulty_level' => $topicData['difficulty_level'],
                            'suggested_periods' => $topicData['suggested_periods'],
                            'order_index' => $topicData['order_index'],
                            'is_active' => true
                        ]);
                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = "Failed to import '{$topicData['name']}': " . $e->getMessage();
                        Log::error('Syllabus topic import error', [
                            'topic' => $topicData['name'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                return [
                    'success' => true,
                    'imported' => $imported,
                    'total' => count($topics),
                    'errors' => $errors
                ];
            });

        } catch (\Exception $e) {
            Log::error('PDF parsing error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'imported' => 0,
                'total' => 0,
                'errors' => ['PDF parsing failed: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Parse topics from PDF text
     *
     * @param string $text
     * @return array
     */
    protected function parseTopics($text)
    {
        $lines = explode("\n", $text);
        $topics = [];
        $currentMainTopic = null;
        $currentSubtopic = null;
        $collectingObjectives = false;
        $collectingContent = false;
        $orderIndex = 1;

        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            
            if (empty($line)) {
                continue;
            }

            // Detect main topic header (e.g., "8.6 FINANCE AND BANKING")
            if ($this->isMainTopicHeader($line)) {
                $currentMainTopic = $this->cleanTopicName($line);
                $currentSubtopic = null;
                $collectingObjectives = false;
                $collectingContent = false;
                continue;
            }

            // Skip FORM labels and other metadata
            if ($this->isFormLabel($line) || $this->isMetadata($line)) {
                continue;
            }

            // Detect subtopic (e.g., "Personal Finance", "Money")
            if ($this->isSubtopic($line) && $currentMainTopic) {
                // Save previous subtopic if exists
                if ($currentSubtopic) {
                    $topics[] = $this->buildTopicData($currentSubtopic, $orderIndex++);
                }

                // Start new subtopic
                $currentSubtopic = [
                    'main_topic' => $currentMainTopic,
                    'name' => $line,
                    'objectives' => [],
                    'content' => []
                ];
                $collectingObjectives = true;
                $collectingContent = false;
                continue;
            }

            // Collect bullet points
            if ($this->isBulletPoint($line) && $currentSubtopic) {
                $cleanedLine = $this->cleanBulletPoint($line);
                
                if ($collectingObjectives) {
                    $currentSubtopic['objectives'][] = $cleanedLine;
                } else {
                    $currentSubtopic['content'][] = $cleanedLine;
                }
                continue;
            }

            // Detect transition from objectives to content
            // If we encounter a non-bullet line after collecting objectives, switch to content
            if ($currentSubtopic && $collectingObjectives && !$this->isBulletPoint($line)) {
                // Check if this might be a content section header or just noise
                if ($this->isContentSectionStart($line)) {
                    $collectingObjectives = false;
                    $collectingContent = true;
                }
            }
        }

        // Save last subtopic
        if ($currentSubtopic) {
            $topics[] = $this->buildTopicData($currentSubtopic, $orderIndex);
        }

        return $topics;
    }

    /**
     * Check if line is a main topic header
     *
     * @param string $line
     * @return bool
     */
    protected function isMainTopicHeader($line)
    {
        // Matches patterns like "8.6 FINANCE AND BANKING"
        return preg_match('/^\d+\.\d+\s+[A-Z\s]+$/', $line);
    }

    /**
     * Check if line is a subtopic
     *
     * @param string $line
     * @return bool
     */
    protected function isSubtopic($line)
    {
        // Subtopic criteria:
        // - Starts with capital letter
        // - Not a bullet point
        // - Not all caps (to avoid headers)
        // - Not a FORM label
        // - Contains at least one lowercase letter (actual topic name)
        
        if ($this->isBulletPoint($line)) {
            return false;
        }

        if ($this->isFormLabel($line)) {
            return false;
        }

        if (preg_match('/^\d+\.\d+/', $line)) {
            return false;
        }

        // Check if it starts with capital and has mixed case
        if (preg_match('/^[A-Z][a-z]/', $line)) {
            return true;
        }

        // Allow title case (e.g., "Personal Finance")
        if (preg_match('/^[A-Z][a-zA-Z\s]+$/', $line) && preg_match('/[a-z]/', $line)) {
            // Make sure it's not too long (subtopics are usually short)
            if (strlen($line) < 50) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if line is a bullet point
     *
     * @param string $line
     * @return bool
     */
    protected function isBulletPoint($line)
    {
        // Matches lines starting with bullet symbols or dashes
        return preg_match('/^[•\-\*\◦\○]\s+/', $line) || 
               preg_match('/^[\x{2022}\x{2023}\x{25E6}\x{2043}\x{2219}]\s+/u', $line);
    }

    /**
     * Check if line is a FORM label
     *
     * @param string $line
     * @return bool
     */
    protected function isFormLabel($line)
    {
        return preg_match('/^FORM\s+\d+/i', $line);
    }

    /**
     * Check if line is metadata or noise
     *
     * @param string $line
     * @return bool
     */
    protected function isMetadata($line)
    {
        $metadataPatterns = [
            '/^LEARNING OBJECTIVES$/i',
            '/^CONTENT$/i',
            '/^Page\s+\d+/i',
            '/^\d+$/',
            '/^ZIMSEC/i',
            '/^SYLLABUS/i',
            '/^COMMERCE/i'
        ];

        foreach ($metadataPatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if line indicates start of content section
     *
     * @param string $line
     * @return bool
     */
    protected function isContentSectionStart($line)
    {
        // After objectives, we might see content headers or just more bullets
        // This is a heuristic - adjust based on actual PDF structure
        return strlen($line) < 100 && !$this->isBulletPoint($line);
    }

    /**
     * Clean bullet point text
     *
     * @param string $line
     * @return string
     */
    protected function cleanBulletPoint($line)
    {
        // Remove bullet symbols and trim
        $cleaned = preg_replace('/^[•\-\*\◦\○]\s+/', '', $line);
        $cleaned = preg_replace('/^[\x{2022}\x{2023}\x{25E6}\x{2043}\x{2219}]\s+/u', '', $cleaned);
        return trim($cleaned);
    }

    /**
     * Clean topic name
     *
     * @param string $line
     * @return string
     */
    protected function cleanTopicName($line)
    {
        // Remove leading numbers like "8.6 "
        $cleaned = preg_replace('/^\d+\.\d+\s+/', '', $line);
        return trim($cleaned);
    }

    /**
     * Build topic data array
     *
     * @param array $subtopic
     * @param int $orderIndex
     * @return array
     */
    protected function buildTopicData($subtopic, $orderIndex)
    {
        $name = $subtopic['main_topic'] . ' - ' . $subtopic['name'];
        
        $learningObjectives = !empty($subtopic['objectives']) 
            ? implode("\n", $subtopic['objectives']) 
            : '';
        
        $description = !empty($subtopic['content']) 
            ? implode("\n", $subtopic['content']) 
            : '';

        // If no content was found, use objectives as description
        if (empty($description) && !empty($learningObjectives)) {
            $description = 'Content to be covered: ' . implode(', ', $subtopic['objectives']);
        }

        return [
            'name' => $name,
            'learning_objectives' => $learningObjectives,
            'description' => $description,
            'difficulty_level' => 'medium',
            'suggested_periods' => 4,
            'order_index' => $orderIndex
        ];
    }

    /**
     * Get preview of topics without saving
     *
     * @param string $pdfPath
     * @return array
     */
    public function previewTopics($pdfPath)
    {
        try {
            $pdf = $this->parser->parseFile($pdfPath);
            $text = $pdf->getText();
            
            $topics = $this->parseTopics($text);
            
            return [
                'success' => true,
                'topics' => $topics,
                'count' => count($topics)
            ];

        } catch (\Exception $e) {
            Log::error('PDF preview error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'topics' => [],
                'count' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
}
