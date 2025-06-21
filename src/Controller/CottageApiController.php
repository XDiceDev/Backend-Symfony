<?php
namespace App\Controller;

use App\Service\CottageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\BookingService;

class CottageApiController extends AbstractController
{
    #[Route('/api/cottages', name: 'api_cottages', methods: ['GET'])]
    public function getCottages(CottageService $cottageService): JsonResponse
    {
        $cottages = $cottageService->getAvailableCottages();
        return $this->json($cottages);
    }


    #[Route('/api/bookings', name: 'api_bookings_create', methods: ['POST'])]
    public function createBooking(Request $request, BookingService $bookingService): Response
    {
        $data = json_decode($request->getContent(), true);
        
        if (empty($data['phone']) || empty($data['cottageId']))
        {
            return $this->json(
                ['error' => 'Phone and cottageId are required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $bookingService->createBooking(
            (string)$data['phone'],
            (int)$data['cottageId'],
            (string)($data['comment'] ?? '')
        );

        return $this->json(
            ['status' => 'Booking created successfully'],
            Response::HTTP_CREATED
        );
    }


    #[Route('/api/bookings/edit/', methods: ['PUT'])]
    public function updateComment(Request $request, BookingService $bookingService): Response
    {
        $phone = $request->request->get('phone', '');
        $cottageId = $request->request->get('cottageId', 0);
        $comment = $request->request->get('comment', '');

        if ($bookingService->updateBookingComment($phone, $cottageId, $comment))
        {
            return $this->json(['status' => 'Comment updated']);
        }

        return $this->json(['error' => 'Booking not found'], 404);
    }


    #[Route('/api/bookings/delete/', methods: ['DELETE'])]
    public function deleteBooking(BookingService $bookingService): Response
    {
        $phone = $request->request->get('phone', '');
        $cottageId = $request->request->get('cottageId', 0);

        if ($bookingService->deleteBooking($phone, $cottageId))
        {
            return $this->json(['status' => 'Booking deleted']);
        }

        return $this->json(['error' => 'Booking not found'], 404);
    }
}