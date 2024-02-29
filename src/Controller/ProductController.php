<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class ProductController extends AbstractController
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

    #[Route('/product', name: 'app_product', methods: ['GET'])]
    public function index(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->findAll();

        $jsonContent = $this->serializer->serialize($products, 'json');

        return new JsonResponse($jsonContent, 200, [], true);
    }

    #[Route('/product/{id}', name: 'app_product_show', methods: ['GET'])]
    public function showProduct(Product $product): JsonResponse
    {
        $jsonContent = $this->serializer->serialize($product, 'json');
        return new JsonResponse($jsonContent, 200, [], true);
    }

    #[Route('/product/{id}', name: 'app_product_delete', methods: ['DELETE'])]
    public function deleteProduct(Product $product): JsonResponse
    {
        $this->em->remove($product);
        $this->em->flush();
        return new JsonResponse(null, 204);
    }

    #[Route('/product', name: 'app_product_create', methods: ['POST'])]
    public function createProduct(ProductRepository $productRepository, Request $request, CategoryRepository $categoryRepository): JsonResponse
    {
        $data = $request->request->all();

        if (!array_key_exists('label', $data) || !array_key_exists('price', $data) || !array_key_exists('description', $data) || !array_key_exists('qte', $data) || !array_key_exists('category_id', $data)) {
            return new JsonResponse('Des champs obligatoires sont manquants', 400);
        }

        $product = new Product();
        $product->setLabel($data['label']);
        $product->setPrice(floatval($data['price']));
        $product->setDescription($data['description']);
        $product->setQte(intval($data['qte']));
        $product->setCategory($categoryRepository->find(intval($data['category_id'])));

        //todo Image

        $this->em->persist($product);
        $this->em->flush();

        return new JsonResponse(null, 201);
    }

    #[Route('/product/{id}', name: 'app_product_update', methods: ['POST'])]
    public function updateProduct(Product $product, Request $request, CategoryRepository $categoryRepository): JsonResponse
    {
        $data = $request->request->all();
        $isOneField = false;

        if (array_key_exists('label', $data)) {
            $product->setLabel($data['label']);
            $isOneField = true;
        }

        if (array_key_exists('price', $data)) {
            $product->setPrice(floatval($data['price']));
            $isOneField = true;
        }

        if (array_key_exists('description', $data)) {
            $product->setDescription($data['description']);
            $isOneField = true;
        }

        if (array_key_exists('qte', $data)) {
            $product->setQte(intval($data['qte']));
            $isOneField = true;
        }

        if (array_key_exists('category_id', $data)) {
            $product->setCategory($categoryRepository->find(intval($data['category_id'])));
            $isOneField = true;
        }

        if ($isOneField) {
            $this->em->persist($product);
            $this->em->flush();
        } else {
            return new JsonResponse('Aucune donnée à modifier', 400);
        }


        return new JsonResponse(["Succès" => "Votre produit a bien été modifié."], 204);
    }
}
