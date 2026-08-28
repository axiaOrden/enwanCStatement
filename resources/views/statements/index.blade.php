<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customer Statements</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/share.css') }}">
</head>
<body>
    <div class="ambient-shape ambient-shape-one" aria-hidden="true"></div>
    <div class="ambient-shape ambient-shape-two" aria-hidden="true"></div>

    @if(session('status'))
        <div class="toast success" role="status">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>
            {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div class="toast error" role="alert">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8v5m0 3h.01M10.3 3.8 2.4 17.5A2 2 0 0 0 4.1 20h15.8a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z"/></svg>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="app-shell">
        <header class="app-header">
            <a class="brand" href="{{ route('statements.index') }}" aria-label="Customer Statements home">
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32"><path d="M9 7.5h14a3 3 0 0 1 3 3v11a3 3 0 0 1-3 3H9a3 3 0 0 1-3-3v-11a3 3 0 0 1 3-3Z"/><path d="M11 13h10M11 18.5h6"/></svg>
                </span>
                <span class="brand-copy">
                    <span>Finance workspace</span>
                    <strong>Statements</strong>
                </span>
            </a>

            <div class="header-center" aria-hidden="true">
                <span class="status-dot"></span>
                Ready to create
            </div>

            <div class="user-area">
                <a class="profile-chip" href="{{ route('profile.edit') }}" aria-label="Open profile settings">
                    <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="user-copy"><strong>{{ auth()->user()->name }}</strong><small>Account settings</small></span>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                </a>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="icon-button" type="submit" aria-label="Sign out" title="Sign out">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-3m-5-4h12m-4-4 4 4-4 4"/></svg>
                    </button>
                </form>
            </div>
        </header>

        <main class="layout">
            <h1 class="sr-only">Customer statements</h1>
            <aside class="sidebar">
                <div class="sidebar-intro">
                    <span class="section-kicker">Build a statement</span>
                    <h1>Choose an account</h1>
                    <p>Set the organization and period, then pick a customer to preview.</p>
                </div>

                <form method="get" action="{{ route('statements.index') }}" class="filters">
                    <label class="field">
                        <span class="field-label">Sales organization</span>
                        <span class="select-wrap">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 21V7l8-4 8 4v14M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01M10 21v-3h4v3"/></svg>
                            <select name="sales_org" onchange="this.form.sold_to_party.value='';this.form.submit()">
                                @foreach($organizations as $org)
                                    <option value="{{ $org['sales_org'] }}" @selected(strcasecmp($salesOrg,$org['sales_org'])===0)>
                                        {{ strtoupper($org['sales_org']) }} · {{ number_format($org['customer_count']) }} customers
                                    </option>
                                @endforeach
                            </select>
                        </span>
                    </label>

                    <input type="hidden" name="sold_to_party" value="{{ $selected }}">

                    <label class="field">
                        <span class="field-label">Search customers</span>
                        <span class="search-row">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                            <input type="search" name="q" value="{{ $search }}" placeholder="Name, code or address">
                            <button type="submit" aria-label="Search customers">Search</button>
                        </span>
                    </label>

                    <fieldset class="period-group">
                        <legend>Statement period</legend>
                        <div class="period-fields">
                            <label class="date-field"><span>From</span><input type="date" name="from" value="{{ $from }}" required></label>
                            <span class="date-divider" aria-hidden="true">→</span>
                            <label class="date-field"><span>To</span><input type="date" name="to" value="{{ $to }}" required></label>
                        </div>
                    </fieldset>

                    @if($selected)
                        <button class="primary" type="submit">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 12a8 8 0 1 1-2.3-5.7L20 8.6M20 4v4.6h-4.6"/></svg>
                            Refresh preview
                        </button>
                    @endif
                </form>

                <div class="list-heading">
                    <div><strong>Customers</strong><span>in {{ strtoupper($salesOrg) }}</span></div>
                    <span class="count-badge">{{ $customers?->count() ?? 0 }} shown</span>
                </div>

                <nav class="customer-list" aria-label="Customers">
                    @forelse($customers ?? [] as $customer)
                        @php($query=array_merge(request()->query(),['sales_org'=>$salesOrg,'sold_to_party'=>$customer->sold_to_party,'from'=>$from,'to'=>$to]))
                        <a class="customer {{ $selected===$customer->sold_to_party?'active':'' }}" href="{{ route('statements.index',$query) }}" @if($selected===$customer->sold_to_party) aria-current="page" @endif>
                            <span class="avatar">{{ strtoupper(substr($customer->customer_name ?: $customer->sold_to_party,0,1)) }}</span>
                            <span class="customer-copy">
                                <strong>{{ $customer->customer_name ?: 'Unnamed customer' }}</strong>
                                <small>{{ $customer->sold_to_party }}{{ $customer->state ? ' · '.$customer->state : '' }}</small>
                            </span>
                            <span class="chevron" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
                            </span>
                        </a>
                    @empty
                        <div class="empty-list">
                            <span aria-hidden="true">⌕</span>
                            <strong>No matches found</strong>
                            <p>Try a customer name, code, or address.</p>
                        </div>
                    @endforelse
                </nav>
                @if($customers)<div class="pagination">{{ $customers->links() }}</div>@endif
            </aside>

            <section class="preview-pane">
                @if($selected)
                    @php($params=['sales_org'=>$salesOrg,'sold_to_party'=>$selected,'from'=>$from,'to'=>$to])
                    <div class="preview-toolbar">
                        <div class="preview-title">
                            <span class="preview-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M6 3h9l4 4v14H6z"/><path d="M14 3v5h5M9 13h7M9 17h5"/></svg>
                            </span>
                            <div><span>Live document</span><strong>Statement · {{ $selected }}</strong></div>
                        </div>

                        <div class="preview-actions">
                            <form action="{{ route('statements.share') }}" method="post" class="share-form">
                                @csrf
                                @foreach($params as $key=>$value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach
                                <label class="email-field">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18v12H3z"/><path d="m3 7 9 7 9-7"/></svg>
                                    <span class="sr-only">Recipient email</span>
                                    <input type="email" name="email" aria-label="Recipient email" placeholder="Recipient email" required>
                                </label>
                                <button type="submit">Share PDF</button>
                            </form>
                            <a class="action-button secondary-action" href="{{ route('statements.download',$params) }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m-4-4 4 4 4-4M5 21h14"/></svg>
                                Download
                            </a>
                            <a class="action-button icon-action" href="{{ route('statements.preview',$params) }}" target="_blank" aria-label="Open statement in a new tab" title="Open full page">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M20 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1h5"/></svg>
                            </a>
                        </div>
                    </div>
                    <div class="document-frame"><iframe title="Customer statement preview" src="{{ route('statements.preview',$params) }}"></iframe></div>
                @else
                    <div class="empty-state">
                        <div class="empty-illustration" aria-hidden="true">
                            <span class="shape-back"></span>
                            <span class="document-icon"><svg viewBox="0 0 72 88"><path d="M13 4h31l15 15v65H13z"/><path d="M43 4v17h16M24 38h24M24 50h24M24 62h15"/></svg></span>
                            <span class="spark spark-one">✦</span><span class="spark spark-two">✦</span>
                        </div>
                        <span class="section-kicker">Your preview will appear here</span>
                        <h2>Pick a customer to begin</h2>
                        <p>Select an account from the list and we’ll prepare a polished, ready-to-share statement.</p>
                    </div>
                @endif
            </section>
        </main>
    </div>
</body>
</html>
