<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\BookingService;
use App\Service\CottageService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

/** @psalm-suppress UnusedClass */
class CottageApiController extends AbstractController
{
    #[Route('/api/cottages', name: 'api_cottages', methods: ['GET'])]
    #[OA\Get(
        path: '/api/cottages',
        operationId: 'getCottages',
        summary: 'Get list of available cottages',
        tags: ['api/cottages'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of available cottages',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'Cottage'),
                                    new OA\Property(property: 'description', type: 'string', example: 'A cottage by the lake'),
                                    new OA\Property(property: 'pricePerNight', type: 'number', format: 'float', example: 100.00),
                                    new OA\Property(property: 'address', type: 'string', example: '123 Lake Street'),
                                ]
                            )
                        )
                    ),
                ]
            ),
        ]
    )]
    public function getCottages(CottageService $cottageService): JsonResponse
    {
        $cottages = $cottageService->getAvailableCottages();

        return $this->json($cottages);
    }

    #[Route('/api/bookings', name: 'api_bookings_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/bookings',
        operationId: 'createBooking',
        summary: 'Create a new booking',
        tags: ['api/bookings'],
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Booking data',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                        required: ['phone', 'cottageId'],
                        properties: [
                            new OA\Property(property: 'phone', type: 'string', example: '+1234567890'),
                            new OA\Property(property: 'cottageId', type: 'integer', example: 1),
                            new OA\Property(property: 'comment', type: 'string', example: 'Late check-in', nullable: true),
                        ]
                    )
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Booking created successfully',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'status', type: 'string', example: 'Booking created successfully'),
                            ]
                        )
                    ),
                ]
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'error', type: 'string', example: 'Phone and cottageId are required'),
                            ]
                        )
                    ),
                ]
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found'),
                            ]
                        )
                    ),
                ]
            ),
        ]
    )]
    public function createBooking(Request $request, BookingService $bookingService): Response
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['phone']) || empty($data['cottageId'])) {
            return $this->json(
                ['error' => 'Phone and cottageId are required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $bookingService->createBooking(
            (string) $data['phone'],
            (int) $data['cottageId'],
            (string) ($data['comment'] ?? '')
        );

        return $this->json(
            ['status' => 'Booking created successfully'],
            Response::HTTP_CREATED
        );
    }

    #[Route('/api/bookings/edit', methods: ['PUT'])]
    #[OA\Put(
        path: '/api/bookings/edit',
        operationId: 'updateBookingComment',
        summary: 'Update booking comment',
        tags: ['api/bookings'],
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Booking update data',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                        required: ['phone', 'cottageId', 'comment'],
                        properties: [
                            new OA\Property(property: 'phone', type: 'string', example: '+1234567890'),
                            new OA\Property(property: 'cottageId', type: 'integer', example: 1),
                            new OA\Property(property: 'comment', type: 'string', example: 'Updated comment'),
                        ]
                    )
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Comment updated',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'status', type: 'string', example: 'Comment updated'),
                            ]
                        )
                    ),
                ]
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'error', type: 'string', example: 'Phone, cottageId, and comment are required'),
                            ]
                        )
                    ),
                ]
            ),
            new OA\Response(
                response: 404,
                description: 'Booking not found',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'error', type: 'string', example: 'Booking not found'),
                            ]
                        )
                    ),
                ]
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found'),
                            ]
                        )
                    ),
                ]
            ),
        ]
    )]
    public function updateComment(Request $request, BookingService $bookingService): Response
    {
        $phone = $request->request->get('phone', '');
        $cottageId = $request->request->get('cottageId', 0);
        $comment = $request->request->get('comment', '');

        if (is_string($phone) && is_scalar($cottageId) && is_string($comment)) {
            $cottageId = (int)$cottageId;
            if ($bookingService->updateBookingComment($phone, $cottageId, $comment)) {
                return $this->json(['status' => 'Comment updated']);
            }
        }

        return $this->json(['error' => 'Booking not found'], 404);
    }

    #[Route('/api/bookings/delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/bookings/delete',
        operationId: 'deleteBooking',
        summary: 'Delete a booking',
        tags: ['api/bookings'],
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Booking deletion data',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                        required: ['phone', 'cottageId'],
                        properties: [
                            new OA\Property(property: 'phone', type: 'string', example: '+1234567890'),
                            new OA\Property(property: 'cottageId', type: 'integer', example: 1),
                        ]
                    )
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Booking deleted',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'status', type: 'string', example: 'Booking deleted'),
                            ]
                        )
                    ),
                ]
            ),
            new OA\Response(
                response: 400,
                description: 'Invalid input',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'error', type: 'string', example: 'Phone and cottageId are required'),
                            ]
                        )
                    ),
                ]
            ),
            new OA\Response(
                response: 404,
                description: 'Booking not found',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'error', type: 'string', example: 'Booking not found'),
                            ]
                        )
                    ),
                ]
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'message', type: 'string', example: 'JWT Token not found'),
                            ]
                        )
                    ),
                ]
            ),
        ]
    )]
    public function deleteBooking(Request $request, BookingService $bookingService): Response
    {
        $phone = $request->request->get('phone', '');
        $cottageId = $request->request->get('cottageId', 0);

        if (is_string($phone) && is_scalar($cottageId)) {
            $cottageId = (int)$cottageId;
            if ($bookingService->deleteBooking($phone, $cottageId)) {
            return $this->json(['status' => 'Booking deleted']);
            }
        }

        return $this->json(['error' => 'Booking not found'], 404);
    }
}
