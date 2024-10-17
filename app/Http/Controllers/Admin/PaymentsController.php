<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;

class PaymentsController extends Controller
{
    public function index()
    {
        return view('admin.pages.payments.index', [
            'transactions' => Transaction::all(),
            'payments' => Payment::all()
        ]);
    }

    public function showoutpayments($id)
    {
        $payment = Payment::find($id);
        return view('admin.pages.payments.edit', compact('payment'));
    }

    public function deletepayments($id)
    {
        $payment = Payment::find($id);
        $payment->delete();
        return redirect()->route('admin.payments.index')->with('message', 'Die Zahlung wurde abgelehnt!');
    }

    public function aproveoutpayments($id)
    {
        $payment = Payment::find($id);
        $payment->is_completed = 1;
        $payment->save();
        $user = \App\Models\User::find($payment->user_id);

        $wallet = Wallet::where('holder_id', $user->id)->first();
        $wallet->balance -= $payment->amount;
        $wallet->save();

        $user->pending_balance -= $payment->amount;
        $user->save();

        return back()->with('message', 'Die Zahlung wurde abgelehnt!');

    }

}
