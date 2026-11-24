<?php

declare(strict_types=1);

namespace App\Model;

final readonly class PlayerProfile
{
    public function __construct(
        public int $userId,
        public ?int $jerseyNumber,
        public string $position,
        public ?int $heightCm,
        public ?int $reachAttackCm,
        public ?int $reachBlockCm,
        public string $dominantHand,
        public ?\DateTimeImmutable $birthDate,
        public string $status,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        $birthDateRaw = $row['birth_date'] ?? null;

        return new self(
            userId: (int) $row['user_id'],
            jerseyNumber: isset($row['jersey_number']) ? (int) $row['jersey_number'] : null,
            position: (string) $row['position'],
            heightCm: isset($row['height_cm']) ? (int) $row['height_cm'] : null,
            reachAttackCm: isset($row['reach_attack_cm']) ? (int) $row['reach_attack_cm'] : null,
            reachBlockCm: isset($row['reach_block_cm']) ? (int) $row['reach_block_cm'] : null,
            dominantHand: (string) ($row['dominant_hand'] ?? 'right'),
            birthDate: is_string($birthDateRaw) && $birthDateRaw !== ''
                ? new \DateTimeImmutable($birthDateRaw)
                : null,
            status: (string) ($row['status'] ?? 'active'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'jerseyNumber' => $this->jerseyNumber,
            'position' => $this->position,
            'heightCm' => $this->heightCm,
            'reachAttackCm' => $this->reachAttackCm,
            'reachBlockCm' => $this->reachBlockCm,
            'dominantHand' => $this->dominantHand,
            'birthDate' => $this->birthDate?->format('Y-m-d'),
            'status' => $this->status,
        ];
    }
}
