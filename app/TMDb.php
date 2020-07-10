<?php

namespace App;

use DateTime;

class TMDb
{
    const URL = 'https://api.themoviedb.org/3/';
    const IMAGE_URL = 'https://image.tmdb.org/t/p/';

    public static function getReleaseDate(int $movie_id)
    {
        // Create a stream
        $context = stream_context_create();

        // TMDb API Key
        $key = env('TMDd_KEY');

        /** @var $rel_date DateTime */
        $rel_date = null;
        $release_date = null;

        // Get Release Date
        $data = json_decode(
            file_get_contents(self::URL . "movie/{$movie_id}/release_dates?api_key={$key}", false, $context),
            true
        );
        foreach ($data['results'] as $item) {
            foreach ($item['release_dates'] as $rd) {
                if ($rd['type'] > 3) {
                    $date = new DateTime($rd['release_date']);
                    if (is_null($rel_date)) {
                        $rel_date = clone $date;
                    } else {
                        if ($rel_date > $date) {
                            $rel_date = clone $date;
                        }
                    }
                }
            }
        }

        if (!is_null($rel_date)) {
            $release_date = $rel_date->format('Y-m-d');
        }

        return $release_date;
    }

    public static function searchForMovie(string $query = null)
    {
        // Create a stream
        $context = stream_context_create();

        // TMDb API Key
        $key = env('TMDd_KEY');

        $term = urlencode($query);

        // Get results
        return file_get_contents(
            self::URL . "search/movie?api_key={$key}&language=en-US&query={$term}&page=1&include_adult=false",
            false,
            $context
        );
    }

    public static function popularMovies()
    {
        // Create a stream
        $context = stream_context_create();

        // TMDb API Key
        $key = env('TMDd_KEY');

        // Get results
        return file_get_contents(
            self::URL . "movie/popular?api_key={$key}&language=en-US&page=1",
            false,
            $context
        );
    }

    public static function autoComplete(string $query = null)
    {
        // Create a stream
        $context = stream_context_create();

        // TMDb API Key
        $key = env('TMDd_KEY');

        $term = urlencode($query);

        // Get results
        $results = json_decode(file_get_contents(
            self::URL . "search/movie?api_key={$key}&language=en-US&query={$term}&page=1&include_adult=false",
            false,
            $context
        ), true);

        $movies = [];
        foreach ($results['results'] as $movie) {
            if ($movie['poster_path'] != null) {
                $movies[] = [
                    $movie['original_title'],
                    $movie['release_date'],
                    self::IMAGE_URL . 'w45_and_h67_bestv2' . $movie['poster_path']
                ];
            } else {
                $movies[] = [$movie['original_title'], $movie['release_date'], $movie['poster_path']];
            }
        }

        return json_encode($movies);
    }
}
