<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BreedController extends Controller
{
    public function getAllBreeds(Request $request)
    {
        $CAT_API_KEY = env("CAT_API_KEY", "null");
        $cat_api_url_breeds = "https://api.thecatapi.com/v1/breeds";

        $response = Http::withHeaders([
            'x-api-key' => $CAT_API_KEY,
        ])->get($cat_api_url_breeds);

        if ($response->successful()) {
            $data = $response->json();

            $breeds = array_map(function($entry) {
                return [
                    'id' => $entry['id'],
                    'name' => $entry['name'],
                    'image_info' => $entry['image'] ?? null,
                ];
            }, $data);


            return response()->json([
                'msg' => "All breeds getted succesfully",
                'breeds' => $breeds,
            ]);
        } else {
            return response()->json(['msg' => "Error: Breeds can't be getted, the CatApi service is maybe under maintenance or is off, try later"], 400);
        }
    }
}
