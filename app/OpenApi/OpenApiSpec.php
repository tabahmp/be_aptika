<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'APTIKA Tools API',
    version: '1.0.0',
    description: 'Dokumentasi API APTIKA Tools'
)]
#[OA\PathItem(
    path: '/api/login'
)]
class OpenApiSpec
{
}
