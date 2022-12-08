<?php

namespace App\Controller\Api;


use App\Entity\Post;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;

#[Route('/api')]
class BlogControllerApi extends AbstractController
{
    #[Route('/', defaults: ['page' => '1'], methods: ['GET'], name: 'blog_index')]
    public function index(Request $request, int $page, PostRepository $posts) {
        $tag = null;
        $latestPost = $posts->createQueryBuilder('p')
            ->addSelect('a', 't')
            ->innerJoin('p.author', 'a')
            ->leftJoin('p.tags', 't')
            ->where('p.publishedAt <= :now')
            ->orderBy('p.numberOfVotes', 'DESC')
            ->setParameter('now', new DateTime())
        ;

        $array = (array) $latestPost;

        return new JsonResponse(
            [
                $array
            ], status: Response::HTTP_OK
        );
    }

    #[Route('/posts/{slug}', methods: ['GET'], name: 'blog_post')]
    public function postShow(Post $post, CommentRepository $commentRepository): Response
    {

        $comments = new ArrayCollection($commentRepository->findBy(['post' => $post, 'parentComment' => null]));

        $post->setComments($comments);
        //return $this->render('blog/post_show.html.twig', ['post' => $post]);
        $commentsJson = (array) $post->getComments();
        return new JsonResponse(
        [
            "Title" => $post->getTitle(),
            "Content" => $post->getContent(),
            "Comments" => $commentsJson
        ], status: Response::HTTP_OK
    );
    }
}