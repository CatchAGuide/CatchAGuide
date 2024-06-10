@extends('pages.profile.layouts.profile')
@section('title', 'Guthaben auszahlen')
@section('profile-content')
     <div class="row">
        <div class="col-12">
            <h5>Dein aktuelles Guthaben: {{ two(auth()->user()->balance - auth()->user()->pending_balance) }} €</h5>
            <h5>Dein schwebendes Guthaben: {{ two(auth()->user()->pending_balance) }} €</h5>
            <h5>Dein ausbezahltes Guthaben: {{ two(auth()->user()->paid_balance) }} €</h5>

            <form action="{{route('profile.getbalance')}}" method="POST">
                @csrf
                <div class="row mt-3">
                    <div class="col-10">
                        <input type="number" class="form-control mt-2" placeholder="Auszahlungsbetrag" name="amount" max="{{Auth::user()->balance - (auth()->user()->pending_balance)}}">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="thm-btn">Auszahlen</button>
                    </div>
                </div>
                <!-- TODO Guthaben auszahlen einbauen -->
            </form>
        </div>
    </div>
    <hr>

    <div class="row">
        <div class="col-12">
            <table class="table">
                <thead>
                <tr>
                    <td>Transaktions-ID</td>
                    <td>Typ</td>
                    <td>Anzahl</td>
                    <td>Datum</td>
                </tr>
                </thead>
                <tbody>
                @foreach(auth()->user()->transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->uuid }}</td>
                        <td>
                            @if($transaction->type === 'deposit')
                                <span class="badge bg-secondary">Einzahlung</span>
                            @else
                                <span class="badge bg-secondary">Auszahlung/Bezahlung</span>
                            @endif
                        </td>
                        <td>{{ two($transaction->amount) }} €</td>
                        <td>{{ $transaction->created_at->format('H:i d.m.Y')  }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('js_after')

@endsection
