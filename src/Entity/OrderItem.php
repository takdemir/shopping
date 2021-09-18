<?php

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderItemRepository::class)
 */
class OrderItem
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="orderItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private Order $orderId;

    /**
     * @ORM\ManyToOne(targetEntity=Product::class, inversedBy="orderItems")
     * @ORM\JoinColumn(nullable=false)
     */
    private Product $product;

    /**
     * @ORM\Column(type="integer")
     */
    private int $quantity;

    /**
     * @ORM\Column(type="float")
     */
    private float $unitPrice;

    /**
     * @ORM\Column(type="float")
     */
    private float $total;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isActive = true;

    /**
     * @ORM\OneToMany(targetEntity=OrderDiscount::class, mappedBy="orderItem")
     */
    private $orderDiscounts;

    public function __construct()
    {
        $this->orderDiscounts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): Order
    {
        return $this->orderId;
    }

    public function setOrderId(Order $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPrice(): ?float
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(float $unitPrice): self
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection|OrderDiscount[]
     */
    public function getOrderDiscounts(): Collection
    {
        return $this->orderDiscounts;
    }

    public function addOrderDiscount(OrderDiscount $orderDiscount): self
    {
        if (!$this->orderDiscounts->contains($orderDiscount)) {
            $this->orderDiscounts[] = $orderDiscount;
            $orderDiscount->setOrderItem($this);
        }

        return $this;
    }

    public function removeOrderDiscount(OrderDiscount $orderDiscount): self
    {
        if ($this->orderDiscounts->removeElement($orderDiscount)) {
            // set the owning side to null (unless already changed)
            if ($orderDiscount->getOrderItem() === $this) {
                $orderDiscount->setOrderItem(null);
            }
        }

        return $this;
    }
}
