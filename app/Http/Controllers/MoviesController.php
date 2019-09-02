<?php

namespace App\Http\Controllers;

use App\TMDb;
use App\Movies;
use Illuminate\Http\Request;
use App\Http\Resources\MoviesResource;

class MoviesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return MoviesResourceCollection
     */
    public function index(Request $request)
    {
        return MoviesResource::collection(Movies::where('user_id', $request->auth->id)->get());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return MoviesResource
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title'    => 'required|string',
            'overview' => 'required|string',
            'image'    => 'string',
            'movie_id' => 'required|numeric',
        ]);

        $release_date = TMDb::getReleaseDate($request->movie_id);

        $movie = Movies::create([
            'user_id'      => $request->auth->id,
            'title'        => $request->title,
            'overview'     => $request->overview,
            'movie_id'     => $request->movie_id,
            'image'        => $request->image,
            'release_date' => $release_date,
        ]);

        return new MoviesResource($movie);
    }

    /**
     * Update the release date of resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Find all movies without release release_date
        $movies = Movies::where('user_id', $request->auth->id)->whereNull('release_date')->get();

        foreach ($movies as $movie) {
            $release_date = TMDb::getReleaseDate($movie->movie_id);
            if (!is_null($release_date)) {
                $movie->release_date = $release_date;
                $movie->save();
            }
        }

        return response()->json(null, 204);
    }

    /**
     * Search for movies in TMDb.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $this->validate($request, [
            'term' => 'required|string',
        ]);

        return response(TMDb::searchForMovie($request->term), 200)->header('Content-Type', 'application/json');
    }

    /**
     * Get popular movies from TMDb.
     *
     * @return \Illuminate\Http\Response
     */
    public function popular()
    {
        return response(TMDb::popularMovies(), 200)->header('Content-Type', 'application/json');
    }

    /**
     * Get movies for autocomplete from TMDb.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function autocomplete(Request $request)
    {
        $this->validate($request, [
            'term' => 'required|string',
        ]);

        return response(TMDb::autoComplete($request->term), 200)->header('Content-Type', 'application/json');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        // Check if currently authenticated user is the owner of the movie
        $movie = Movies::findOrFail($id);
        if ($request->auth->id != $movie->user_id) {
            return response()->json(['error' => 'You can only delete your own movie.'], 403);
        }

        $movie->delete();
        return response()->json(null, 204);
    }

    public function __construct()
    {
        MoviesResource::withoutWrapping();
    }
}
