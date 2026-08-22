<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatementRequest;
use App\Mail\CustomerStatementMail;
use App\Services\StatementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;

class ShareStatementController extends Controller
{
    public function __invoke(StatementRequest $request, StatementService $statements)
    {
        $request->validate(['email' => ['required', 'email:rfc', 'max:255']]);
        $data = $statements->statement($request->sales_org, $request->sold_to_party, $request->from, $request->to);
        $pdf = Pdf::loadView('statements.document', $data)->setPaper('a4')->output();
        Mail::to($request->email)->send(new CustomerStatementMail($data, $pdf));

        return back()->with('status', "Statement sent to {$request->email}.");
    }
}
