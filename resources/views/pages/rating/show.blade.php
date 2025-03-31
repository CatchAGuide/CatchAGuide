@extends('pages.profile.layouts.profile')

@section('profile-content')
    <div class="container">
        <div class="card">
            <h4 class="card-header">Bewertung für {{ $user->full_name }} abgeben</h4>
            <div class="card-body">
                <form action="{{ route('ratings.store', $booking->id) }}" method="POST">
                    @csrf
                    <div class="form-control">
                        <input id="input-4" name="rating" class="rating" data-show-clear="false" data-show-caption="true">
                        <label for="description">Kommentar</label>
                        <textarea name="description" id="description" class="form-control" cols="30" rows="10"></textarea>
                    </div>
                    <button type="submit" class="thm-btn mt-3">Absenden</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('css_after')
    <style>
        .rating-container > .caption {
            display: none;
        }
    </style>
@endsection
