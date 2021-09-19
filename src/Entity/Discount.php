<?php

namespace App\Entity;

use App\Repository\DiscountRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DiscountRepository::class)
 */
class Discount implements \JsonSerializable
{
    use CreatedAtTrait;

    public const DISCOUNT_CLASS_NAMES = [
        'BuyNDecreasePercentDiscount',
        'BuyNPayKDiscount',
        'PercentOverDiscount'
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="discounts")
     */
    private ?User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="discounts")
     */
    private ?Category $category;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="discounts")
     */
    private ?Product $product;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $discountCode;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private string $discountClassName;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isActive;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTime $startAt = null;

    /**
     * @ORM\Column(type="datetime")
     */
    private ?DateTime $expireAt = null;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private ?array $parameters = [];

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getDiscountCode(): string
    {
        return $this->discountCode;
    }

    public function setDiscountCode(string $discountCode): self
    {
        $this->discountCode = $discountCode;

        return $this;
    }

    public function getDiscountClassName(): string
    {
        return $this->discountClassName;
    }

    public function setDiscountClassName(string $discountClassName): self
    {
        $this->discountClassName = $discountClassName;

        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getStartAt(): ?DateTime
    {
        return $this->startAt;
    }

    public function setStartAt(?DateTime $startAt): void
    {
        if ($startAt) {
            $this->startAt = $startAt;
        }
    }

    public function getExpireAt(): ?DateTime
    {
        return $this->expireAt;
    }

    public function setExpireAt(?DateTime $expireAt): void
    {
        if ($expireAt) {
            $this->expireAt = $expireAt;
        }
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    public function setParameters(?array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $user = $this->getUser();
        $category = $this->getCategory();
        $product = $this->getProduct();
        return [
            'id' => $this->getId(),
            'discountCode' => $this->getDiscountCode(),
            'discountClassName' => $this->getDiscountClassName(),
            'isActive' => $this->getIsActive(),
            'createdAt' => $this->getCreatedAt() ? $this->getCreatedAt()->format('Y-m-d H:i:s') : null,
            'startAt' => $this->getStartAt() ? $this->getStartAt()->format('Y-m-d H:i:s') : null,
            'expireAt' => $this->getExpireAt() ? $this->getExpireAt()->format('Y-m-d H:i:s') : null,
            'parameters' => $this->getParameters(),
            'user' => $user ? [
                'id' => $user->getId(),
                'name' => $user->getName(),
            ] : null,
            'category' => $category ? [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'isActive' => $category->getIsActive(),
            ] : null,
            'product' => $product ? [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'isActive' => $product->getIsActive()
            ] : null,

        ];
    }
}
