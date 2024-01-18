<?php

namespace App\Services;


use App\Http\Requests\Playlist\AddVideoToPlaylistRequest;
use App\Http\Requests\Playlist\CreatePlaylistRequest;
use App\Http\Requests\Playlist\ListPlaylistRequest;
use App\Http\Requests\Playlist\MyPlaylistRequest;
use App\Http\Requests\Playlist\ShowPlaylistRequest;
use App\Http\Requests\Playlist\SortVideoInPlaylistRequest;
use App\Playlist;
use Illuminate\Support\Facades\DB;

class PlaylistService extends BaseService
{
    public static function getAll(ListPlaylistRequest $request)
    {
        return Playlist::all();
    }

    public static function my(MyPlaylistRequest $request)
    {
        return Playlist::where('user_id', auth()->id())->get();
    }

    public static function show(ShowPlaylistRequest $request)
    {
        return Playlist::with('videos')
            ->find($request->playlist->id);
    }

    public static function create(CreatePlaylistRequest $request)
    {
        $data = $request->validated();
        $user = auth()->user();
        $playlist = $user->playlists()->create($data);
        return response($playlist);
    }

    public static function addVideo(AddVideoToPlaylistRequest $request)
    {
        DB::table('playlist_videos')
            ->where('video_id', $request->video->id)
            ->delete();

        $request->playlist
            ->videos()
            ->attach($request->video->id);

        return response(['message' => 'ویدیو با موفقیت به لیست پخش مورد نظر اضافه شد'], 200);
    }

    public static function sortVideos(SortVideoInPlaylistRequest $request)
    {
        $request->playlist
            ->videos()
            ->detach($request->videos);

        $request->playlist
            ->videos()
            ->attach($request->videos);

        return response(['message' => 'لیست پخش با موفقیت مرتب سازی شد'], 200);
    }


}
