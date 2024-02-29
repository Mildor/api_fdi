<?php

namespace App\Entity;

use App\Repository\BasketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BasketRepository::class)]
class Basket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: BasketProducts::class, mappedBy: 'Basket')]
    private Collection $basketProducts;

    #[ORM\OneToOne(mappedBy: 'Basket', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    public function __construct()
    {
        $this->basketProducts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, BasketProducts>
     */
    public function getBasketProducts(): Collection
    {
        return $this->basketProducts;
    }

    public function addBasketProduct(BasketProducts $basketProduct): static
    {
        if (!$this->basketProducts->contains($basketProduct)) {
            $this->basketProducts->add($basketProduct);
            $basketProduct->setBasket($this);
        }

        return $this;
    }

    public function removeBasketProduct(BasketProducts $basketProduct): static
    {
        if ($this->basketProducts->removeElement($basketProduct)) {
            // set the owning side to null (unless already changed)
            if ($basketProduct->getBasket() === $this) {
                $basketProduct->setBasket(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setBasket(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getBasket() !== $this) {
            $user->setBasket($this);
        }

        $this->user = $user;

        return $this;
    }
}
