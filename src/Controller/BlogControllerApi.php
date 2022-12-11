<?php

namespace App\Controller;


use App\Entity\Comment;
use App\Entity\Post;
use App\Event\CommentCreatedEvent;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use App\Repository\TagRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PhpParser\JsonDecoder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
            ->setParameter('now', new \DateTimeImmutable(), Types::DATETIME_IMMUTABLE)
        ;

        return new JsonResponse(
            [
                $latestPost->getQuery()->getArrayResult()
            ], status: Response::HTTP_OK
        );
    }

    #[Route('/ask', defaults: ['page' => '1', '_format' => 'html'], methods: ['GET'], name: 'blog_ask')]
    public function ask(Request $request, int $page, string $_format, PostRepository $posts, TagRepository $tags): Response
    {
        $tag = null;
        $key = null;
        $term = "*";
        $latestPosts = $posts->createQueryBuilder('p')
            ->addSelect('a', 't')
            ->innerJoin('p.author', 'a')
            ->leftJoin('p.tags', 't')
            ->where('p.link LIKE :t_'.$key)
            ->setParameter('t_'.$key, '%'.$term.'%')
            ->orderBy('p.publishedAt', 'DESC')
        ;
        return new JsonResponse(
            [
                $latestPosts->getQuery()->getArrayResult()
            ], status: Response::HTTP_OK
        );
    }

    #[Route('/comment/{id}', methods: ['POST'], name: 'reply_new')]
    public function replyNew(Request $request, Comment $parentComment, EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $comment->setAuthor($this->getUser());
        $comment->setPost($parentComment->getPost());



        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        $parentComment->addReply($comment);

        $entityManager->persist($comment);
        $entityManager->flush();
        if ($form->isSubmitted() && $form->isValid()) {
//            $entityManager->persist($comment);
//            $entityManager->flush();

            // When an event is dispatched, Symfony notifies it to all the listeners
            // and subscribers registered to it. Listeners can modify the information
            // passed in the event and they can even modify the execution flow, so
            // there's no guarantee that the rest of this controller will be executed.
            // See https://symfony.com/doc/current/components/event_dispatcher.html
            try{
                $eventDispatcher->dispatch(new CommentCreatedEvent($comment));

            }
            catch(Exception $e){

            }

            return new JsonResponse(
                [
                    $comment->getId(),
                    $comment->getAuthor(),
                    $comment->getContent()
                ], status: Response::HTTP_OK
            );
        }

        return $this->render('blog/comment_form_error.html.twig', [
            'post' => $parentComment->getPost(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/search', methods: ['GET'], name: 'blog_search')]
    public function search(Request $request, PostRepository $posts): Response
    {
        $query = $request->query->get('q', '');

        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(
                [
                    $query
                ], status: Response::HTTP_OK
            );
        }

        $foundPosts = $posts->findBySearchQuery($query);

        $results = [];
        foreach ($foundPosts as $post) {
            $results[] = [
                'title' => htmlspecialchars($post->getTitle(), ENT_COMPAT | ENT_HTML5),
                'date' => $post->getPublishedAt()->format('M d, Y'),
                'author' => htmlspecialchars($post->getAuthor()->getFullName(), ENT_COMPAT | ENT_HTML5),
                'summary' => htmlspecialchars($post->getSummary(), ENT_COMPAT | ENT_HTML5),
                'url' => $this->generateUrl('blog_post', ['slug' => $post->getSlug()]),
            ];
        }

        return new JsonResponse(
            [
                $results
            ], status: Response::HTTP_OK
        );
    }

    #[Route('/newest', defaults: ['page' => '1', '_format' => 'html'], methods: ['GET'], name: 'blog_newest_index')]
    public function newestPosts(Request $request, int $page, string $_format, PostRepository $posts, TagRepository $tags): Response
    {
            $tag = null;
            $key = null;
            $newestsPosts = $posts->createQueryBuilder('p')
                ->addSelect('a', 't')
                ->innerJoin('p.author', 'a')
                ->leftJoin('p.tags', 't')
                ->where('p.link LIKE :t_'.$key)
                ->setParameter('t_'.$key, '%'.$term.'%')
                ->orderBy('p.publishedAt', 'DESC')
            ;
            return new JsonResponse(
                [
                    $newestwPosts->getQuery()->getArrayResult()
                ], status: Response::HTTP_OK
            );
    }

    #[Route('/post/new', methods: ['GET', 'POST'], name: 'post_new')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository): Response
    {
            $post = new Post();
            $post->setAuthor($this->getUser());

            // See https://symfony.com/doc/current/form/multiple_buttons.html
            $form = $this->createForm(PostType::class, $post)
                ->add('saveAndCreateNew', SubmitType::class);
            $form->handleRequest($request);

            if($post->getLink() != '*'){
                $post->setType("url");

                $exsistingPost = $postRepository->findOneBy(['link' => $post->getLink()]);

                if($exsistingPost){
                    //dd($exsistingPost);
                    //return $this->redirectToRoute('blog_post');
                    return new JsonResponse(
                        [
                            $post->getId(),
                            $post->getAuthor(),
                            $post->getPublishedAt(),
                            $post->getTitle(),
                            $post->getLink(),
                            $post->getContent(),
                        ], status: Response::HTTP_OK
                    );
                }

            }
            else{
                $post->setType("ask");
            }

            if ($form->isSubmitted() && $form->isValid()) {

                $entityManager->persist($post);
                $entityManager->flush();

                $this->addFlash('success', 'post.created_successfully');

                if ($form->get('saveAndCreateNew')->isClicked()) {
                    return new JsonResponse(
                        [
                            $post->getId(),
                            $post->getAuthor(),
                            $post->getPublishedAt(),
                            $post->getTitle(),
                            $post->getLink(),
                            $post->getContent(),
                            $post->getComments()
                        ], status: Response::HTTP_OK
                    );
                }
                return $this->redirectToRoute('blog_index');
            }
            return $this->render('admin/blog/new.html.twig', [
                'post' => $post,
                'form' => $form->createView(),
            ]);
    }

    #[Route('/posts/{slug}/vote', methods: ['GET'], name: 'vote_post')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function postVote(Post $post,  EntityManagerInterface $entityManager): Response
    {
        $post->addVote($this->getUser());
        //$post->addUserIdVotes($this->getUser()->getId());
        $entityManager->persist($post);
        $entityManager->flush();

        //dd($newPost);

        return new JsonResponse(
            [
                $post->getId(),
                $post->getAuthor(),
                $post->getPublishedAt(),
                $post->getTitle(),
                $post->getLink(),
                $post->getContent(),
                $post->getComments(),
                $post->getNumberOfVotes()
            ], status: Response::HTTP_OK
        );
    }

    #[Route('/{id<\d+>}', methods: ['GET'], name: 'admin_post_show')]
        public function show(Post $post): Response
        {
            // This security check can also be performed
            // using a PHP attribute: #[IsGranted('show', subject: 'post', message: 'Posts can only be shown to their authors.')]
            $this->denyAccessUnlessGranted(PostVoter::SHOW, $post, 'Posts can only be shown to their authors.');

            return new JsonResponse(
                [
                    $post
                ], status: Response::HTTP_OK
            );
        }
    }
}