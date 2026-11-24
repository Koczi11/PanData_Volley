<?php

declare(strict_types=1);

namespace App\Model;

final readonly class MatchEntity
{
    public function __construct(
        public int $id,
        public int $clubId,
        public string $opponentName,
        public string $competition,
        public string $season,
        public \DateTimeImmutable $matchDate,
        public ?string $location,
        public bool $isHome,
        public string $status,
        public int $clubSetsWon,
        public int $opponentSetsWon,
        public ?string $notes,
        public ?int $createdBy,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            clubId: (int) $row['club_id'],
            opponentName: (string) $row['opponent_name'],
            competition: (string) $row['competition'],
            season: (string) $row['season'],
            matchDate: new \DateTimeImmutable((string) $row['match_date']),
            location: isset($row['location']) ? (string) $row['location'] : null,
            isHome: filter_var($row['is_home'] ?? true, FILTER_VALIDATE_BOOL),
            status: (string) ($row['status'] ?? 'scheduled'),
            clubSetsWon: (int) ($row['club_sets_won'] ?? 0),
            opponentSetsWon: (int) ($row['opponent_sets_won'] ?? 0),
            notes: isset($row['notes']) ? (string) $row['notes'] : null,
            createdBy: isset($row['created_by']) ? (int) $row['created_by'] : null,
            createdAt: new \DateTimeImmutable((string) $row['created_at']),
            updatedAt: new \DateTimeImmutable((string) $row['updated_at']),
        );
    }

    public function scoreLabel(): string
    {
        return sprintf('%d:%d', $this->clubSetsWon, $this->opponentSetsWon);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'clubId' => $this->clubId,
            'opponentName' => $this->opponentName,
            'competition' => $this->competition,
            'season' => $this->season,
            'matchDate' => $this->matchDate->format(\DateTimeInterface::ATOM),
            'location' => $this->location,
            'isHome' => $this->isHome,
            'status' => $this->status,
            'clubSetsWon' => $this->clubSetsWon,
            'opponentSetsWon' => $this->opponentSetsWon,
            'scoreLabel' => $this->scoreLabel(),
            'notes' => $this->notes,
            'createdBy' => $this->createdBy,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->updatedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
