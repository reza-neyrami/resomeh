<?php

namespace App\Services;


use App\Channel;
use App\Comment;
use App\Http\Requests\Channel\FollowChannelRequest;
use App\Http\Requests\Channel\StatisticsRequest;
use App\Http\Requests\Channel\UnFollowChannelRequest;
use App\Http\Requests\Channel\UpdateChannelRequest;
use App\Http\Requests\Channel\UpdateSocialsRequest;
use App\Http\Requests\Channel\UploadBannerForChannelRequest;
use App\Video;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChannelService extends BaseService
{
    public static function updateChannelInfo(UpdateChannelRequest $request)
    {
        try {

            DB::beginTransaction();

            $channel->name = $request->name;
            $channel->info = $request->info;
            $channel->save();

            $user->website = $request->website;
            $user->save();

            DB::commit();
            return response(['message' => 'ثبت تغییرات کانال انجام شد'], 200);
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            return response(['message' => 'خطایی رخ داده است'], 500);
        }
    }

    public static function uploadAvatarForChannel(UploadBannerForChannelRequest $request)
    {
        try {
            $banner = $request->file('banner');
            $fileName = md5(auth()->id()) . '-' . Str::random(15);
            Storage::disk('channel')->put($fileName, $banner->get());

            $channel = auth()->user()->channel;
            if ($channel->banner) {
                Storage::disk('channel')->delete($channel->banner);
            }
            $channel->banner = Storage::disk('channel')->path($fileName);
            $channel->save();

            return response([
                'banner' => Storage::disk('channel')->url($fileName)
            ], 200);
        } catch (\Exception $e) {
            return response(['message' => 'خطایی رخ داده است'], 500);
        }
    }

    public static function updateSocials(UpdateSocialsRequest $request)
    {
        try {
            $socials = [
                'cloob' => $request->input('cloob'),
                'lenzor' => $request->input('lenzor'),
                'facebook' => $request->input('facebook'),
                'twitter' => $request->input('twitter'),
                'telegram' => $request->input('telegram'),
            ];

            auth()->user()->channel->update(['socials' => $socials]);
            return response(['message' => 'با موفقیت ثبت شد'], 200);
        } catch (Exception $exception) {
            Log::error($exception);
            return response(['message' => 'خطایی رخ داده است'], 500);
        }
    }

    public static function statistics(StatisticsRequest $request)
    {
        $fromDate = now()->subDays(
            $request->get('last_n_days', 7)
        )->toDateString();

        $data = [
            'views' => [],
            'total_views' => 0,
            'total_followers' => $request->user()->followers()->count(),
            'total_videos' => $request->user()->channelVideos()->count(),
            'total_comments' => Video::channelComments($request->user()->id)
                ->selectRaw('comments.*')
                ->count(), //TODO تعداد نظرات تایید نشده رو باید بگیریم
        ];

        Video::views($request->user()->id)
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
}
