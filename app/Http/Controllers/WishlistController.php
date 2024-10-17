<?php

namespace App\Http\Controllers;

use App\Models\Guiding;

class WishlistController extends Controller
{
    public function addOrRemove(Guiding $guiding)
    {
        if($wishlist_item = auth()->user()->wishlist_items->where('guiding_id', $guiding->id)->first()) {
            $wishlist_item->delete();

            return redirect()->back()->with(['message' => 'Das Guiding wurde erfolgreich von der Wunschliste entfernt!']);
        }

        auth()->user()->wishlist_items()->create([
            'guiding_id' => $guiding->id
        ]);

        return redirect()->back()->with(['message' => 'Das Guiding wurde erfolgreich zur Wunschliste hinzugefügt!']);
    }
}
