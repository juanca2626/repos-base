<?php

namespace App\Http\Controllers;

use App\Contact;
use App\ProgressBar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ContactsController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:contacts.read')->only('index');
        $this->middleware('permission:contacts.create')->only('store');
        $this->middleware('permission:contacts.update')->only('update');
        $this->middleware('permission:contacts.delete')->only('delete');
    }

    /**
     * Display a listing of the resource.
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $lang = $request->input("lang");
        $hotel_id = $request->input("hotel_id");
        $contacts = Contact::with([
            'translations' => function ($query) use ($lang) {
                $query->where('type', 'contact');
                $query->whereHas('language', function ($q) use ($lang) {
                    $q->where('iso', $lang);
                });
            }
        ])->where('hotel_id', $hotel_id)->get();

        return Response::json(['success' => true, 'data' => $contacts]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'surname' => 'required',
            'lastname' => 'required',
            'position' => 'required',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {
            $contact = new Contact();
            $contact->name = $request->input('name');
            $contact->surname = $request->input('surname');
            $contact->email = $request->input('email');
            $contact->principal = $request->input('principal');
            $contact->lastname = $request->input('lastname');
            $contact->position = $request->input('position');
            $contact->status = $request->input('status');
            $contact->hotel_id = $request->input('hotel_id');
            $contact->save();

            ProgressBar::firstOrCreate(
                [
                    'slug' => 'hotel_progress_contacts',
                    'value' => 10,
                    'type' => 'hotel',
                    'object_id' => $contact->hotel_id
                ]
            );

            return Response::json(['success' => true]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $contact = Contact::find($id);

        return Response::json(['success' => true, 'data' => $contact]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'surname' => 'required',
            'lastname' => 'required',
            'position' => 'required',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return Response::json(['success' => false]);
        } else {
            $contact = Contact::find($id);
            $contact->name = $request->input('name');
            $contact->surname = $request->input('surname');
            $contact->email = $request->input('email');
            $contact->principal = $request->input('principal');
            $contact->lastname = $request->input('lastname');
            $contact->position = $request->input('position');
            $contact->status = $request->input('status');
            $contact->hotel_id = $request->input('hotel_id');
            $contact->save();

            ProgressBar::updateOrCreate(
                [
                    'slug' => 'hotel_progress_contacts',
                    'value' => 10,
                    'type' => 'hotel',
                    'object_id' => $contact->hotel_id
                ]
            );
            return Response::json(['success' => true]);
        }
    }

    public function updateStatus($id, Request $request)
    {
        $contact = Contact::find($id);
        if ($request->input("status")) {
            $contact->status = false;
        } else {
            $contact->status = true;
        }
        $contact->save();
        return Response::json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        $contact = Contact::find($id);

        $contact->delete();

        return Response::json(['success' => true]);
    }
}
