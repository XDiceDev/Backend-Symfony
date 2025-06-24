<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Cottage;
use App\Service\BookingService;
use App\Service\CottageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[OA\Tag(name: 'api/cottages')]
class CottageApiController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
                                    new OA\Property(property: 'name', type: 'string', example: 'Cozy Cottage'),
                                    new OA\Property(property: 'description', type: 'string', example: 'A cozy cottage by the lake'),
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

    #[Route('/api/cottages', name: 'api_cottages_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(
        path: '/api/cottages',
        operationId: 'createCottage',
        summary: 'Create a new cottage',
        tags: ['api/cottages'],
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            description: 'Cottage data',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                        required: ['name', 'description', 'pricePerNight', 'address'],
                        properties: [
                            new OA\Property(property: 'name', type: 'string', example: 'Cozy Cottage'),
                            new OA\Property(property: 'description', type: 'string', example: 'A cozy cottage by the lake'),
                            new OA\Property(property: 'pricePerNight', type: 'number', format: 'float', example: 100.00),
                            new OA\Property(property: 'address', type: 'string', example: '123 Lake Street'),
                        ]
                    )
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Cottage created successfully',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Cozy Cottage'),
                                new OA\Property(property: 'description', type: 'string', example: 'A cozy cottage by the lake'),
                                new OA\Property(property: 'pricePerNight', type: 'number', format: 'float', example: 100.00),
                                new OA\Property(property: 'address', type: 'string', example: '123 Lake Street'),
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
                                new OA\Property(property: 'error', type: 'string', example: 'Name, description, pricePerNight, and address are required'),
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
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'message', type: 'string', example: 'Access denied'),
                            ]
                        )
                    ),
                ]
            ),
        ]
    )]
    public function createCottage(Request $request, CottageService $cottageService): Response
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['name']) || empty($data['description']) || empty($data['pricePerNight']) || empty($data['address'])) {
            return $this->json(
                ['error' => 'Name, description, pricePerNight, and address are required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $cottage = $cottageService->createCottage(
                (string) $data['name'],
                (string) $data['description'],
                (float) $data['pricePerNight'],
                (string) $data['address']
            );

            return $this->json($cottage, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to create cottage: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/cottages/{id}', name: 'api_cottages_update', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Put(
        path: '/api/cottages/{id}',
        operationId: 'updateCottage',
        summary: 'Update a cottage',
        tags: ['api/cottages'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            description: 'Cottage update data',
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        type: 'object',
                        properties: [
                            new OA\Property(property: 'name', type: 'string', example: 'Cozy Cottage'),
                            new OA\Property(property: 'description', type: 'string', example: 'A cozy cottage by the lake'),
                            new OA\Property(property: 'pricePerNight', type: 'number', format: 'float', example: 100.00),
                            new OA\Property(property: 'address', type: 'string', example: '123 Lake Street'),
                        ]
                    )
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cottage updated',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Cozy Cottage'),
                                new OA\Property(property: 'description', type: 'string', example: 'A cozy cottage by the lake'),
                                new OA\Property(property: 'pricePerNight', type: 'number', format: 'float', example: 100.00),
                                new OA\Property(property: 'address', type: 'string', example: '123 Lake Street'),
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
                                new OA\Property(property: 'error', type: 'string', example: 'Invalid data provided'),
                            ]
                        )
                    ),
                ]
            ),
            new OA\Response(
                response: 404,
                description: 'Cottage not found',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'error', type: 'string', example: 'Cottage not found'),
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
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'message', type: 'string', example: 'Access denied'),
                            ]
                        )
                    ),
                ]
            ),
        ]
    )]
    public function updateCottage(int $id, Request $request, CottageService $cottageService): Response
    {
        $data = json_decode($request->getContent(), true);

        try {
            $cottage = $cottageService->updateCottage(
                $id,
                isset($data['name']) ? (string) $data['name'] : null,
                isset($data['description']) ? (string) $data['description'] : null,
                isset($data['pricePerNight']) ? (float) $data['pricePerNight'] : null,
                isset($data['address']) ? (string) $data['address'] : null
            );

            if (!$cottage) {
                return $this->json(['error' => 'Cottage not found'], Response::HTTP_NOT_FOUND);
            }

            return $this->json($cottage);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to update cottage: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/cottages/{id}', name: 'api_cottages_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Delete(
        path: '/api/cottages/{id}',
        operationId: 'deleteCottage',
        summary: 'Delete a cottage',
        tags: ['api/cottages'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cottage deleted',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'status', type: 'string', example: 'Cottage deleted'),
                            ]
                        )
                    ),
                ]
            ),
            new OA\Response(
                response: 404,
                description: 'Cottage not found',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'error', type: 'string', example: 'Cottage not found'),
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
            new OA\Response(
                response: 403,
                description: 'Forbidden',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'message', type: 'string', example: 'Access denied'),
                            ]
                        )
                    ),
                ]
            ),
        ]
    )]
    public function deleteCottage(int $id, CottageService $cottageService): Response
    {
        try {
            if ($cottageService->deleteCottage($id)) {
                return $this->json(['status' => 'Cottage deleted']);
            }
            return $this->json(['error' => 'Cottage not found'], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to delete cottage: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/bookings', name: 'api_bookings', methods: ['GET'])]
    #[OA\Get(
        path: '/api/bookings',
        operationId: 'getBookings',
        summary: 'Get list of bookings',
        tags: ['api/bookings'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'phone',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', example: '+1234567890')
            ),
            new OA\Parameter(
                name: 'cottageId',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of bookings',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'phone', type: 'string', example: '+1234567890'),
                                    new OA\Property(property: 'cottageId', type: 'integer', example: 1),
                                    new OA\Property(property: 'comment', type: 'string', example: 'Late check-in'),
                                ]
                            )
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
    public function getBookings(Request $request, BookingService $bookingService): JsonResponse
    {
        $phone = $request->query->get('phone');
        $cottageId = $request->query->getInt('cottageId');

        $bookings = $bookingService->getBookings(
            is_string($phone) ? $phone : null,
            $cottageId > 0 ? $cottageId : null
        );

        return $this->json($bookings);
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
                response: 404,
                description: 'Cottage not found',
                content: [
                    new OA\MediaType(
                        mediaType: 'application/json',
                        schema: new OA\Schema(
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'error', type: 'string', example: 'Cottage not found'),
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

        try {
            $cottage = $this->entityManager->getRepository(Cottage::class)->find((int) $data['cottageId']);
            if (!$cottage) {
                return $this->json(['error' => 'Cottage not found'], Response::HTTP_NOT_FOUND);
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
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to create booking: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/bookings/edit', name: 'api_bookings_update', methods: ['PUT'])]
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
        $data = json_decode($request->getContent(), true);

        if (empty($data['phone']) || empty($data['cottageId']) || !isset($data['comment'])) {
            return $this->json(
                ['error' => 'Phone, cottageId, and comment are required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $cottage = $this->entityManager->getRepository(Cottage::class)->find((int) $data['cottageId']);
            if (!$cottage) {
                return $this->json(['error' => 'Cottage not found'], Response::HTTP_NOT_FOUND);
            }
            if ($bookingService->updateBookingComment(
                (string) $data['phone'],
                (int) $data['cottageId'],
                (string) $data['comment']
            )) {
                return $this->json(['status' => 'Comment updated']);
            }
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to update booking: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['error' => 'Booking not found'], Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/bookings/delete', name: 'api_bookings_delete', methods: ['DELETE'])]
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
        $data = json_decode($request->getContent(), true);

        if (empty($data['phone']) || empty($data['cottageId'])) {
            return $this->json(
                ['error' => 'Phone and cottageId are required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $cottage = $this->entityManager->getRepository(Cottage::class)->find((int) $data['cottageId']);
            if (!$cottage) {
                return $this->json(['error' => 'Cottage not found'], Response::HTTP_NOT_FOUND);
            }
            if ($bookingService->deleteBooking(
                (string) $data['phone'],
                (int) $data['cottageId']
            )) {
                return $this->json(['status' => 'Booking deleted']);
            }
        } catch (\Exception $e) {
            return $this->json(['error' => 'Failed to delete booking: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['error' => 'Booking not found'], Response::HTTP_NOT_FOUND);
    }
}