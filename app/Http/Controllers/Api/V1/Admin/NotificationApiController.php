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
        $data = $request->all();

        // Remove emojis from notification fields
        if (isset($data['heading'])) {
            $data['heading'] = $this->removeEmojis($data['heading']);
        }

        if (isset($data['content'])) {
            $data['content'] = $this->removeEmojis($data['content']);
        }

        $notification = Notification::create($data);

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
        $data = $request->all();

        // Remove emojis from notification fields
        if (isset($data['heading'])) {
            $data['heading'] = $this->removeEmojis($data['heading']);
        }

        if (isset($data['content'])) {
            $data['content'] = $this->removeEmojis($data['content']);
        }

        $notification->update($data);

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
     * Personal Anniversary notifications for today +
     * Work Anniversary notifications for today
     */
    public function getAllNotifications()
    {
        // Existing notifications
        $notifications = Notification::latest()->get();

        // Today
        $today = Carbon::today();

        // Dynamic notifications
        $dynamicNotifications = collect();

        // Employees jinki koi bhi special date aaj hai
        $employees = Employee::where(function ($query) use ($today) {

            // Birthday
            $query->where(function ($q) use ($today) {
                $q->whereNotNull('date_of_birth')
                    ->whereMonth('date_of_birth', $today->month)
                    ->whereDay('date_of_birth', $today->day);
            })

            // Personal Anniversary
            ->orWhere(function ($q) use ($today) {
                $q->whereNotNull('anniversary_date')
                    ->whereMonth('anniversary_date', $today->month)
                    ->whereDay('anniversary_date', $today->day);
            })

            // Work Anniversary from Date of Joining
            ->orWhere(function ($q) use ($today) {
                $q->whereNotNull('date_of_joining')
                    ->whereMonth('date_of_joining', $today->month)
                    ->whereDay('date_of_joining', $today->day);
            });

        })->get();

        foreach ($employees as $employee) {

            /*
            |--------------------------------------------------------------------------
            | Birthday
            |--------------------------------------------------------------------------
            */

            if (
                $employee->date_of_birth &&
                Carbon::parse($employee->date_of_birth)->month == $today->month &&
                Carbon::parse($employee->date_of_birth)->day == $today->day
            ) {

                $dynamicNotifications->push([
                    'id' => 'birthday-' . $employee->id . '-' . $today->format('Y-m-d'),

                    'heading' => 'Happy Birthday!',

                    'content' =>
                        '<p>Wishing <strong>' .
                        e($employee->full_name) .
                        '</strong> a very Happy Birthday!</p>' .

                        '<p>May your special day be filled with happiness, success and wonderful moments.</p>',

                    'created_at' => $today->format('Y-m-d H:i:s'),

                    'updated_at' => $today->format('Y-m-d H:i:s'),

                    'deleted_at' => null,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Personal Anniversary
            |--------------------------------------------------------------------------
            */

            if (
                $employee->anniversary_date &&
                Carbon::parse($employee->anniversary_date)->month == $today->month &&
                Carbon::parse($employee->anniversary_date)->day == $today->day
            ) {

                $dynamicNotifications->push([
                    'id' => 'anniversary-' . $employee->id . '-' . $today->format('Y-m-d'),

                    'heading' => 'Happy Anniversary!',

                    'content' =>
                        '<p>Warm wishes to <strong>' .
                        e($employee->full_name) .
                        '</strong> on this special anniversary!</p>' .

                        '<p>Wishing you happiness, love and many more beautiful years ahead.</p>',

                    'created_at' => $today->format('Y-m-d H:i:s'),

                    'updated_at' => $today->format('Y-m-d H:i:s'),

                    'deleted_at' => null,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Work Anniversary - Date of Joining
            |--------------------------------------------------------------------------
            */

            if (
                $employee->date_of_joining &&
                Carbon::parse($employee->date_of_joining)->month == $today->month &&
                Carbon::parse($employee->date_of_joining)->day == $today->day
            ) {

                $joiningDate = Carbon::parse($employee->date_of_joining);

                $yearsCompleted = $joiningDate->diffInYears($today);

                $yearText = $yearsCompleted == 1
                    ? '1 year'
                    : $yearsCompleted . ' years';

                $dynamicNotifications->push([
                    'id' => 'work-anniversary-' . $employee->id . '-' . $today->format('Y-m-d'),

                    'heading' => 'Work Anniversary!',

                    'content' =>
                        '<p>Congratulations to <strong>' .
                        e($employee->full_name) .
                        '</strong> on completing <strong>' .
                        $yearText .
                        '</strong> with us!</p>' .

                        '<p>Thank you for your dedication, hard work and valuable contribution. Wishing you many more successful years ahead!</p>',

                    'created_at' => $today->format('Y-m-d H:i:s'),

                    'updated_at' => $today->format('Y-m-d H:i:s'),

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

        /*
        |--------------------------------------------------------------------------
        | Remove Emojis From Existing Notifications
        |--------------------------------------------------------------------------
        */

        $existingNotifications = collect($existingNotifications)
            ->map(function ($notification) {

                if (isset($notification['heading'])) {
                    $notification['heading'] = $this->removeEmojis(
                        $notification['heading']
                    );
                }

                if (isset($notification['content'])) {
                    $notification['content'] = $this->removeEmojis(
                        $notification['content']
                    );
                }

                return $notification;
            });

        /*
        |--------------------------------------------------------------------------
        | Merge + Sort
        |--------------------------------------------------------------------------
        */

        $allNotifications = $existingNotifications
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

    /**
     * Remove emojis and unsupported Unicode characters.
     */
    private function removeEmojis($text)
    {
        if ($text === null) {
            return '';
        }

        $text = (string) $text;

        return preg_replace(
            [
                '/[\x{1F600}-\x{1F64F}]/u',
                '/[\x{1F300}-\x{1F5FF}]/u',
                '/[\x{1F680}-\x{1F6FF}]/u',
                '/[\x{1F700}-\x{1F77F}]/u',
                '/[\x{1F780}-\x{1F7FF}]/u',
                '/[\x{1F800}-\x{1F8FF}]/u',
                '/[\x{1F900}-\x{1F9FF}]/u',
                '/[\x{1FA00}-\x{1FAFF}]/u',
                '/[\x{2600}-\x{26FF}]/u',
                '/[\x{2700}-\x{27BF}]/u',
                '/[\x{FE00}-\x{FE0F}]/u',
                '/[\x{1F1E6}-\x{1F1FF}]/u',
                '/[\x{200D}]/u',
            ],
            '',
            $text
        );
    }
}