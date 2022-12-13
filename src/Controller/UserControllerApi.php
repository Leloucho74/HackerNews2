<?php

namespace App\Controller;

use App\Form\UserType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/profile')]
class UserControllerApi extends AbstractController
{
    #[Route('/', methods: ['GET', 'POST'], name: 'user_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() === NULL) {
            return new JsonResponse(
                [
                    "user not logged"
                ]
            );
        }

        else {
            $user = $this->getUser();

            return new JsonResponse(
                [
                    "Nombre de usuario" => $user->getUsername(),
                    "Nombre completo" => $user->getFullName(),
                    "Email" => $user->getEmail()
                ], status: Response::HTTP_OK
            );
        }
    }

    #[Route('/show/{username}', methods: ['GET'], name: 'user_show')]
    //    #[ParamConverter('user', options: ['mapping' => ['username' => 'username']])]
        public function show(Request $request, UserRepository $userRepository, PostRepository $postRepository, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
        {
            if ($this->getUser() === NULL) {
                return new JsonResponse(
                    [
                        "to see a user first u have to be logged"
                    ]
                );
            }

            $user = $userRepository->findOneBy(["username" => $request->attributes->get("username")]);
            $posts = $postRepository->findOneBy(["author" => $user]);
            $comments = $commentRepository->findOneBy(["author" => $user]);
            $user->posts = $posts;
            $user->comments = $comments;
            return new JsonResponse(
                [
                    $user
                ], status: Response::HTTP_OK
            );
        }
    #[Route('/edit', methods: ['GET', 'POST'], name: 'user_edit')]
    public function update(Request $request) {
        if ($this->getUser() === NULL) {
            return new JsonResponse(
                [
                    "user not logged"
                ]
            );
        }
        else {
            $user = $this->getUser();
            $user->username = $request->attributes->get("username");
            $user->fullName = $request->attributes->get("fullName");
            $user->email = $request->attributes->get("email");
            return new JsonResponse(
                [
                    "Nombre de usuario" => $user->getUsername(),
                    "Nombre completo" => $user->getFullName(),
                    "Email" => $user->getEmail()
                ], status: Response::HTTP_OK
            );
        }
    }
}
