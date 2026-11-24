<?php

declare(strict_types=1);

namespace App\Model;

final readonly class MatchVideo
{
    public function __construct(
        public int $id,
        public int $matchId,
        public ?int $uploadedBy,
        public string $title,
        public string $cameraAngle,
        public string $youtubeUrl,
        public string $youtubeVideoId,
        public ?int $durationSeconds,
        public \DateTimeImmutable $createdAt,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            matchId: (int) $row['match_id'],
            uploadedBy: isset($row['uploaded_by']) ? (int) $row['uploaded_by'] : null,
            title: (string) $row['title'],
            cameraAngle: (string) ($row['camera_angle'] ?? 'main'),
            youtubeUrl: (string) $row['youtube_url'],
            youtubeVideoId: (string) $row['youtube_video_id'],
            durationSeconds: isset($row['duration_seconds']) ? (int) $row['duration_seconds'] : null,
            createdAt: new \DateTimeImmutable((string) $row['created_at']),
        );
    }

    public function embedUrl(): string
    {
        return sprintf('https://www.youtube-nocookie.com/embed/%s', rawurlencode($this->youtubeVideoId));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'matchId' => $this->matchId,
            'uploadedBy' => $this->uploadedBy,
            'title' => $this->title,
            'cameraAngle' => $this->cameraAngle,
            'youtubeUrl' => $this->youtubeUrl,
            'youtubeVideoId' => $this->youtubeVideoId,
            'embedUrl' => $this->embedUrl(),
            'durationSeconds' => $this->durationSeconds,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
