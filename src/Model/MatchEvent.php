<?php

declare(strict_types=1);

namespace App\Model;

final readonly class MatchEvent
{
    public function __construct(
        public int $id,
        public int $matchId,
        public ?int $matchVideoId,
        public ?int $playerUserId,
        public int $setNumber,
        public int $rallyNumber,
        public string $skill,
        public string $evaluation,
        public ?int $zoneFrom,
        public ?int $zoneTo,
        public int $videoTimestampSec,
        public ?string $description,
        public ?int $createdBy,
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
            matchVideoId: isset($row['match_video_id']) ? (int) $row['match_video_id'] : null,
            playerUserId: isset($row['player_user_id']) ? (int) $row['player_user_id'] : null,
            setNumber: (int) $row['set_number'],
            rallyNumber: (int) $row['rally_number'],
            skill: (string) $row['skill'],
            evaluation: (string) $row['evaluation'],
            zoneFrom: isset($row['zone_from']) ? (int) $row['zone_from'] : null,
            zoneTo: isset($row['zone_to']) ? (int) $row['zone_to'] : null,
            videoTimestampSec: (int) ($row['video_timestamp_sec'] ?? 0),
            description: isset($row['description']) ? (string) $row['description'] : null,
            createdBy: isset($row['created_by']) ? (int) $row['created_by'] : null,
            createdAt: new \DateTimeImmutable((string) $row['created_at']),
        );
    }

    public function formattedTimestamp(): string
    {
        $hours = intdiv($this->videoTimestampSec, 3600);
        $minutes = intdiv($this->videoTimestampSec % 3600, 60);
        $seconds = $this->videoTimestampSec % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'matchId' => $this->matchId,
            'matchVideoId' => $this->matchVideoId,
            'playerUserId' => $this->playerUserId,
            'setNumber' => $this->setNumber,
            'rallyNumber' => $this->rallyNumber,
            'skill' => $this->skill,
            'evaluation' => $this->evaluation,
            'zoneFrom' => $this->zoneFrom,
            'zoneTo' => $this->zoneTo,
            'videoTimestampSec' => $this->videoTimestampSec,
            'formattedTimestamp' => $this->formattedTimestamp(),
            'description' => $this->description,
            'createdBy' => $this->createdBy,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
