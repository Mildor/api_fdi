<?php

namespace App\Controller;

use App\Entity\Basket;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserController extends AbstractController
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

    #[Route('/user', name: 'app_user', methods: ['GET'])]
    public function index(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        $jsonContent = $this->serializer->serialize($users, 'json');

        return new JsonResponse($jsonContent, 200, [], true);
    }

    #[Route('/user/{id}', name: 'app_user_show', methods: ['GET'])]
    public function showUser(User $user): JsonResponse
    {
        $jsonContent = $this->serializer->serialize($user, 'json');
        return new JsonResponse($jsonContent, 200, [], true);
    }

    #[Route('/user/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function deleteUser(User $user): JsonResponse
    {
        $this->em->remove($user);
        $this->em->flush();
        return new JsonResponse(null, 204);
    }

    #[Route('/user/{id}', name: 'app_user_update', methods: ['PUT'])]
    public function updateUser(User $user, Request $request): JsonResponse
    {
        $data = $request->request->all();
        empty($data['email']) ? true : $user->setEmail($data['email']);
        empty($data['password']) ? true : $user->setPassword($data['password']);
        $this->em->persist($user);
        $this->em->flush();
        return new JsonResponse(null, 204);
    }

    #[Route('/user', name: 'app_user_create', methods: ['POST'])]
    public function createUser(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = $request->request->all();

        if (!array_key_exists('email', $data) || !array_key_exists('password', $data))
            return new JsonResponse(["Erreur" => "Email ou mot de passe manquant"], 400);

        $plainTextPassword = $data["password"];

        $user = new User();

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plainTextPassword
        );

        $user->setEmail($data['email']);
        $user->setPassword($hashedPassword);

        $basket = new Basket();
        $user->setBasket($basket);
        $basket->setUser($user);

        $this->em->persist($user);
        $this->em->persist($basket);
        $this->em->flush();

        return new JsonResponse(null, 201);
    }

    #[Route("/user/{id}/basket", name: "app_user_basket", methods: ["GET"])]
    public function showUserBasket(User $user): JsonResponse
    {
        $basket = $user->getBasket();
        $jsonContent = $this->serializer->serialize($basket, 'json');
        return new JsonResponse($jsonContent, 200, [], true);
    }

    #[Route("/user/login/", name: "app_user_login", methods: ["POST"])]
    public function login(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jWTToken, Security $security): JsonResponse
    {
        $data = $request->request->all();

        if(!array_key_exists('email', $data) || !array_key_exists('password', $data))
            return new JsonResponse(["Erreur" => "Email ou mot de passe manquant"], 400);

        $email = $data['email'];
        $password = $data['password'];

        $user = $userRepository->findOneBy(['email' => $email]);

        if(!$user || !$passwordHasher->isPasswordValid($user, $password))
            return new JsonResponse(["Erreur" => "Email ou mot de passe incorrect"], 400);

        $token = $jWTToken->create($user);
        
        return new JsonResponse(["token" => $token], 200);
    }
}
