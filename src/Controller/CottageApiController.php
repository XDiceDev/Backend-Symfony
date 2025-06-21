<?php
namespace App\Controller;

use App\Service\CottageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CottageApiController extends AbstractController
{
    #[Route('/api/cottages', name: 'api_cottages', methods: ['GET'])]
    public function getCottages(CottageService $cottageService): JsonResponse
    {
        $cottages = $cottageService->getAvailableCottages();
        return $this->json($cottages);
    }
}