<?php

namespace App\Blog\Tag;

use App\Controller;
use App\Blog\Entity\Post;
use App\Blog\Entity\Tag;
use App\Blog\Post\PostRepository;
use Cycle\ORM\ORMInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Yiisoft\Router\UrlGeneratorInterface;

class TagController extends Controller
{
    private const POSTS_PER_PAGE = 10;
    private const POPULAR_TAGS_COUNT = 10;

    protected function getId(): string
    {
        return 'blog/tag';
    }

    public function index(
        Request $request,
        ORMInterface $orm,
        UrlGeneratorInterface $urlGenerator
    ): Response {
        /** @var TagRepository $tagRepo */
        $tagRepo = $orm->getRepository(Tag::class);
        /** @var PostRepository $postRepo */
        $postRepo = $orm->getRepository(Post::class);
        $label = $request->getAttribute('label', null);
        $pageNum = (int)$request->getAttribute('page', 1);

        $item = $tagRepo->findByLabel($label);

        if ($item === null) {
            return $this->responseFactory->createResponse(404);
        }
        // preloading of posts
        $paginator = $postRepo
            ->findByTag($item->getId())
            ->withTokenGenerator(fn ($page) => $urlGenerator->generate(
                'blog/tag',
                ['label' => $label, 'page' => $page]
            ))
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($pageNum);

        $data = [
            'item' => $item,
            'paginator' => $paginator,
        ];
        $output = $this->render(__FUNCTION__, $data);

        $response = $this->responseFactory->createResponse();
        $response->getBody()->write($output);
        return $response;
    }
}
