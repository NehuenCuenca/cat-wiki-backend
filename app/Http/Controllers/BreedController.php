<?php

namespace App\Http\Controllers;

use App\Models\Breed;
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

        $parsedResponse = $response->json();

        if ($response->failed()) {
            return response()->json(['msg' => $parsedResponse['message']], $response->status());
        }

        if ($response->successful()) {

            $breeds = array_map(function ($entry) {
                return [
                    'id' => $entry['id'],
                    'name' => $entry['name'],
                    'image_info' => $entry['image'] ?? null,
                ];
            }, $parsedResponse);


            return response()->json([
                'msg' => "All breeds getted succesfully",
                'breeds' => $breeds,
            ]);
        }
    }


    public function getBreed($breed_id, Request $request)
    {
        $CAT_API_KEY = env("CAT_API_KEY", "null");
        $cat_api_url_breed  = "https://api.thecatapi.com/v1/breeds/$breed_id";
        $cat_api_url_images = "https://api.thecatapi.com/v1/images/search?breed_ids=$breed_id&limit=11";

        $breedResponse = Http::withHeaders([
            'x-api-key' => $CAT_API_KEY,
        ])->get($cat_api_url_breed);
        $parsedBreedResponse = $breedResponse->json();

        if ($breedResponse->failed()) {
            return response()->json(['msg' => $parsedBreedResponse['message']], $breedResponse->status());
        }

        if ($breedResponse->successful() && !empty($parsedBreedResponse)) {
            // dd($breed);

            $imagesBreedResponse = Http::withHeaders([
                'x-api-key' => $CAT_API_KEY,
            ])->get($cat_api_url_images);
            $parsedImagesBreedResponse = $imagesBreedResponse->json();

            $urlsImages = array_map(function ($entry) {
                return $entry['url'];
            }, $parsedImagesBreedResponse);

            return response()->json([
                'msg' => "Breed '$breed_id' getted succesfully",
                'breed' => $parsedBreedResponse,
                'images' => $urlsImages ?? []
            ]);
        } else {
            return response()->json(['msg' => "Error: It seems that the breed '$breed_id' doesn't exist in the CatApi"], 400);
        }
    }


    public function getMostPopularBreeds(Request $request)
    {
        $mostPopularBreeds = Breed::orderBy("visits", "DESC")->take(10)->get()->toArray();

        $CAT_API_KEY = env("CAT_API_KEY", "null");
        $cat_api_url_breeds = "https://api.thecatapi.com/v1/breeds";

        $breedsResponse = Http::withHeaders([
            'x-api-key' => $CAT_API_KEY,
        ])->get($cat_api_url_breeds);
        $parsedBreedsResponse = $breedsResponse->json();

        if ($breedsResponse->failed()) {
            return response()->json(['msg' => $parsedBreedsResponse['message']], $breedsResponse->status());
        }

        // dd( $mostPopularBreeds, $parsedBreedsResponse);
        $popularBreeds = array_map(function ($entry) use ($parsedBreedsResponse) {
            $isPopular = null;

            for ($i = 0; $i < count($parsedBreedsResponse); $i++) {
                $actualParsedBreed = $parsedBreedsResponse[$i];
                if ($actualParsedBreed['name'] === $entry['name']) {
                    $isPopular = $actualParsedBreed;
                    break;
                }
            }

            return $isPopular ?? false;
        }, $mostPopularBreeds);

        return response()->json([
            'msg' => "Most popular breeds getted succesfully",
            'breeds' => $popularBreeds ?? [],
        ]);
    }

    public function getRandomBreeds($quantity, Request $request)
    {
        $parsedQuantity = intval($quantity);
        if ($parsedQuantity <= 0) {
            return response()->json([
                'msg' => "Error: Can't get that quantity ($quantity) of breeds",
            ], 400);
        }

        $CAT_API_KEY = env("CAT_API_KEY", "null");
        $cat_api_url_breeds = "https://api.thecatapi.com/v1/breeds";

        $breedsResponse = Http::withHeaders([
            'x-api-key' => $CAT_API_KEY,
        ])->get($cat_api_url_breeds);
        $parsedBreedsResponse = $breedsResponse->json();

        if ($breedsResponse->failed()) {
            return response()->json(['msg' => $parsedBreedsResponse['message']], $breedsResponse->status());
        }

        function generateRandomIndexes($quantity, $limit)
        {
            $randomIndexes = array();

            while (count($randomIndexes) < $quantity) {
                $randomIndex = rand(0, $limit - 1);
                if (!in_array($randomIndex, $randomIndexes)) {
                    $randomIndexes[] = $randomIndex;
                }
            }

            return $randomIndexes;
        }


        $totalBreeds = count($parsedBreedsResponse);
        if ($parsedQuantity >= $totalBreeds) {
            $parsedQuantity = $totalBreeds;
        } //Limit the quantity requested

        $breedsIndexes = generateRandomIndexes($parsedQuantity, $totalBreeds);
        $randomBreeds = array_map(function ($index) use ($parsedBreedsResponse) {
            return $parsedBreedsResponse[$index];
        }, $breedsIndexes);

        return response()->json([
            'msg' => "Random breeds getted succesfully",
            'breeds' => $randomBreeds ?? [],
        ]);
    }

    public function updatePopularity($breed_id, Request $request)
    {
        $alreadyExist = Breed::firstWhere('short_name', $breed_id);

        if (!$alreadyExist) {
            $CAT_API_KEY = env("CAT_API_KEY", "null");
            $cat_api_url_breed  = "https://api.thecatapi.com/v1/breeds/$breed_id";

            $breedResponse = Http::withHeaders([
                'x-api-key' => $CAT_API_KEY,
            ])->get($cat_api_url_breed);
            $parsedBreedResponse = $breedResponse->json();

            if ($breedResponse->failed()) {
                return response()->json(['msg' => $parsedBreedResponse['message']], $breedResponse->status());
            }
            if (empty($parsedBreedResponse)) {
                return response()->json(['msg' => "Error: that breed doesnt exist in the CatApi"], $breedResponse->status());
            }

            $newBreed = Breed::create([
                'name' => $parsedBreedResponse['name'],
                'short_name' => $parsedBreedResponse['id'],
                'visits' => 1,
            ]);

            return response()->json([
                'msg' => "Breed popularity succesfully update",
                "breed" => $newBreed
            ], 200);
        }

        $alreadyExist->visits = $alreadyExist->visits + 1;
        $alreadyExist->save();

        return response()->json([
            'msg' => "Breed popularity succesfully update",
            "breed" => $alreadyExist
        ], 200);
    }
}
