<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\BasketProducts;
use App\Entity\Product;
use App\Repository\BasketProductsRepository;
use App\Repository\BasketRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;


class BasketController extends AbstractController
{

    private $encoders;
    private $normalizers;
    private $serializer;
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->encoders = [new XmlEncoder(), new JsonEncoder()];
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function (object $object, string $format, array $context): string {
                return $object->getId();
            },
        ];
        $this->normalizers = [new ObjectNormalizer(null, null, null, null, null, null, $defaultContext)];
        $this->serializer = new Serializer($this->normalizers, $this->encoders);
        $this->em = $em;
    }

    #[Route('/basket', name: 'app_basket', methods: ['GET'])]
    public function index(BasketRepository $basketRepository): JsonResponse
    {
        $baskets = $basketRepository->findAll();

        $jsonContent = $this->serializer->serialize($baskets, 'json');

        return new JsonResponse($jsonContent, 200, [], true);
    }

    #[Route('/basket/{id}', name: 'app_basket_show', methods: ['GET'])]
    public function showBasket(Basket $basket): JsonResponse
    {
        $jsonContent = $this->serializer->serialize($basket, 'json');
        return new JsonResponse($jsonContent, 200, [], true);
    }

    #[Route('/basket/{id}', name: 'app_basket_delete', methods: ['DELETE'])]
    public function deleteBasket(Basket $basket): JsonResponse
    {
        $user = $basket->getUser();
        $user->setBasket(null);
        
        $this->em->persist($user);
        $this->em->remove($basket);
        $this->em->flush();
        return new JsonResponse(["Succès"=>"Votre panier a été supprimé !"], 204);
    }

    #[Route('/basket', name: 'app_basket_create', methods: ['POST'])]
    public function createBasket(Request $request, UserRepository $userRepository): JsonResponse
    {
        $data = $request->request->all();

        if (!array_key_exists('user', $data))
            return new JsonResponse(["Erreur" => "user manquant"], 400);

        $basket = new Basket();
        $user = $userRepository->find($data['user']);
        $basket->setUser($user);
        $user->setBasket($basket);

        $this->em->persist($basket);
        $this->em->flush();

        return new JsonResponse(["Succès" => "Votre panier a été créé !"], 201);
    }

    #[Route("/basket/{id}/add/{product_id}", name: "app_basket_add_product", methods: ["POST"])]
    public function addProduct(Basket $basket, int $product_id, Request $request): JsonResponse
    {
        $data = $request->request->all();

        if(!array_key_exists('qte', $data))
            return new JsonResponse(["Erreur" => "qte manquant"], 400);

        $product = $this->em->getRepository(Product::class)->find($product_id);
        $basketProduct = new BasketProducts();
        $basketProduct->setBasket($basket);
        $basketProduct->setProduct($product);
        $basketProduct->setQte(intval($data['qte']));
        $basket->addBasketProduct($basketProduct);
        $product->addBasketProduct($basketProduct);
        $this->em->persist($basketProduct);
        $this->em->flush();
        return new JsonResponse(["Succès" => "Votre produit a été ajouté au panier !"], 201);
    }

    #[Route("/basket/{id}/remove/{product_id}", name: "app_basket_remove_product", methods: ["DELETE"])]
    public function removeProduct(Basket $basket, int $product_id, ProductRepository $productRepository, BasketProductsRepository $basketProductsRepository): JsonResponse
    {
        $product = $productRepository->find($product_id);
        $basketProduct = $basketProductsRepository->findOneBy(['basket' => $basket, 'product' => $product]);
        $basket->removeBasketProduct($basketProduct);
        $product->removeBasketProduct($basketProduct);
        $this->em->remove($basketProduct);
        $this->em->flush();
        return new JsonResponse(["Succès" => "Votre produit a été supprimé du panier !"], 204);
    }

    #[Route('/basket/{id}/update/{product_id}', name: 'app_basket_update', methods: ['POST'])]
    public function updateBasket(Basket $basket, int $product_id, Request $request, BasketProductsRepository $basketProductsRepository): JsonResponse
    {
        $data = $request->request->all();
        $basketProduct = $basketProductsRepository->findOneBy(['basket' => $basket, 'product' => $product_id]);

        if (array_key_exists('qte', $data)) {
            $basketProduct->setQte(intval($data['qte']));
            $this->em->persist($basketProduct);
            $this->em->flush();
            return new JsonResponse(["Succès" => "Votre produit a été modifié !"], 200);
        }
        return new JsonResponse(["Erreur" => "qte manquant"], 400);
    }
}
