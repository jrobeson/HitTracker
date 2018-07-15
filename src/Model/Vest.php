<?php declare(strict_types=1);

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;
use App\Validator\Constraints as HitTrackerAssert;
use Sylius\Component\Resource\Model\ResourceInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @UniqueEntity("radioId")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="units",
 *            uniqueConstraints={
 *                @ORM\UniqueConstraint(name="idx_unit_radio_id",
 *                                      columns={"radio_id"}
 *                ),
 *            }
 * )
 */
class Vest implements ResourceInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=17, unique=true)
     * @Assert\NotBlank()
     * @HitTrackerAssert\MacAddress
     */
    private $radioId;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Choice(callback = "getUnitTypes")
     */
    private $unitType;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     * @Assert\Choice(callback = "getColors")
     */
    private $color;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $zones;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->id = 0;
        $this->radioId = '';
        $this->active = true;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setNumber(int $number): void
    {
        $this->id = $number;
    }

    public function getNumber(): ?int
    {
        return $this->id;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    /** @return string[] */
    public static function getColors(): array
    {
        return ['orange', 'green'];
    }

    public function setUnitType(string $unitType): void
    {
        $this->unitType = $unitType;
    }

    public function getUnitType(): ?string
    {
        return $this->unitType;
    }

    /** @return string[] */
    public static function getUnitTypes(): ?array
    {
        return ['vest', 'target'];
    }

    public function setZones(int $zones): void
    {
        $this->zones = $zones;
    }

    public function getZones(): ?int
    {
        return $this->zones;
    }

    public function setRadioId(string $radioId): void
    {
        $this->radioId = strtolower($radioId);
    }

    public function getRadioId(): string
    {
        return $this->radioId;
    }

    public function setActive(bool $active = true): void
    {
        $this->active = $active;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /** @ORM\PrePersist */
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }

    /** @ORM\PreUpdate */
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
