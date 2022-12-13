<?php

namespace App\Controller;

use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/profile')]
class UserControllerApi extends AbstractController
{
    #[Route('/edit', methods: ['GET', 'POST'], name: 'user_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        return new JsonResponse(
            [
                "Nombre de usuario" => $user->getUsername(),
                "Nombre completo" => $user->getFullName(),
                "Email" => $user->getEmail()
            ], status: Response::HTTP_OK
        );
    }

    #[Route('/show/{username}', methods: ['GET'], name: 'user_show')]
    //    #[ParamConverter('user', options: ['mapping' => ['username' => 'username']])]
        public function show(Request $request, UserRepository $userRepository, PostRepository $postRepository, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
        {

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
}
