<?php

namespace App\Controller;

use App\Entity\Url;
use App\Repository\UrlRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UrlController extends AbstractController
{
    const LENGTH = 11;

    public function __construct(
        private EntityManagerInterface $em,
        private UrlRepository $urlRepository
    )
    {
    }

    #[Route("/{key}", name: "index")]
    public function index($key): RedirectResponse|JsonResponse
    {
        $existingUrl = $this->urlRepository->findOneBy(['key' => $key]);
        if ($existingUrl) {
            return $this->redirect($existingUrl->getFullUrl());
        }

        return $this->json([
            "full" => $existingUrl->getFullUrl()
        ]);
    }

    #[Route("/shorten", name: "shorten_url")]
    public function shortenUrl(Request $request): JsonResponse
    {
        $url = $request->query->get('url');
        $existingUrl = $this->urlRepository->findOneBy(['fullUrl' => $url]);
        if ($existingUrl) {
            return $this->json([
                "full" => $existingUrl->getFullUrl(),
                "short" => $request->getBasePath().'/'.$existingUrl->getKey()
            ]);
        }

        $key = substr(md5(uniqid(rand(), true)), 0, self::LENGTH);

        $urlRecord = new Url();
        $urlRecord->setFullUrl(filter_var($url, FILTER_SANITIZE_URL));
        $urlRecord->setKey($key);
        $this->em->persist($urlRecord);
        $this->em->flush();

        return $this->json([
            "full" => $request->query->get('url'),
            "short" => $key
        ]);
    }

    #[Route("/full/{key}", name: "get_full_url")]
    public function getFullUrl($key): JsonResponse
    {
        $existingUrl = $this->urlRepository->findOneBy(['key' => $key]);
        if (!$existingUrl) {
            return $this->json([
                "error" => "No such key"
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json([
            "full" => $existingUrl->getFullUrl()
        ]);
    }
}