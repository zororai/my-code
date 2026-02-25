<?php

namespace App\Services;

use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Str;

class CambridgeSyllabusPdfImportService
{
    /**
     * Import Cambridge IGCSE syllabus topics from PDF file
     *
     * @param string $pdfPath Path to the PDF file
     * @param int $subjectId Subject ID
     * @param string $term Term (e.g., 'Term 1', 'Term 2', 'Term 3')
     * @return array Array of topic data ready for database insertion
     */
    public function importFromPdf($pdfPath, $subjectId, $term = 'Term 1')
    {
        // Extract text from PDF
        $text = $this->extractTextFromPdf($pdfPath);

        // Normalize text
        $normalizedText = $this->normalizeText($text);

        // Extract only subject content section
        $contentText = $this->extractSubjectContent($normalizedText);

        if (empty($contentText)) {
            return [];
        }

        // Parse topics from content
        $topics = $this->parseTopics($contentText, $subjectId, $term);

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
     * Extract subject content section from PDF text
     * Only content from "3 Subject content" onward
     *
     * @param string $text
     * @return string
     */
    protected function extractSubjectContent($text)
    {
        // Find "3 Subject content" or similar marker
        if (preg_match('/3\s+Subject\s+content/i', $text, $matches, PREG_OFFSET_CAPTURE)) {
            $startPos = $matches[0][1];
            return substr($text, $startPos);
        }

        // Fallback: try to find any numbered section that looks like main content
        if (preg_match('/\n\s*\d+\s+[A-Z][a-z]+\s+[a-z]+/i', $text, $matches, PREG_OFFSET_CAPTURE)) {
            $startPos = $matches[0][1];
            return substr($text, $startPos);
        }

        return $text;
    }

    /**
     * Parse topics from normalized content text
     *
     * @param string $text
     * @param int $subjectId
     * @param string $term
     * @return array
     */
    protected function parseTopics($text, $subjectId, $term)
    {
        $topics = [];
        $orderIndex = 1;

        // Split by main sections (e.g., "1 Data representation", "2 Communication")
        $mainSections = $this->splitIntoMainSections($text);

        foreach ($mainSections as $sectionNumber => $sectionData) {
            $sectionTitle = $sectionData['title'];
            $sectionContent = $sectionData['content'];

            // Split section into subsections (e.g., "1.1 Number systems", "1.2 Text")
            $subsections = $this->extractSubsections($sectionContent);

            foreach ($subsections as $subsection) {
                $topicData = $this->parseSubsection(
                    $subsection,
                    $sectionNumber,
                    $sectionTitle,
                    $subjectId,
                    $term,
                    $orderIndex
                );

                if ($topicData) {
                    $topics[] = $topicData;
                    $orderIndex++;
                }
            }
        }

        return $topics;
    }

    /**
     * Split text into main numbered sections
     *
     * @param string $text
     * @return array
     */
    protected function splitIntoMainSections($text)
    {
        $sections = [];
        
        // Match main sections like "1 Data representation", "2 Communication"
        // Pattern: single digit followed by space and capitalized title
        preg_match_all('/\n\s*(\d+)\s+([A-Z][^\n]+)\n/i', $text, $matches, PREG_OFFSET_CAPTURE);

        for ($i = 0; $i < count($matches[0]); $i++) {
            $sectionNumber = trim($matches[1][$i][0]);
            $sectionTitle = trim($matches[2][$i][0]);
            $startPos = $matches[0][$i][1];
            
            // Get content until next section or end of text
            if (isset($matches[0][$i + 1])) {
                $endPos = $matches[0][$i + 1][1];
                $content = substr($text, $startPos, $endPos - $startPos);
            } else {
                $content = substr($text, $startPos);
            }

            $sections[$sectionNumber] = [
                'title' => $sectionTitle,
                'content' => $content
            ];
        }

        return $sections;
    }

    /**
     * Extract subsections from section content
     *
     * @param string $content
     * @return array
     */
    protected function extractSubsections($content)
    {
        $subsections = [];
        
        // Match subsections like "1.1 Number systems", "1.2 Text"
        // Pattern: digit.digit followed by space and title
        preg_match_all('/(\d+\.\d+)\s+([^\n]+)\n/i', $content, $matches, PREG_OFFSET_CAPTURE);

        for ($i = 0; $i < count($matches[0]); $i++) {
            $subsectionNumber = trim($matches[1][$i][0]);
            $subsectionTitle = trim($matches[2][$i][0]);
            $startPos = $matches[0][$i][1];
            
            // Get content until next subsection or end of content
            if (isset($matches[0][$i + 1])) {
                $endPos = $matches[0][$i + 1][1];
                $subsectionContent = substr($content, $startPos, $endPos - $startPos);
            } else {
                $subsectionContent = substr($content, $startPos);
            }

            $subsections[] = [
                'number' => $subsectionNumber,
                'title' => $subsectionTitle,
                'content' => $subsectionContent
            ];
        }

        return $subsections;
    }

    /**
     * Parse individual subsection into topic data
     *
     * @param array $subsection
     * @param string $sectionNumber
     * @param string $sectionTitle
     * @param int $subjectId
     * @param string $term
     * @param int $orderIndex
     * @return array|null
     */
    protected function parseSubsection($subsection, $sectionNumber, $sectionTitle, $subjectId, $term, $orderIndex)
    {
        $subsectionNumber = $subsection['number'];
        $subsectionTitle = $subsection['title'];
        $content = $subsection['content'];

        // Combine section and subsection titles
        $topicName = "{$sectionNumber} {$sectionTitle} - {$subsectionNumber} {$subsectionTitle}";

        // Extract "Candidates should be able to:" section
        $learningObjectives = $this->extractCandidatesSection($content);

        // Extract "Notes and guidance" section
        $notesAndGuidance = $this->extractNotesAndGuidance($content);

        // Build description
        $description = $this->buildDescription($notesAndGuidance, $content);

        // Skip if no meaningful content
        if (empty($learningObjectives) && empty($description)) {
            return null;
        }

        return [
            'subject_id' => $subjectId,
            'syllabus_category' => 'cambridge',
            'term' => $term,
            'name' => $topicName,
            'description' => $description,
            'learning_objectives' => $learningObjectives,
            'difficulty_level' => 'medium',
            'suggested_periods' => 6,
            'order_index' => $orderIndex,
            'is_active' => true,
        ];
    }

    /**
     * Extract "Candidates should be able to:" section
     *
     * @param string $content
     * @return string|null
     */
    protected function extractCandidatesSection($content)
    {
        // Pattern to match "Candidates should be able to:" followed by content
        // until "Notes and guidance" or end
        $pattern = '/Candidates\s+should\s+be\s+able\s+to:\s*(.*?)(?:Notes\s+and\s+guidance|$)/is';
        
        if (preg_match($pattern, $content, $matches)) {
            $objectives = trim($matches[1]);
            
            // Clean up bullet points and formatting
            $objectives = $this->cleanBulletPoints($objectives);
            
            return !empty($objectives) ? $objectives : null;
        }

        return null;
    }

    /**
     * Extract "Notes and guidance" section
     *
     * @param string $content
     * @return string|null
     */
    protected function extractNotesAndGuidance($content)
    {
        // Pattern to match "Notes and guidance" followed by content
        $pattern = '/Notes\s+and\s+guidance\s*:?\s*(.*?)(?:\d+\.\d+\s+[A-Z]|$)/is';
        
        if (preg_match($pattern, $content, $matches)) {
            $notes = trim($matches[1]);
            
            // Clean up formatting
            $notes = $this->cleanBulletPoints($notes);
            
            return !empty($notes) ? $notes : null;
        }

        return null;
    }

    /**
     * Clean bullet points and formatting
     *
     * @param string $text
     * @return string
     */
    protected function cleanBulletPoints($text)
    {
        // Replace common bullet point markers with dashes
        $text = preg_replace('/^[•●○▪▫■□◦⦿⦾]\s*/m', '- ', $text);
        
        // Clean up excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\s*-\s*/', "\n- ", $text);
        
        return trim($text);
    }

    /**
     * Build description from notes and other content
     *
     * @param string|null $notes
     * @param string $fullContent
     * @return string
     */
    protected function buildDescription($notes, $fullContent)
    {
        $parts = [];

        if ($notes) {
            $parts[] = "Notes and Guidance:\n" . $notes;
        }

        // If no notes, try to extract some general content
        if (empty($parts)) {
            // Get first few lines of content as description
            $lines = explode("\n", $fullContent);
            $descLines = array_slice($lines, 0, 5);
            $desc = implode("\n", $descLines);
            $desc = trim($desc);
            
            if (!empty($desc) && strlen($desc) > 20) {
                $parts[] = $desc;
            }
        }

        return implode("\n\n", $parts);
    }
}
