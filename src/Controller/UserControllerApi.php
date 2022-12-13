<?php

namespace App\Controller;

use App\Form\UserType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
        if ($user != null) {
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);
            $entityManager->flush();

            $this->addFlash('success', 'user.updated_successfully');
            return new JsonResponse(
                [
                    "username" => $user->getUsername(),
                    "Nombre completo" => $user->getFullName(),
                    "email" => $user->getEmail()
                ], status: Response::HTTP_OK
            );}
        else return new JsonResponse([
            "error, 404, User does not exist"
        ], status: Response::HTTP_NOT_FOUND);
    }

    #[Route('/show/{username}', methods: ['GET'], name: 'user_show')]
    //    #[ParamConverter('user', options: ['mapping' => ['username' => 'username']])]
    public function show(Request $request, UserRepository $userRepository, PostRepository $postRepository, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
    {

        $user = $userRepository->findOneBy(["username" => $request->attributes->get("username")]);
        $posts = $postRepository->findOneBy(["author" => $user]);
        $comments = new ArrayCollection($commentRepository->findBy(['author' => $user, 'parentComment' => null]));
        $user->posts = $posts;
        $user->comments = $comments;
        $arrayC = [];
        for ($i = 0; $i < $comments->count(); ++$i) {
            array_push($arrayC, $comments->get($i)->getContent());
        }
        return new JsonResponse(
            [
                "username" => $user->getUsername(),
                "Nombre completo" => $user->getFullName(),
                "email" => $user->getEmail(),
                "comments" => $arrayC
            ], status: Response::HTTP_OK
        );
    }
}