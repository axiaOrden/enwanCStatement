<?php

namespace App\Http\Controllers;

use App\Http\Requests\StatementRequest;
use App\Services\StatementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatementController extends Controller
{
    public function __construct(private readonly StatementService $statements) {}

    public function index(Request $request): View
    {
        $organizations = $this->statements->organizations();
        $salesOrg = trim((string) $request->input('sales_org', $organizations[0]['sales_org'] ?? ''));
        $search = trim((string) $request->input('q', ''));
        $selected = trim((string) $request->input('sold_to_party', ''));
        $from = (string) $request->input('from', now()->startOfMonth()->toDateString());
        $to = (string) $request->input('to', now()->endOfMonth()->toDateString());

        return view('statements.index', [
            'organizations' => $organizations,
            'customers' => $salesOrg === '' ? null : $this->statements->customers($salesOrg, $search),
            'salesOrg' => $salesOrg, 'search' => $search, 'selected' => $selected, 'from' => $from, 'to' => $to,
        ]);
    }

    public function preview(StatementRequest $request): View
    {
        return view('statements.document', $this->statements->statement(...$this->parameters($request)));
    }

    public function download(StatementRequest $request)
    {
        $data = $this->statements->statement(...$this->parameters($request));
        $filename = "Statement_{$request->sold_to_party}_{$request->from}_to_{$request->to}.pdf";
        return Pdf::loadView('statements.document', $data)->setPaper('a4')->download($filename);
    }

    private function parameters(StatementRequest $request): array
    {
        return [$request->string('sales_org')->toString(), $request->string('sold_to_party')->toString(), $request->string('from')->toString(), $request->string('to')->toString()];
    }
}
