<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\CsvImportTrait;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyVisitRequest;
use App\Http\Requests\StoreVisitRequest;
use App\Http\Requests\UpdateVisitRequest;
use App\Models\Visit;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Str;

class VisitController extends Controller
{
    use MediaUploadingTrait, CsvImportTrait;

    public function index(Request $request)
    {
        abort_if(Gate::denies('visit_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        if ($request->ajax()) {
              $query = Visit::where('user', auth()->id())
            ->select(sprintf('%s.*', (new Visit)->table));
            $table = Datatables::of($query);

            $table->addColumn('placeholder', '&nbsp;');
            $table->addColumn('actions', '&nbsp;');

            $table->editColumn('actions', function ($row) {
                $viewGate      = 'visit_show';
                $editGate      = 'visit_edit';
                $deleteGate    = 'visit_delete';
                $crudRoutePart = 'visits';

                return view('partials.datatablesActions', compact(
                    'viewGate',
                    'editGate',
                    'deleteGate',
                    'crudRoutePart',
                    'row'
                ));
            });

            $table->editColumn('id', function ($row) {
                return $row->id ? $row->id : '';
            });
            $table->editColumn('user', function ($row) {
                return $row->user ? $row->user : '';
            });
            $table->editColumn('latitude', function ($row) {
                return $row->latitude ? $row->latitude : '';
            });
            $table->editColumn('longitude', function ($row) {
                return $row->longitude ? $row->longitude : '';
            });
            $table->editColumn('location', function ($row) {
                return $row->location ? $row->location : '';
            });
            $table->editColumn('visited_time', function ($row) {
                return $row->visited_time ? $row->visited_time : '';
            });
            $table->editColumn('visited_counter_image', function ($row) {
                if ($photo = $row->visited_counter_image) {
                    return sprintf(
                        '<a href="%s" target="_blank"><img src="%s" width="50px" height="50px"></a>',
                        $photo->url,
                        $photo->thumbnail
                    );
                }

                return '';
            });
            $table->editColumn('visit_self_image', function ($row) {
                if ($photo = $row->visit_self_image) {
                    return sprintf(
                        '<a href="%s" target="_blank"><img src="%s" width="50px" height="50px"></a>',
                        $photo->url,
                        $photo->thumbnail
                    );
                }

                return '';
            });
            $table->editColumn('visited_out_latitude', function ($row) {
                return $row->visited_out_latitude ? $row->visited_out_latitude : '';
            });
            $table->editColumn('visited_out_longitude', function ($row) {
                return $row->visited_out_longitude ? $row->visited_out_longitude : '';
            });
            $table->editColumn('visited_out_location', function ($row) {
                return $row->visited_out_location ? $row->visited_out_location : '';
            });
            $table->editColumn('visited_out_time', function ($row) {
                return $row->visited_out_time ? $row->visited_out_time : '';
            });
            $table->editColumn('visited_out_counter_image', function ($row) {
                if ($photo = $row->visited_out_counter_image) {
                    return sprintf(
                        '<a href="%s" target="_blank"><img src="%s" width="50px" height="50px"></a>',
                        $photo->url,
                        $photo->thumbnail
                    );
                }

                return '';
            });
            $table->editColumn('visited_out_self_image', function ($row) {
                if ($photo = $row->visited_out_self_image) {
                    return sprintf(
                        '<a href="%s" target="_blank"><img src="%s" width="50px" height="50px"></a>',
                        $photo->url,
                        $photo->thumbnail
                    );
                }

                return '';
            });
            $table->editColumn('visited_duration', function ($row) {
                return $row->visited_duration ? $row->visited_duration : 'minutes';
            });

            $table->rawColumns(['actions', 'placeholder', 'visited_counter_image', 'visit_self_image', 'visited_out_counter_image', 'visited_out_self_image']);

            return $table->make(true);
        }

        return view('admin.visits.index');
    }

  public function create()
{
    abort_if(Gate::denies('visit_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    // Active visit nikalna (jo abhi tak OUT nahi hua)
    $activeVisit = Visit::where('user', auth()->id())
        ->whereNull('visited_out_time')
        ->latest()
        ->first();

    return view('admin.visits.create', compact('activeVisit'));
}

public function start(Request $request)
{
    
    abort_if(Gate::denies('visit_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    $request->validate([
        'latitude'  => ['required','numeric'],
        'title'  => ['required','string'],
        'longitude' => ['required','numeric'],
        'visited_counter_image' => ['required'],
        'visit_self_image'      => ['required'],
    ]);

    // Agar already active visit hai (out nahi hua)
    $activeVisit = Visit::where('user', auth()->id())
        ->whereNull('visited_out_time')
        ->first();

    if($activeVisit){
        return redirect()->route('admin.visits.create')
            ->with('flash', 'You already have an active visit. Please check-out first.');
    }

    // Naya visit create karo
    $visit = Visit::create([
        'user'         => auth()->id(),
        'latitude'     => $request->latitude,
        'title'     => $request->title,
        'longitude'    => $request->longitude,
        'location'     => $request->input('location'),
        'visited_time' => now(),
    ]);


    $this->handleImageUpload($visit, $request, 'visited_counter_image');
    $this->handleImageUpload($visit, $request, 'visit_self_image');

    return redirect()->route('admin.visits.create')
        ->with('flash', 'Check-in successful. Now mark Out when you leave.');
}

public function out(Request $request, Visit $visit)
{
    abort_if(Gate::denies('visit_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

    if ((int) $visit->user !== (int) auth()->id()) {
        abort(Response::HTTP_FORBIDDEN, 'Not your visit');
    }

    $request->validate([
        'visited_out_latitude'  => ['required','numeric'],
        'visited_out_longitude' => ['required','numeric'],
        'visited_out_counter_image' => ['required'],
        'visited_out_self_image'    => ['required'],
    ]);

    // Abhi ka time check-out ke liye
    $outTime = now();

    // Duration calculate karo (minutes me)
   $duration = $visit->visited_time 
    ? Carbon::parse($visit->visited_time)->diffInMinutes($outTime) 
    : null;

    $visit->update([
        'visited_out_latitude'  => $request->visited_out_latitude,
        'visited_out_longitude' => $request->visited_out_longitude,
        'visited_out_location'  => $request->input('visited_out_location'),
        'visited_out_time'      => $outTime,
        'visited_duration'      => $duration, // yahan save hoga
    ]);

    $this->handleImageUpload($visit, $request, 'visited_out_counter_image');
    $this->handleImageUpload($visit, $request, 'visited_out_self_image');

    return redirect()->route('admin.visits.create')
        ->with('flash', 'Visit closed (Out marked). You can check-in again.');
}


   protected function handleImageUpload($model, Request $request, $field)
{
    // Case 1: normal file upload
    if ($request->hasFile($field)) {
        $model->addMediaFromRequest($field)->toMediaCollection($field);
        return;
    }

    // Case 2: base64 string
    $base64 = $request->input($field);
    if ($base64 && Str::startsWith($base64, 'data:image')) {
        $image = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $image = str_replace(' ', '+', $image);

        $fileName = Str::uuid() . '.png';
        $tmpPath = storage_path('app/tmp/' . $fileName);

        if (!file_exists(dirname($tmpPath))) {
            mkdir(dirname($tmpPath), 0755, true);
        }

        file_put_contents($tmpPath, base64_decode($image));

        // Spatie will MOVE this file, so no need to manually unlink
        $model->addMedia($tmpPath)->toMediaCollection($field);

        // Optional: only unlink if file still exists
        if (file_exists($tmpPath)) {
            unlink($tmpPath);
        }
    }
}

    public function edit(Visit $visit)
    {
        abort_if(Gate::denies('visit_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.visits.edit', compact('visit'));
    }

    public function update(UpdateVisitRequest $request, Visit $visit)
    {
        $visit->update($request->all());

        if ($request->input('visited_counter_image', false)) {
            if (! $visit->visited_counter_image || $request->input('visited_counter_image') !== $visit->visited_counter_image->file_name) {
                if ($visit->visited_counter_image) {
                    $visit->visited_counter_image->delete();
                }
                $visit->addMedia(storage_path('tmp/uploads/' . basename($request->input('visited_counter_image'))))->toMediaCollection('visited_counter_image');
            }
        } elseif ($visit->visited_counter_image) {
            $visit->visited_counter_image->delete();
        }

        if ($request->input('visit_self_image', false)) {
            if (! $visit->visit_self_image || $request->input('visit_self_image') !== $visit->visit_self_image->file_name) {
                if ($visit->visit_self_image) {
                    $visit->visit_self_image->delete();
                }
                $visit->addMedia(storage_path('tmp/uploads/' . basename($request->input('visit_self_image'))))->toMediaCollection('visit_self_image');
            }
        } elseif ($visit->visit_self_image) {
            $visit->visit_self_image->delete();
        }

        if ($request->input('visited_out_counter_image', false)) {
            if (! $visit->visited_out_counter_image || $request->input('visited_out_counter_image') !== $visit->visited_out_counter_image->file_name) {
                if ($visit->visited_out_counter_image) {
                    $visit->visited_out_counter_image->delete();
                }
                $visit->addMedia(storage_path('tmp/uploads/' . basename($request->input('visited_out_counter_image'))))->toMediaCollection('visited_out_counter_image');
            }
        } elseif ($visit->visited_out_counter_image) {
            $visit->visited_out_counter_image->delete();
        }

        if ($request->input('visited_out_self_image', false)) {
            if (! $visit->visited_out_self_image || $request->input('visited_out_self_image') !== $visit->visited_out_self_image->file_name) {
                if ($visit->visited_out_self_image) {
                    $visit->visited_out_self_image->delete();
                }
                $visit->addMedia(storage_path('tmp/uploads/' . basename($request->input('visited_out_self_image'))))->toMediaCollection('visited_out_self_image');
            }
        } elseif ($visit->visited_out_self_image) {
            $visit->visited_out_self_image->delete();
        }

        return redirect()->route('admin.visits.index');
    }

    public function show(Visit $visit)
    {
        abort_if(Gate::denies('visit_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.visits.show', compact('visit'));
    }

    public function destroy(Visit $visit)
    {
        abort_if(Gate::denies('visit_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $visit->delete();

        return back();
    }

    public function massDestroy(MassDestroyVisitRequest $request)
    {
        $visits = Visit::find(request('ids'));

        foreach ($visits as $visit) {
            $visit->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('visit_create') && Gate::denies('visit_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new Visit();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
