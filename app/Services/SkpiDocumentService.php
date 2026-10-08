<?php

namespace App\Services;

use App\Models\SkpiRequest;
use App\Models\User;
use Carbon\Carbon;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use ZipArchive;

class SkpiDocumentService
{
    private const WORD_NAMESPACE = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    public function generate(SkpiRequest $skpiRequest): array
    {
        $user = $skpiRequest->user()->with('profile.studyProgram')->firstOrFail();
        $activities = $user->submissions()->with('rule')->where('status', 'approved')->oldest('started_at')->get();
        $number = 'SKPI/POLTEKSI/'.now()->format('Y').'/'.str_pad((string) $skpiRequest->id, 5, '0', STR_PAD_LEFT);
        $snapshot = $this->snapshot($user, $activities->all());

        $temporaryDirectory = storage_path('app/private/tmp/skpi-'.$skpiRequest->id.'-'.Str::uuid());
        File::ensureDirectoryExists($temporaryDirectory);

        try {
            $safeNim = preg_replace('/[^A-Za-z0-9_-]/', '-', (string) $snapshot['nim']) ?: (string) $skpiRequest->id;
            $baseName = 'SKPI-'.$safeNim;
            $temporaryDocx = $temporaryDirectory.DIRECTORY_SEPARATOR.$baseName.'.docx';
            $this->buildDocx($temporaryDocx, $snapshot, $number);

            $directory = 'skpi/'.$user->id;
            $docxPath = $directory.'/SKPI-'.$skpiRequest->id.'.docx';
            Storage::disk('local')->put($docxPath, File::get($temporaryDocx));

            return compact('number', 'snapshot', 'docxPath');
        } finally {
            File::deleteDirectory($temporaryDirectory);
        }
    }

    private function snapshot(User $user, array $activities): array
    {
        $profile = $user->profile;

        return [
            'name' => $user->name,
            'nim' => $profile->nim,
            'study_program' => $profile->studyProgram->name,
            'birthplace' => $profile->birthplace,
            'birthdate' => $profile->birthdate?->toDateString(),
            'cohort' => $profile->cohort,
            'graduation_year' => $profile->graduation_year,
            'diploma_number' => $profile->diploma_number,
            'academic_title' => $profile->academic_title,
            'learning_outcomes' => $profile->studyProgram->learning_outcomes ?? [],
            'issued_date' => now()->toDateString(),
            'activities' => collect($activities)->map(fn ($activity) => [
                'category' => $activity->rule->category,
                'activity_type' => $activity->rule->activity_type,
                'name' => $activity->activity_name,
                'organizer' => $activity->organizer,
                'year' => $activity->started_at->year,
                'points' => $activity->approved_points ?? $activity->estimated_points,
            ])->values()->all(),
        ];
    }

    private function buildDocx(string $destination, array $snapshot, string $number): void
    {
        $template = config('skpi.template_path', resource_path('templates/skpi-template.docx'));
        if (! is_file($template)) {
            throw new RuntimeException('Template SKPI tidak ditemukan.');
        }
        if (! copy($template, $destination)) {
            throw new RuntimeException('Template SKPI tidak dapat disalin.');
        }

        $zip = new ZipArchive;
        if ($zip->open($destination) !== true) {
            throw new RuntimeException('Template SKPI tidak dapat dibuka.');
        }

        try {
            $xml = $zip->getFromName('word/document.xml');
            if ($xml === false) {
                throw new RuntimeException('Isi template SKPI tidak lengkap.');
            }

            $document = new DOMDocument('1.0', 'UTF-8');
            $document->preserveWhiteSpace = true;
            if (! $document->loadXML($xml)) {
                throw new RuntimeException('Isi template SKPI tidak valid.');
            }
            $xpath = new DOMXPath($document);
            $xpath->registerNamespace('w', self::WORD_NAMESPACE);

            $this->replaceParagraphContaining($xpath, 'Nomor:', 'Nomor: '.$number);
            $this->fillIdentityTable($xpath, $snapshot);
            $this->fillLearningOutcomes($xpath, $snapshot['learning_outcomes']);
            $this->fillActivityTables($xpath, $snapshot['activities']);

            $issuedDate = Carbon::parse($snapshot['issued_date'])->locale('id')->translatedFormat('d F Y');
            $this->replaceExactParagraph($xpath, 'Gresik, …………………………', 'Gresik, '.$issuedDate);
            $this->replaceExactParagraph($xpath, '……………………………………', (string) config('skpi.signatory_name'));
            $this->replaceParagraphContaining($xpath, 'NIDN.', 'NIDN. '.config('skpi.signatory_nidn'));

            $zip->addFromString('word/document.xml', $document->saveXML());
        } finally {
            $zip->close();
        }
    }

    private function fillIdentityTable(DOMXPath $xpath, array $snapshot): void
    {
        $values = [
            'Nama Lengkap' => $snapshot['name'],
            'Tempat & Tanggal Lahir' => $snapshot['birthplace'].', '.Carbon::parse($snapshot['birthdate'])->locale('id')->translatedFormat('d F Y'),
            'Nomor Induk Mahasiswa' => $snapshot['nim'],
            'Tahun Masuk' => $snapshot['cohort'],
            'Tahun Lulus' => $snapshot['graduation_year'],
            'Nomor Ijazah Nasional' => $snapshot['diploma_number'],
            'Gelar Akademik' => $snapshot['academic_title'],
        ];

        foreach ($xpath->query('//w:tbl') as $table) {
            foreach ($xpath->query('./w:tr', $table) as $row) {
                $cells = $xpath->query('./w:tc', $row);
                if ($cells->length < 2) {
                    continue;
                }
                $label = trim($this->nodeText($xpath, $cells->item(0)));
                if (array_key_exists($label, $values)) {
                    $this->setNodeText($xpath, $cells->item(1), (string) $values[$label]);
                }
            }
        }
    }

