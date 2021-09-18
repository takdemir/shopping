<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order implements \JsonSerializable
{
    use CreatedAtTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="orders")
     */
    private User $user;

    /**
     * @ORM\Column(type="float")
     */
    private float $total;

    /**
     * @ORM\OneToMany(targetEntity=OrderItem::class, mappedBy="orderId", orphanRemoval=true, cascade={"all"})
     */
    private $orderItems;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isActive = true;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->orderItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

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

    /**
     * @return Collection|OrderItem[]
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): self
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems[] = $orderItem;
            $orderItem->setOrderId($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): self
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrderId() === $this) {
                $orderItem->setOrderId(null);
            }
        }

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
     * @return array
     */
    public function jsonSerialize(): array
    {
        $user = $this->getUser();
        $orderItems = $this->getOrderItems();
        $preparedOrderItems = [];
        foreach ($orderItems as $orderItem) {
            $sub['id'] = $orderItem->getId();
            $sub['quantity'] = $orderItem->getQuantity();
            $sub['unitPrice'] = $orderItem->getUnitPrice();
            $sub['total'] = $orderItem->getTotal();
            $sub['isActive'] = $orderItem->getIsActive();
            $product = $orderItem->getProduct();
            $sub['product'] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'isActive' => $product->getIsActive(),
                'price' => $product->getPrice(),
                'stock' => $product->getStock(),
                'description' => $product->getDescription(),
                'currency' => $product->getCurrency(),
                'createdAt' => $product->getCreatedAt()
            ];
            $preparedOrderItems[] = $sub;
        }
        return [
            'id' => $this->getId(),
            'total' => $this->getTotal(),
            'isActive' => $this->getIsActive(),
            'createdAt' => $this->getCreatedAt(),
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail()
            ],
            'orderItems' => $preparedOrderItems
        ];
    }
}
