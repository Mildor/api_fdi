<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CategoryController extends AbstractController
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

    #[Route('/category', name: 'app_category', methods: ['GET'])]
    public function allCategories(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();

        $jsonContent = $this->serializer->serialize($categories, 'json');

        return new JsonResponse($jsonContent, 200, [], true);
    }

    #[Route('/category/{id}', name: 'app_category_show', methods: ['GET'])]
    public function showCategory(Category $category): JsonResponse
    {
        $jsonContent = $this->serializer->serialize($category, 'json');

        return new JsonResponse($jsonContent, 200, [], true);
    }

    #[Route('/category/{id}', name: 'app_category_delete', methods: ['DELETE'])]
    public function deleteCategory(Category $category): JsonResponse
    {

        $this->em->remove($category);
        $this->em->flush();

        return new JsonResponse(["Succès" => "Votre catégorie est supprimée ! Tous les produits liés a cette catégorie seront supprimés !"], 204);
    }

    #[Route('/category', name: 'app_category_create', methods: ['POST'])]
    public function createCategory(Request $request): JsonResponse
    {
        $data = $request->request->all();

        if (!array_key_exists('label', $data))
            return new JsonResponse(["Erreur" => "Champ label manquant"], 400);

        $category = new Category();
        $category->setLabel($data['label']);

        $this->em->persist($category);
        $this->em->flush();

        return new JsonResponse(null, 201);
    }

    #[Route('/category/{id}', name: 'app_category_update', methods: ['POST'])]
    public function updateCategory(Category $category, Request $request): JsonResponse
    {
        $data = $request->request->all();

        $oldLabel = $category->getLabel();

        if (!array_key_exists('label', $data))
            return new JsonResponse(["Erreur" => "Champ label manquant"], 400);

        $category->setLabel($data['label']);
        $this->em->persist($category);
        $this->em->flush();

        return new JsonResponse(["Succès" => "La catégorie $oldLabel a été modifiée !"], 204);
    }
}
