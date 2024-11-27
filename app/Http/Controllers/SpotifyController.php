<?php

namespace App\Http\Controllers;

use App\Exports\DatasetExport;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class SpotifyController extends Controller
{
    public function generateToken()
    {
        $body = [
            "grant_type" => "client_credentials",
            // "client_id" => "b7ad52b22b904c6aa41986b0563d52c9",
            "client_id" => "f07fcbbea16c413085e2e69670d6faba",
            "client_secret" => "a756da89960f4550a3450a9b28a1bae9",
            // "client_secret" => "00f5e6c670d845f59e2b0bace12f8751"
        ];

        $url = "https://accounts.spotify.com/api/token";

        $resp = Http::asForm()->post($url, $body);

        $result = $resp['access_token'];

        return response()->json(['token' => $result]);

    }
    public function index(Request $request)
    {
        set_time_limit(0);
        /// set auth
        $token = $request->bearerToken();

        $headers = [
            "Authorization" => "Bearer $token",
        ];

        $artistList = [
            'NCT',
            'NCT Dream',
            'NCT 127',
            'NCT Wish',
            'WayV',
            'TVXQ',
            'BoA',
            'Kangta',
            'Super Junior',
            'SHINee',
            'F(X)',
            'EXO',
            'Red Velvet',
            'Girls Generation',
            'Aespa',
            'RIIZE',
            'CBX',
            'K.R.Y',
            'D&E',
            'TTS',
            'Super Junior M',
            'EXO SC',
            'NCT DoJaeJung',
            '2PM',
            'Miss A',
            'TWICE',
            'GOT7',
            'ITZY',
            'STRAY KIDS',
            'NMIXX',
            'Xdinary Heroes',
            'Big Bang',
            '2ne1',
            'Winner',
            'iKON',
            'Black Pink',
            'Treasure',
            'Baby Monster',
            'Infinite',
            'B.A.P',
            'Boyfriend',
            'Teen Top',
            'Mblaq',
            'B1A4',
            'Apink',
            'Gidle',
            'Ive',
            'Le Sserafim',
            'New Jeans',
            'Illit',
            'BTS',
            'Seventeen',
            'Enhypen',
            'TXT',
            'IOI',
            'Wanna One',
            'Sistar',
            'AOA',
            'Kep1er',
            'ZB1',
            'Boy Next Door',
            'Ateez',
            'The Boyz',
            'ASTRO',
            'Stay C',
            'Oh My Girl',
            'Kiss of life',
            'Fromis9',
        ];

        $dataset = [];
        $artistData = [];

        foreach ($artistList as $artist) {
            $url = "https://api.spotify.com/v1/search?q=$artist&type=track&market=ID&limit=50&offset=0";
            $resp = Http::withHeaders($headers)->get($url);
            if ($resp->status() == 401) {
                $token = json_decode($this->generateToken()->getContent(), true)['token'];
                // dd($token['token']);
                $headers = [
                    "Authorization" => "Bearer $token",
                ];
                $url = "https://api.spotify.com/v1/search?q=$artist=nct&type=track&market=ID&limit=50&offset=0";
                $resp = Http::withHeaders($headers)->get($url);
            }
            $result = $resp->json()['tracks']['items'];

            $data = [
                'track_id',
                'track_name',
                'popularity',
                'artists',
                'genre',
                'danceability',
                'energy',
                'key',
                'loudness',
                'mode',
                'speechiness',
                'acousticness',
                'instrumentalness',
                'liveness',
                'valence',
                'tempo',
                'album_id',
                'album_name',
                'album_release_date',
                'duration_ms',
            ];

            foreach ($result as $key => $value) {
                $names = [];
                $artistData = [];

                foreach ($value['artists'] as $key => $artist) {
                    $names[] = $artist['name'] ?: null;

                    $isInArray = in_array($artist['id'], $artistData);
                    if (!$isInArray) {

                        // } else {

                        $artistData[$artist['id']] = [];
                        $url = "https://api.spotify.com/v1/artists/{$artist['id']}";
                        $responseArtist = Http::withHeaders(headers: $headers)->get($url);

                        if ($responseArtist->json()['genres']) {
                            $artistData[$artist['id']]['artistGenre'] = $responseArtist->json()['genres'][0];
                        } else {
                            $artistData[$artist['id']]['artistGenre'] = null;
                        }

                        $url = "https://api.spotify.com/v1/audio-features/{$value['id']}";
                        $responseSong = Http::withHeaders(headers: $headers)->get($url);
                        // return response()->json($responseSong->getHeaders());
                        $artistData[$artist['id']]['danceability'] = $responseSong['danceability'] ?: null;
                        $artistData[$artist['id']]['energy'] = $responseSong['energy'] ?: null;
                        $artistData[$artist['id']]['key'] = $responseSong['key'] ?: null;
                        $artistData[$artist['id']]['loudness'] = $responseSong['loudness'] ?: null;
                        $artistData[$artist['id']]['mode'] = $responseSong['mode'] ?: null;
                        $artistData[$artist['id']]['speechiness'] = $responseSong['speechiness'] ?: null;
                        $artistData[$artist['id']]['acousticness'] = $responseSong['acousticness'] ?: null;
                        $artistData[$artist['id']]['instrumentalness'] = $responseSong['instrumentalness'] ?: null;
                        $artistData[$artist['id']]['liveness'] = $responseSong['liveness'] ?: null;
                        $artistData[$artist['id']]['valence'] = $responseSong['valence'] ?: null;
                        $artistData[$artist['id']]['tempo'] = $responseSong['tempo'] ?: null;
                        // dd($artistData);
                    }
                    break;
                }
                // return response()->json($artistData);
                $artists = implode(", ", $names);

                $item = [
                    'track_id' => $value['id'] ?: null,
                    'track_name' => $value['name'] ?: null,
                    'popularity' => $value['popularity'] ?: null,
                    'artists' => $artists,
                    'genre' => $artistData[$artist['id']]['artistGenre'],
                    'danceability' => $artistData[$artist['id']]['danceability'],
                    'energy' => $artistData[$artist['id']]['energy'],
                    'key' => $artistData[$artist['id']]['key'],
                    'loudness' => $artistData[$artist['id']]['loudness'],
                    'mode' => $artistData[$artist['id']]['mode'],
                    'speechiness' => $artistData[$artist['id']]['speechiness'],
                    'acousticness' => $artistData[$artist['id']]['acousticness'],
                    'instrumentalness' => $artistData[$artist['id']]['instrumentalness'],
                    'liveness' => $artistData[$artist['id']]['liveness'],
                    'valence' => $artistData[$artist['id']]['valence'],
                    'tempo' => $artistData[$artist['id']]['tempo'],
                    'album_id' => $value['album']['id'] ?: null,
                    'album_name' => $value['album']['name'] ?: null,
                    'album_release_date' => $value['album']['release_date'] ?: null,
                    'duration_ms' => $value['duration_ms'] ?: null,
                ];

                $data[] = $item;
                break;
            }

            $dataset[] = $data;
            usleep(100000);
            break;
        }

        // Excel::create('dataset_spotify.csv');
        $now = Carbon::now()->toDateTimeString();
        Excel::store(new DatasetExport($dataset), "dataset_kpop_spotify_$now.csv");
        return response()->json([
            'status' => 'success',
            'data' => $dataset,
        ]);

    }

    public function search(Request $request)
    {
        set_time_limit(0);
        /// set auth
        $token = $request->bearerToken();

        $headers = [
            "Authorization" => "Bearer $token",
        ];

        $count = range(1, 40);

        $dataset = [];
        $artistData = [];

        $dataset[] = [
            'track_id',
            'track_name',
            'artists',
            'popularity',
            'danceability',
            'energy',
            'key',
            'loudness',
            'mode',
            'speechiness',
            'acousticness',
            'instrumentalness',
            'liveness',
            'valence',
            'tempo',
            'album_id',
            'album_name',
            'album_release_date',
            'duration_ms',
        ];

        $offset = 500;
        $limit = 50;
        for ($i = 1; $i <= 10; $i++) {
            if ($limit + $offset >= 1000)
                break;

            $url = "https://api.spotify.com/v1/search?q=year%3A2024&type=track&market=ID&limit=$limit&offset=$offset";
            $resp = Http::withHeaders($headers)->get($url);

            if ($resp->status() == 401) {
                return response()->json(["status" => "Unauthenticated"]);
            }

            if ($resp->status() != 200) {
                return response()->json(["status" => $resp->status(), "response" => json_decode($resp->body(), true)]);
            }
            // if($resp->status() == 401){
            //     $token =json_decode( $this->generateToken()->getContent(), true)['token'];
            //     // dd($token['token']);
            //     $headers = [
            //         "Authorization" => "Bearer $token",
            //     ];
            //     $url = "https://api.spotify.com/v1/search?q=year%3A2024&type=track&market=ID&limit=$limit&offset=$offset";
            //     $resp = Http::withHeaders($headers)->get($url);
            // }
            // dd($resp->json());
            // return response()->json($resp->json());
            $result = $resp->json()['tracks']['items'];

            foreach ($result as $value) {
                // $names = [];
                $artistData = [];
                $names = [];

                foreach ($value['artists'] as $key => $artist) {
                    $names[] = $artist['name'] ?: null;

                    // $isInArray = in_array($artist['id'], $artistData);
                }
                // if(!$isInArray){

                // } else {

                // $artistData[$artist['id']] = [];
                // $url = "https://api.spotify.com/v1/artists/{$artist['id']}";
                // $responseArtist = Http::withHeaders(headers: $headers)->get($url);

                // if($responseArtist->json()['genres']){
                //     $artistData[$artist['id']]['artistGenre']   = $responseArtist->json()['genres'][0];
                // } else{
                //     $artistData[$artist['id']]['artistGenre']   = null;
                // }

                // usleep(500000);
                $url = "https://api.spotify.com/v1/audio-features/{$value['id']}";
                $responseSong = Http::withHeaders(headers: $headers)->get($url);
                if ($responseSong->status() != 200) {
                    return response()->json(
                        ["status" => $responseSong->status(), "response_b" => json_decode($responseSong->body(), true)],
                        $responseSong->status()
                    );
                }

                // return response()->json($responseSong->getHeaders());
                // dd($responseSong);
                $value['danceability'] = $responseSong['danceability'] ?: null;
                $value['energy'] = $responseSong['energy'] ?: null;
                $value['key'] = $responseSong['key'] ?: null;
                $value['loudness'] = $responseSong['loudness'] ?: null;
                $value['mode'] = $responseSong['mode'] ?: null;
                $value['speechiness'] = $responseSong['speechiness'] ?: null;
                $value['acousticness'] = $responseSong['acousticness'] ?: null;
                $value['instrumentalness'] = $responseSong['instrumentalness'] ?: null;
                $value['liveness'] = $responseSong['liveness'] ?: null;
                $value['valence'] = $responseSong['valence'] ?: null;
                $value['tempo'] = $responseSong['tempo'] ?: null;
                // dd($artistData);
                // }
                //     break;
                // }        
                // return response()->json($artistData);
                $artists = implode(", ", $names);
                // dd($artists, $value['name']);
                $item = [
                    $value['id'] ?: null,
                    $value['name'] ?: null,
                    $artists,
                    $value['popularity'] ?: null,
                    // 'genre'                 => $value['artistGenre'],
                    $value['danceability'] ?: null,
                    $value['energy'] ?: null,
                    $value['key'] ?: null,
                    $value['loudness'] ?: null,
                    $value['mode'] ?: null,
                    $value['speechiness'] ?: null,
                    $value['acousticness'] ?: null,
                    $value['instrumentalness'] ?: null,
                    $value['liveness'] ?: null,
                    $value['valence'] ?: null,
                    $value['tempo'] ?: null,
                    $value['album']['id'] ?: null,
                    $value['album']['name'] ?: null,
                    $value['album']['release_date'] ?: null,
                    $value['duration_ms'] ?: null,
                ];

                $dataset[] = $item;
                // dd($dataset);
            }

            // $dataset[] = $data;
            // usleep(100000);
            $offset += 50;
        }

        // Excel::create('dataset_spotify.csv');
        $now = Carbon::now()->toDateTimeString();
        Excel::store(new DatasetExport($dataset), "dataset_kpop_spotify_$now.csv");
        return response()->json([
            'status' => 'success',
            'data' => $dataset,
        ]);
    }
}
