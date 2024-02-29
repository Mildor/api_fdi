<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 75)]
    private ?string $label = null;

    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'category', orphanRemoval: true)]
    private Collection $Products;

    public function __construct()
    {
        $this->Products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->Products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->Products->contains($product)) {
            $this->Products->add($product);
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->Products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }
}
