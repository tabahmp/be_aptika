<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'APTIKA Tools API',
    version: '1.0.0',
    description: 'Dokumentasi API APTIKA Tools'
)]

#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum',
    description: 'Masukkan token Sanctum dengan format: Bearer {token}'
)]

#[OA\Post(
    path: '/api/login',
    summary: 'Login pengguna',
    description: 'Autentikasi pengguna menggunakan email dan password.',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    format: 'email',
                    example: 'admin@aptika.com'
                ),
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    format: 'password',
                    example: 'password'
                )
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'Login berhasil'),
        new OA\Response(response: 401, description: 'Email atau password salah'),
        new OA\Response(response: 422, description: 'Validasi gagal')
    ]
)]

#[OA\Get(
    path: '/api/admin/users',
    summary: 'Daftar pengguna',
    description: 'Mengambil seluruh data pengguna. Hanya dapat diakses oleh pengguna aktif dengan role admin.',
    security: [
        ['sanctum' => []]
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Daftar pengguna berhasil diambil',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Admin APTIKA'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@aptika.com'),
                        new OA\Property(property: 'position', type: 'string', nullable: true, example: 'Administrator'),
                        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '081234567890'),
                        new OA\Property(property: 'avatar', type: 'string', nullable: true, example: 'avatars/admin.jpg'),
                        new OA\Property(property: 'role', type: 'string', example: 'admin'),
                        new OA\Property(property: 'is_active', type: 'integer', example: 1),
                        new OA\Property(property: 'avatar_url', type: 'string', nullable: true, example: 'http://127.0.0.1:8000/storage/avatars/admin.jpg'),
                        new OA\Property(property: 'jabatan', type: 'string', nullable: true, example: 'Administrator'),
                        new OA\Property(property: 'no_telp', type: 'string', nullable: true, example: '081234567890')
                    ]
                )
            )
        ),
        new OA\Response(response: 401, description: 'Tidak terautentikasi'),
        new OA\Response(response: 403, description: 'Akses ditolak. Hanya admin yang diperbolehkan.')
    ]
)]

#[OA\Post(
    path: '/api/admin/users',
    summary: 'Tambah pengguna',
    description: 'Membuat pengguna baru. Hanya dapat diakses oleh pengguna aktif dengan role admin.',
    security: [
        ['sanctum' => []]
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email', 'password', 'role', 'is_active'],
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'User Baru'),
                new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'userbaru@aptika.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'password123'),
                new OA\Property(property: 'role', type: 'string', enum: ['admin', 'user'], example: 'user'),
                new OA\Property(property: 'is_active', type: 'integer', enum: [0, 1], example: 1),
                new OA\Property(property: 'position', type: 'string', nullable: true, maxLength: 255, example: 'Staff'),
                new OA\Property(property: 'phone', type: 'string', nullable: true, maxLength: 50, example: '081234567890'),
                new OA\Property(property: 'jabatan', type: 'string', nullable: true, maxLength: 255, example: 'Staff Administrasi'),
                new OA\Property(property: 'no_telp', type: 'string', nullable: true, maxLength: 50, example: '081234567890')
            ]
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'User berhasil dibuat'),
        new OA\Response(response: 401, description: 'Tidak terautentikasi'),
        new OA\Response(response: 403, description: 'Akses ditolak. Hanya admin yang diperbolehkan.'),
        new OA\Response(response: 422, description: 'Validasi gagal')
    ]
)]

#[OA\Get(
    path: '/api/admin/users/{user}',
    summary: 'Detail pengguna',
    description: 'Mengambil detail pengguna berdasarkan ID. Hanya dapat diakses oleh pengguna aktif dengan role admin.',
    security: [
        ['sanctum' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'user',
            description: 'ID pengguna',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Detail pengguna berhasil diambil',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Admin APTIKA'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@aptika.com'),
                    new OA\Property(property: 'position', type: 'string', nullable: true, example: 'Administrator'),
                    new OA\Property(property: 'phone', type: 'string', nullable: true, example: '081234567890'),
                    new OA\Property(property: 'avatar', type: 'string', nullable: true, example: 'avatars/admin.jpg'),
                    new OA\Property(property: 'role', type: 'string', example: 'admin'),
                    new OA\Property(property: 'is_active', type: 'integer', example: 1),
                    new OA\Property(property: 'avatar_url', type: 'string', nullable: true, example: 'http://127.0.0.1:8000/storage/avatars/admin.jpg'),
                    new OA\Property(property: 'jabatan', type: 'string', nullable: true, example: 'Administrator'),
                    new OA\Property(property: 'no_telp', type: 'string', nullable: true, example: '081234567890')
                ]
            )
        ),
        new OA\Response(response: 401, description: 'Tidak terautentikasi'),
        new OA\Response(response: 403, description: 'Akses ditolak. Hanya admin yang diperbolehkan.'),
        new OA\Response(response: 404, description: 'Pengguna tidak ditemukan')
    ]
)]

#[OA\Put(
    path: '/api/admin/users/{user}',
    summary: 'Perbarui pengguna',
    description: 'Memperbarui data pengguna berdasarkan ID. Hanya dapat diakses oleh pengguna aktif dengan role admin.',
    security: [
        ['sanctum' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'user',
            description: 'ID pengguna yang akan diperbarui',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 1
        )
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email', 'role', 'is_active'],
            properties: [
                new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Admin APTIKA Updated'),
                new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'admin.updated@aptika.com'),
                new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, nullable: true, example: 'password123'),
                new OA\Property(property: 'role', type: 'string', enum: ['admin', 'user'], example: 'admin'),
                new OA\Property(property: 'is_active', type: 'integer', enum: [0, 1], example: 1),
                new OA\Property(property: 'position', type: 'string', nullable: true, maxLength: 255, example: 'Administrator'),
                new OA\Property(property: 'phone', type: 'string', nullable: true, maxLength: 50, example: '081234567890'),
                new OA\Property(property: 'jabatan', type: 'string', nullable: true, maxLength: 255, example: 'Administrator'),
                new OA\Property(property: 'no_telp', type: 'string', nullable: true, maxLength: 50, example: '081234567890')
            ]
        )
    ),
    responses: [
        new OA\Response(response: 200, description: 'User berhasil diperbarui'),
        new OA\Response(response: 401, description: 'Tidak terautentikasi'),
        new OA\Response(response: 403, description: 'Akses ditolak. Hanya admin yang diperbolehkan.'),
        new OA\Response(response: 404, description: 'Pengguna tidak ditemukan'),
        new OA\Response(response: 422, description: 'Validasi gagal')
    ]
)]

#[OA\Delete(
    path: '/api/admin/users/{user}',
    summary: 'Hapus pengguna',
    description: 'Menghapus pengguna berdasarkan ID. Admin tidak dapat menghapus akun miliknya sendiri.',
    security: [
        ['sanctum' => []]
    ],
    parameters: [
        new OA\Parameter(
            name: 'user',
            description: 'ID pengguna yang akan dihapus',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'integer'),
            example: 2
        )
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'User berhasil dihapus',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'User deleted successfully'
                    )
                ]
            )
        ),
        new OA\Response(
            response: 400,
            description: 'Admin tidak dapat menghapus akun sendiri',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Anda tidak dapat menghapus akun Anda sendiri.'
                    )
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Tidak terautentikasi'
        ),
        new OA\Response(
            response: 403,
            description: 'Akses ditolak. Hanya admin yang diperbolehkan.'
        ),
        new OA\Response(
            response: 404,
            description: 'Pengguna tidak ditemukan'
        )
    ]
)]

class OpenApiSpec
{
}