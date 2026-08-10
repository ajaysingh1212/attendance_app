<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateNotificationRequest;
use App\Http\Resources\Admin\NotificationResource;
use App\Models\Notification;
use App\Models\Employee;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class NotificationApiController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(
            Gate::denies('notification_access'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );

        return new NotificationResource(Notification::all());
    }

    public function store(StoreNotificationRequest $request)
    {
        $notification = Notification::create($request->all());

        return (new NotificationResource($notification))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Notification $notification)
    {
        abort_if(
            Gate::denies('notification_show'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );

        return new NotificationResource($notification);
    }

    public function update(
        UpdateNotificationRequest $request,
        Notification $notification
    ) {
        $notification->update($request->all());

        return (new NotificationResource($notification))
            ->response()
            ->setStatusCode(Response::HTTP_ACCEPTED);
    }

    public function destroy(Notification $notification)
    {
        abort_if(
            Gate::denies('notification_delete'),
            Response::HTTP_FORBIDDEN,
            '403 Forbidden'
        );

        $notification->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * Get All Notifications
     *
     * Existing notifications +
     * Birthday notifications for today +
     * Work Anniversary notifications for today
     */
    public function getAllNotifications()
    {
        /*
        |--------------------------------------------------------------------------
        | Existing Notifications
        |--------------------------------------------------------------------------
        */

        $notifications = Notification::latest()->get();

        /*
        |--------------------------------------------------------------------------
        | Today's Date
        |--------------------------------------------------------------------------
        */

        $today = Carbon::today();

        $dynamicNotifications = collect();


        /*
        |--------------------------------------------------------------------------
        | Employees
        |--------------------------------------------------------------------------
        */

        $employees = Employee::where(function ($query) use ($today) {

            $query
                ->where(function ($q) use ($today) {
                    $q->whereNotNull('date_of_birth')
                        ->whereMonth('date_of_birth', $today->month)
                        ->whereDay('date_of_birth', $today->day);
                })
                ->orWhere(function ($q) use ($today) {
                    $q->whereNotNull('anniversary_date')
                        ->whereMonth('anniversary_date', $today->month)
                        ->whereDay('anniversary_date', $today->day);
                });

        })->get();


        /*
        |--------------------------------------------------------------------------
        | Birthday / Anniversary Notifications
        |--------------------------------------------------------------------------
        */

        foreach ($employees as $employee) {

            /*
            |--------------------------------------------------------------------------
            | 🎂 Birthday
            |--------------------------------------------------------------------------
            */

            if (
                !empty($employee->date_of_birth) &&
                Carbon::parse($employee->date_of_birth)->month == $today->month &&
                Carbon::parse($employee->date_of_birth)->day == $today->day
            ) {

                $dynamicNotifications->push([
                    'id' => 'birthday-' . $employee->id . '-' . $today->format('Y-m-d'),

                    'heading' => '🎂 Happy Birthday!',

                    'content' => '<p>Wishing <strong>'
                        . e($employee->full_name)
                        . '</strong> a very Happy Birthday! 🎉</p>'
                        . '<p>May your special day be filled with happiness, success and wonderful moments.</p>',

                    'created_at' => now()->format('Y-m-d H:i:s'),

                    'updated_at' => now()->format('Y-m-d H:i:s'),

                    'deleted_at' => null,
                ]);
            }


            /*
            |--------------------------------------------------------------------------
            | 🎉 Work Anniversary
            |--------------------------------------------------------------------------
            */

            if (
                !empty($employee->anniversary_date) &&
                Carbon::parse($employee->anniversary_date)->month == $today->month &&
                Carbon::parse($employee->anniversary_date)->day == $today->day
            ) {

                $dynamicNotifications->push([
                    'id' => 'anniversary-' . $employee->id . '-' . $today->format('Y-m-d'),

                    'heading' => '🎉 Happy Work Anniversary!',

                    'content' => '<p>Congratulations to <strong>'
                        . e($employee->full_name)
                        . '</strong> on their work anniversary! 🎊</p>'
                        . '<p>Thank you for your valuable contribution and dedication. Wishing you many more successful years ahead!</p>',

                    'created_at' => now()->format('Y-m-d H:i:s'),

                    'updated_at' => now()->format('Y-m-d H:i:s'),

                    'deleted_at' => null,
                ]);
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Existing + Dynamic Notifications
        |--------------------------------------------------------------------------
        */

        $existingNotifications = NotificationResource::collection($notifications)
            ->resolve();

        $allNotifications = collect($existingNotifications)
            ->merge($dynamicNotifications)
            ->sortByDesc('created_at')
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Final Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'status' => true,
            'message' => 'All notifications fetched successfully',
            'data' => $allNotifications
        ]);
    }
}