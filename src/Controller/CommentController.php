<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/')]
class CommentController extends AbstractController
{
    /**
     * NOTE: The ParamConverter mapping is required because the route parameter
     * (postSlug) doesn't match any of the Doctrine entity properties (slug).
     *
     * See https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html#doctrine-converter
     * @param Request $request
     * @param Comment $comment
     * @param Post $post
     * @param EventDispatcherInterface $eventDispatcher
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/threads', methods: ['GET'], name: 'comment_index')]
    public function index(CommentRepository $comments): Response
    {

        $test = $comments->findByPost($this->getUser());
        dd($test);

        return $this->redirectTo('blog_index');
    }
}