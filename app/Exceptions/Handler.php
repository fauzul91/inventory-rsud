<?php

use App\Helpers\ResponseHelper;

class Handler
{
    public function render($request, Throwable $e)
    {
        return ResponseHelper::jsonResponse(
            false,
            $e->getMessage(),
            null,
            500
        );
    }
}