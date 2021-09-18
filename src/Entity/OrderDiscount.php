<?php

namespace App\Entity;

use App\Repository\OrderDiscountRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderDiscountRepository::class)
 */
class OrderDiscount
{
    use CreatedAtTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="orderDiscounts")
     */
    private Order $orderId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $discountReason;

    /**
     * @ORM\Column(type="float", precision=8, scale=2)
     */
    private float $discountAmount;

    /**
     * @ORM\Column(type="float", precision=8, scale=2)
     */
    private float $totalDiscount;

    /**
     * @ORM\Column(type="float", precision=8, scale=2)
     */
    private float $discountedTotal;

    /**
     * @ORM\ManyToOne(targetEntity=OrderItem::class, inversedBy="orderDiscounts")
     */
    private ?OrderItem $orderItem;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?Order
    {
        return $this->orderId;
    }

    public function setOrderId(?Order $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getDiscountReason(): ?string
    {
        return $this->discountReason;
    }

    public function setDiscountReason(string $discountReason): self
    {
        $this->discountReason = $discountReason;

        return $this;
    }

    public function getDiscountAmount(): ?float
    {
        return $this->discountAmount;
    }

    public function setDiscountAmount(float $discountAmount): self
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    public function getTotalDiscount(): ?float
    {
        return $this->totalDiscount;
    }

    public function setTotalDiscount(float $totalDiscount): self
    {
        $this->totalDiscount = $totalDiscount;

        return $this;
    }

    public function getDiscountedTotal(): ?float
    {
        return $this->discountedTotal;
    }

    public function setDiscountedTotal(float $discountedTotal): self
    {
        $this->discountedTotal = $discountedTotal;

        return $this;
    }

    public function getOrderItem(): ?OrderItem
    {
        return $this->orderItem;
    }

    public function setOrderItem(?OrderItem $orderItem): self
    {
        $this->orderItem = $orderItem;

        return $this;
    }
}
