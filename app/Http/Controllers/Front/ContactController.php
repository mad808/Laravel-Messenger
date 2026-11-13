<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = auth()->user()->contacts()->with('contactUser')->get();
        return view('front.contacts.index', compact('contacts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'nickname' => 'nullable|string|max:50',
        ]);

        $digits = preg_replace('/\D/', '', $request->phone);
        if (strlen($digits) === 8) $phone = '+993' . $digits;
        elseif (strlen($digits) === 11 && str_starts_with($digits, '993')) $phone = '+' . $digits;
        else $phone = '+' . $digits;

        if ($phone === auth()->user()->phone) {
            return back()->with('error', 'Kendinizi rehbere ekleyemezsiniz.');
        }

        $userToAdd = User::where('phone', $phone)->first();

        if (!$userToAdd) {
            return back()->with('error', 'Bu numaraya ait kayıtlı bir kullanıcı bulunamadı.');
        }

        $exists = auth()->user()->contacts()->where('contact_user_id', $userToAdd->id)->exists();
        if ($exists) {
            return back()->with('error', 'Bu kişi zaten rehberinizde.');
        }

        Contact::create([
            'user_id' => auth()->id(),
            'contact_user_id' => $userToAdd->id,
            'nickname' => $request->nickname,
        ]);

        return back()->with('success', 'Kişi rehbere eklendi.');
    }

    public function destroy(Contact $contact)
    {
        if ($contact->user_id !== auth()->id()) {
            abort(403);
        }
        $contact->delete();
        return back()->with('success', 'Kişi rehberden silindi.');
    }
}