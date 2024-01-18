<?php

namespace App\Services;

use App\Events\DeleteVideo;
use App\Events\UploadNewVideo;
use App\Events\VisitVideo;
use App\Http\Requests\Video\ChangeStateVideoRequest;
use App\Http\Requests\Video\CreateVideoRequest;
use App\Http\Requests\Video\DeleteVideoRequest;
use App\Http\Requests\Video\FavouritesVideoListRequest;
use App\Http\Requests\Video\LikedByCurrentUserVideoRequest;
use App\Http\Requests\Video\LikeVideoRequest;
use App\Http\Requests\Video\ListVideoRequest;
use App\Http\Requests\Video\RepublishVideoRequest;
use App\Http\Requests\Video\ShowVideoCommentsRequest;
use App\Http\Requests\Video\ShowVideoRequest;
use App\Http\Requests\Video\ShowVideoStatisticsRequest;
use App\Http\Requests\Video\UnlikeVideoRequest;
use App\Http\Requests\Video\UpdateVideoRequest;
use App\Http\Requests\Video\UploadVideoBannerRequest;
use App\Http\Requests\Video\UploadVideoRequest;
use App\Playlist;
use App\Video;
use App\VideoFavourite;
use App\VideoRepublish;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoService extends BaseService
{
    public static function list(ListVideoRequest $request)
    {
        $user = auth('api')->user();

        if ($request->has('republished')) {
            if ($user) {
                $videos = $request->republished ? $user->republishedVideos() : $user->channelVideos();
            } else {
                $videos = $request->republished ? Video::whereRepublished() : Video::whereNotRepublished();
            }
        } else {
            $videos = $user ? $user->videos() : Video::query();
        }

        $result = $videos
            ->orderBy('id')
            ->paginate();

        return $result;
    }

    public static function show(ShowVideoRequest $request)
    {
        event(new VisitVideo($request->video));
        $videoData = $request->video->toArray();

        $conditions = [
            'video_id' => $request->video->id,
            'user_id' => auth('api')->check() ? auth('api')->id() : null,
        ];
        if (!auth('api')->check()) {
            $conditions['user_ip'] = client_ip();
        }
        $videoData['liked'] = VideoFavourite::where($conditions)->count();

        $videoData['tags'] = $request->video->tags;

        $videoData['comments'] = sort_comments($request->video->comments);

        $videoData['related_videos'] = $request->video->related()->take(5)->get();

        $videoData['playlist'] = $request->video
            ->playlist()
            ->with('videos')
            ->first();

        return $videoData;
    }

    public static function upload(UploadVideoRequest $request)
    {
        try {
            $video = $request->file('video');
            $fileName = time() . Str::random(10);
            Storage::disk('videos')->put('/tmp/' . $fileName, $video->get());

            return response(['video' => $fileName], 200);
        } catch (Exception $e) {
            return response(['message' => 'خطایی رخ داده است'], 500);
        }
    }

    public static function uploadBanner(UploadVideoBannerRequest $request)
    {
        try {
            $banner = $request->file('banner');
            $fileName = time() . Str::random(10) . '-banner';
            Storage::disk('videos')->put('/tmp/' . $fileName, $banner->get());

            return response(['banner' => $fileName], 200);
        } catch (Exception $e) {
            return response(['message' => 'خطایی رخ داده است'], 500);
        }
    }

    public static function create(CreateVideoRequest $request)
    {
        try {
            DB::beginTransaction();

            // ذخیره ویدیو
            $video = Video::create([
                'title' => $request->title,
                'user_id' => auth()->id(),
                'category_id' => $request->category,
                'channel_category_id' => $request->channel_category,
                'slug' => '',
                'info' => $request->info,
                'duration' => 0,
                'banner' => null,
                'enable_comments' => $request->enable_comments,
                'publish_at' => $request->publish_at,
                'state' => Video::STATE_PENDING,
            ]);

            // ایجاد اسلاگ یکتا از روی آیدی
            $video->slug = uniqueId($video->id);
            $video->banner = $video->slug . '-banner';
            $video->save();

            // ذخیره فایل ویدیو و بنر
            event(new UploadNewVideo($video, $request));
            if ($request->banner) {
                Storage::disk('videos')
                    ->move('/tmp/' . $request->banner, auth()->id() . '/' . $video->banner);
            }

            //تخصیص ویدیو به لیست پخش
            if ($request->playlist) {
                $playlist = Playlist::find($request->playlist);
                $playlist->videos()->attach($video->id);
            }

            // سینک کردن تگ ها
            if (!empty($request->tags)) {
                $video->tags()->attach($request->tags);
            }

            DB::commit();
            return response($video);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response(['message' => 'خطایی رخ داده است'], 500);
        }
    }

    public static function changeState(ChangeStateVideoRequest $request)
    {
        $video = $request->video;

        $video->state = $request->state;
        $video->save();

        return response($video);
    }

    public static function republish(RepublishVideoRequest $request)
    {
        try {
            VideoRepublish::create([
                'user_id' => auth()->id(),
                'video_id' => $request->video->id,
            ]);
            return response(['message' => 'بازنشر با موفقیت انجام شد'], 200);
        } catch (\Exception $exception) {
            Log::error($exception);
            return response(['message' => 'عملیات بازنشر با شکست مواجه شد، مجددا تلاش کنید'], 500);
        }
    }

    public static function like(LikeVideoRequest $request)
    {
        VideoFavourite::create([
            'user_id' => auth('api')->id(),
            'user_ip' => client_ip(),
            'video_id' => $request->video->id,
        ]);

        return response(['message' => 'با موفثیت ثبت شد'], 200);
    }

    public static function unlike(UnlikeVideoRequest $request)
    {
        $user = auth('api')->user();
        $conditions = [
            'video_id' => $request->video->id,
            'user_id' => $user ? $user->id : null
        ];

        if (empty($user)) {
            $conditions['user_ip'] = client_ip();
        }

        VideoFavourite::where($conditions)->delete();
        return response(['message' => 'با موفثیت ثبت شد'], 200);
    }

    public static function likedByCurrentUser(LikedByCurrentUserVideoRequest $request)
    {
        $user = $request->user();
        $videos = $user->favouriteVideos()
            ->paginate();

        return $videos;
    }

    public static function delete(DeleteVideoRequest $request)
    {
        try {
            DB::beginTransaction();
            $request->video->forceDelete();
            event(new DeleteVideo($request->video));
            DB::commit();
            return response(['message' => 'حذف با موفقیت انجام شد'], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response(['message' => 'حذف انجام نشد'], 500);
        }

    }

    public static function statistics(ShowVideoStatisticsRequest $request)
    {
        $fromDate = now()->subDays(
            $request->get('last_n_days', 7)
        )->toDateString();

        $data = [
            'views' => [],
            'total_views' => 0,
        ];

        Video::views($request->user()->id)
            ->where('videos.id', $request->video->id)
            ->whereRaw("date(video_views.created_at) >= '{$fromDate}'")
            ->selectRaw('date(video_views.created_at) as date, count(*) as views')
            ->groupBy(DB::raw('date(video_views.created_at)'))
            ->get()
            ->each(function ($item) use (&$data) {
                $data['total_views'] += $item->views;
                $data['views'][$item->date] = $item->views;
            });

        return $data;
    }

    public static function update(UpdateVideoRequest $request)
    {
        $video = $request->video;

        try {
            DB::beginTransaction();

            if ($request->has('title')) $video->title = $request->title;
            if ($request->has('info')) $video->info = $request->info;
            if ($request->has('category')) $video->category_id = $request->category;
            if ($request->has('channel_category')) $video->channel_category_id = $request->channel_category;
            if ($request->has('enable_comments')) $video->enable_comments = $request->enable_comments;
            if ($request->banner) {
                Storage::disk('videos')
                    ->delete(auth()->id() . '/' . $video->banner);

                Storage::disk('videos')
                    ->move('/tmp/' . $request->banner, auth()->id() . '/' . $video->banner);
            }
            if (!empty($request->tags)) {
                $video->tags()->sync($request->tags);
            }

            DB::commit();
            return response($video);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response(['message' => 'خطایی رخ داده است'], 500);
        }
    }

    public static function favourites(FavouritesVideoListRequest $request)
    {
        $videos = $request->user()
            ->favouriteVideos()
            ->selectRaw('videos.*, channels.name channel_name')
            ->leftJoin('channels', 'channels.user_id', '=', 'videos.user_id')
            ->get();

        return [
            'videos' => $videos,
            'total_fav_videos' => count($videos),
            'total_videos' => $request->user()->channelVideos()->count(),
            'total_comments' => Video::channelComments($request->user()->id)
                ->selectRaw('comments.*')
                ->count(), //TODO تعداد نظرات تایید شده رو باید بگیریم
            'total_views' => Video::views($request->user()->id)->count()
        ];
    }
}
