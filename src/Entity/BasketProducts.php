<?php

namespace App\Entity;

use App\Repository\BasketProductsRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;

#[ORM\Entity(repositoryClass: BasketProductsRepository::class)]
class BasketProducts
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'basketProducts')]
    #[JoinColumn(name: 'product_id', referencedColumnName: 'id')]
    private ?Product $Product = null;

    #[ORM\ManyToOne(inversedBy: 'basketProducts')]
    #[JoinColumn(name: 'basket_id', referencedColumnName: 'id')]
    private ?Basket $Basket = null;

    #[ORM\Column]
    private ?int $qte = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->Product;
    }

    public function setProduct(?Product $Product): static
    {
        $this->Product = $Product;

        return $this;
    }

    public function getBasket(): ?Basket
    {
        return $this->Basket;
    }

    public function setBasket(?Basket $Basket): static
    {
        $this->Basket = $Basket;

        return $this;
    }

    public function getQte(): ?int
    {
        return $this->qte;
    }

    public function setQte(int $qte): static
    {
        $this->qte = $qte;

        return $this;
    }
}
