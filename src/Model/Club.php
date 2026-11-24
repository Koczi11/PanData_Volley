<?php

declare(strict_types=1);

namespace App\Model;

final readonly class Club
{
    public function __construct(
        public int $id,
        public string $name,
        public string $shortName,
        public string $slug,
        public string $city,
        public ?string $league,
        public ?string $logoPath,
        public bool $isActive,
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
            name: (string) $row['name'],
            shortName: (string) $row['short_name'],
            slug: (string) $row['slug'],
            city: (string) $row['city'],
            league: isset($row['league']) ? (string) $row['league'] : null,
            logoPath: isset($row['logo_path']) ? (string) $row['logo_path'] : null,
            isActive: filter_var($row['is_active'] ?? false, FILTER_VALIDATE_BOOL),
            createdBy: isset($row['created_by']) ? (int) $row['created_by'] : null,
            createdAt: new \DateTimeImmutable((string) $row['created_at']),
            updatedAt: new \DateTimeImmutable((string) $row['updated_at']),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'shortName' => $this->shortName,
            'slug' => $this->slug,
            'city' => $this->city,
            'league' => $this->league,
            'logoPath' => $this->logoPath,
            'isActive' => $this->isActive,
            'createdBy' => $this->createdBy,
            'createdAt' => $this->createdAt->format(\DateTimeInterface::ATOM),
            'updatedAt' => $this->updatedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
