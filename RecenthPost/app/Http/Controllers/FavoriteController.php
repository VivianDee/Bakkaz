<?php

namespace App\Http\Controllers;

use App\Service\PostService;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function favorite(Request $request)
    {
        return PostService::addPostToFav($request);
    }
    public function getFavorite(Request $request)
    {
        return PostService::getFavoriteUsers($request);
    }

    public function getMutualFavorites(Request $request)
    {
        return PostService::getMutualFavorites($request);
    }

    public function UpdateFavoritesPostNotification(Request $request)
    {
        return PostService::UpdateFavoritesPostNotification($request);
    }
}
