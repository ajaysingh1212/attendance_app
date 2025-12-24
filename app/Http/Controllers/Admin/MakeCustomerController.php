<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\MassDestroyMakeCustomerRequest;
use App\Http\Requests\StoreMakeCustomerRequest;
use App\Http\Requests\UpdateMakeCustomerRequest;
use App\Models\MakeCustomer;
use App\Models\ProductCategory;
use Gate;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class MakeCustomerController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        abort_if(Gate::denies('make_customer_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $makeCustomers = MakeCustomer::with(['shop_category', 'media', 'created_by'])
        ->where('created_by_id', auth()->id()) // 👈 filter by logged-in user
        ->get();

        return view('admin.makeCustomers.index', compact('makeCustomers'));
    }

    public function create()
    {
        abort_if(Gate::denies('make_customer_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $shop_categories = ProductCategory::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        return view('admin.makeCustomers.create', compact('shop_categories'));
    }

    public function store(StoreMakeCustomerRequest $request)
    {
        $makeCustomer = MakeCustomer::create(
        $request->all() + ['created_by_id' => auth()->id()] // 👈 yaha set ho raha hai
    );
   

        if ($request->input('shop_image', false)) {
            $makeCustomer->addMedia(storage_path('tmp/uploads/' . basename($request->input('shop_image'))))->toMediaCollection('shop_image');
        }

        if ($request->input('id_proof', false)) {
            $makeCustomer->addMedia(storage_path('tmp/uploads/' . basename($request->input('id_proof'))))->toMediaCollection('id_proof');
        }

        if ($request->input('gst_certificate', false)) {
            $makeCustomer->addMedia(storage_path('tmp/uploads/' . basename($request->input('gst_certificate'))))->toMediaCollection('gst_certificate');
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $makeCustomer->id]);
        }

        return redirect()->route('admin.make-customers.index');
    }

    public function edit(MakeCustomer $makeCustomer)
    {
        abort_if(Gate::denies('make_customer_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $shop_categories = ProductCategory::pluck('name', 'id')->prepend(trans('global.pleaseSelect'), '');

        $makeCustomer->load('shop_category');

        return view('admin.makeCustomers.edit', compact('makeCustomer', 'shop_categories'));
    }

    public function update(UpdateMakeCustomerRequest $request, MakeCustomer $makeCustomer)
    {
        $makeCustomer->update($request->all());

        if ($request->input('shop_image', false)) {
            if (! $makeCustomer->shop_image || $request->input('shop_image') !== $makeCustomer->shop_image->file_name) {
                if ($makeCustomer->shop_image) {
                    $makeCustomer->shop_image->delete();
                }
                $makeCustomer->addMedia(storage_path('tmp/uploads/' . basename($request->input('shop_image'))))->toMediaCollection('shop_image');
            }
        } elseif ($makeCustomer->shop_image) {
            $makeCustomer->shop_image->delete();
        }

        if ($request->input('id_proof', false)) {
            if (! $makeCustomer->id_proof || $request->input('id_proof') !== $makeCustomer->id_proof->file_name) {
                if ($makeCustomer->id_proof) {
                    $makeCustomer->id_proof->delete();
                }
                $makeCustomer->addMedia(storage_path('tmp/uploads/' . basename($request->input('id_proof'))))->toMediaCollection('id_proof');
            }
        } elseif ($makeCustomer->id_proof) {
            $makeCustomer->id_proof->delete();
        }

        if ($request->input('gst_certificate', false)) {
            if (! $makeCustomer->gst_certificate || $request->input('gst_certificate') !== $makeCustomer->gst_certificate->file_name) {
                if ($makeCustomer->gst_certificate) {
                    $makeCustomer->gst_certificate->delete();
                }
                $makeCustomer->addMedia(storage_path('tmp/uploads/' . basename($request->input('gst_certificate'))))->toMediaCollection('gst_certificate');
            }
        } elseif ($makeCustomer->gst_certificate) {
            $makeCustomer->gst_certificate->delete();
        }

        return redirect()->route('admin.make-customers.index');
    }

    public function show(MakeCustomer $makeCustomer)
    {
        abort_if(Gate::denies('make_customer_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $makeCustomer->load('shop_category');

        return view('admin.makeCustomers.show', compact('makeCustomer'));
    }

    public function destroy(MakeCustomer $makeCustomer)
    {
        abort_if(Gate::denies('make_customer_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $makeCustomer->delete();

        return back();
    }

    public function massDestroy(MassDestroyMakeCustomerRequest $request)
    {
        $makeCustomers = MakeCustomer::find(request('ids'));

        foreach ($makeCustomers as $makeCustomer) {
            $makeCustomer->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        abort_if(Gate::denies('make_customer_create') && Gate::denies('make_customer_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $model         = new MakeCustomer();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}