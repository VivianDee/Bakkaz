<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelpers;
use App\Service\AdministrativeService;
use Illuminate\Http\Request;

class AdministrativeController extends Controller
{
    /**
     * Handle the deletion of user interactions.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteInteractions(Request $request)
    {
        // Call the service to handle soft deletion
        AdministrativeService::deleteInteractions($request);

        // Return a success response
        return ResponseHelpers::success(message:'User interactions marked as deleted successfully.' ) ;
    }
}