    private function fillLearningOutcomes(DOMXPath $xpath, array $outcomes): void
    {
        foreach ($outcomes as $category => $items) {
            $heading = $this->findParagraph($xpath, fn (string $text) => preg_match('/^\d+\.\s*'.preg_quote($category, '/').'$/u', $text) === 1);
            if (! $heading) {
                continue;
            }

            $paragraphs = [];
            $cursor = $heading->nextSibling;
            while ($cursor) {
                if ($cursor instanceof DOMElement && $cursor->namespaceURI === self::WORD_NAMESPACE && $cursor->localName === 'p') {
                    $text = trim($this->nodeText($xpath, $cursor));
                    if (preg_match('/^\d+\.\s/u', $text) || $text === 'INFORMASI AKTIVITAS, PRESTASI, DAN SERTIFIKASI') {
                        break;
                    }
                    if ($text !== '') {
                        $paragraphs[] = $cursor;
                    }
                }
                $cursor = $cursor->nextSibling;
            }

            if ($paragraphs === []) {
                continue;
            }

            $items = array_values(array_filter($items, fn ($item) => filled($item)));
            $template = $paragraphs[0];
            foreach ($items as $index => $item) {
                if (isset($paragraphs[$index])) {
                    $this->setNodeText($xpath, $paragraphs[$index], (string) $item);

                    continue;
                }
                $clone = $template->cloneNode(true);
                $this->setNodeText($xpath, $clone, (string) $item);
                $heading->parentNode->insertBefore($clone, $cursor);
            }
            foreach (array_slice($paragraphs, count($items)) as $unused) {
                $unused->parentNode->removeChild($unused);
            }
        }
    }

    private function fillActivityTables(DOMXPath $xpath, array $activities): void
    {
        $groups = [[], [], []];
        foreach ($activities as $activity) {
            $isInternship = str_contains(Str::lower($activity['activity_type']), 'magang') || str_contains(Str::lower($activity['activity_type']), 'pkl');
            if ($activity['category'] === 'Prestasi Akademik dan Nonakademik' || $activity['category'] === 'Proyek, Penelitian, dan Pengabdian Masyarakat') {
                $groups[1][] = $activity;
            } elseif ($activity['category'] === 'Organisasi dan Kepemimpinan' || $isInternship) {
                $groups[2][] = $activity;
            } else {
                $groups[0][] = $activity;
            }
        }

        $activityTables = [];
        foreach ($xpath->query('//w:tbl') as $table) {
            $firstRow = $xpath->query('./w:tr[1]', $table)->item(0);
            if ($firstRow && str_contains($this->nodeText($xpath, $firstRow), 'Nama Kegiatan')) {
                $activityTables[] = $table;
            }
        }

        foreach ($activityTables as $index => $table) {
            $rows = $xpath->query('./w:tr', $table);
            if ($rows->length < 2) {
                continue;
            }
            $templateRow = $rows->item(1)->cloneNode(true);
            for ($rowIndex = $rows->length - 1; $rowIndex >= 1; $rowIndex--) {
                $row = $rows->item($rowIndex);
                $row->parentNode->removeChild($row);
            }

            $items = $groups[$index] ?? [];
            if ($items === []) {
                $emptyRow = $templateRow->cloneNode(true);
                $this->fillActivityRow($xpath, $emptyRow, ['', '', '', '', '']);
                $table->appendChild($emptyRow);

                continue;
            }

            foreach ($items as $activityIndex => $activity) {
                $row = $templateRow->cloneNode(true);
                $this->fillActivityRow($xpath, $row, [
                    (string) ($activityIndex + 1),
                    $activity['name'],
                    $activity['organizer'],
                    (string) $activity['year'],
                    (string) $activity['points'],
                ]);
                $table->appendChild($row);
            }
        }
    }

    private function fillActivityRow(DOMXPath $xpath, DOMNode $row, array $values): void
    {
        foreach ($xpath->query('./w:tc', $row) as $index => $cell) {
            $this->setNodeText($xpath, $cell, $values[$index] ?? '');
        }
    }

    private function replaceParagraphContaining(DOMXPath $xpath, string $needle, string $replacement): void
    {
        $paragraph = $this->findParagraph($xpath, fn (string $text) => str_contains($text, $needle));
        if ($paragraph) {
            $this->setNodeText($xpath, $paragraph, $replacement);
        }
    }

    private function replaceExactParagraph(DOMXPath $xpath, string $search, string $replacement): void
    {
        $paragraph = $this->findParagraph($xpath, fn (string $text) => trim($text) === $search);
        if ($paragraph) {
            $this->setNodeText($xpath, $paragraph, $replacement);
        }
    }

    private function findParagraph(DOMXPath $xpath, callable $matches): ?DOMElement
    {
        foreach ($xpath->query('//w:body//w:p') as $paragraph) {
            if ($matches(trim($this->nodeText($xpath, $paragraph)))) {
                return $paragraph;
            }
        }

        return null;
    }

    private function nodeText(DOMXPath $xpath, DOMNode $node): string
    {
        $text = '';
        foreach ($xpath->query('.//w:t', $node) as $textNode) {
            $text .= $textNode->nodeValue;
        }

        return $text;
    }

    private function setNodeText(DOMXPath $xpath, DOMNode $node, string $value): void
    {
        $textNodes = $xpath->query('.//w:t', $node);
        if ($textNodes->length === 0) {
            return;
        }
        $textNodes->item(0)->nodeValue = $value;
        for ($index = 1; $index < $textNodes->length; $index++) {
            $textNodes->item($index)->nodeValue = '';
        }
    }
}
