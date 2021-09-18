<?php

namespace App\Entity;

use App\Repository\DiscountRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DiscountRepository::class)
 */
class Discount
{
    use CreatedAtTrait;
    use StartAtTrait;
    use ExpireAtTrait;

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

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->startAt = null;
        $this->expireAt = null;
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
}
