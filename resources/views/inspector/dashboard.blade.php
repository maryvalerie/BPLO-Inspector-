@extends('layouts.app')

@section('title', 'Inspector Portal')

@section('content')
    <div class="page-header inspector-page-header">
        <div>
            <p class="inspector-eyebrow">FIELD OPERATIONS</p>
            <h1>Inspector workspace</h1>
            <p class="muted mb-0">Verify permits on-site and monitor records issued by BPLO.</p>
        </div>
        <a href="{{ route('inspector.permits.index') }}" class="btn">Permit Monitoring</a>
    </div>

    <div class="stat-grid">
        <x-stat-card :value="$counts['active']" label="Active Permits" icon="✅" accent="green" />
        <x-stat-card :value="$counts['expired']" label="Expired Permits" icon="⚠️" accent="amber" />
        <x-stat-card :value="$counts['revoked']" label="Revoked Permits" icon="⛔" accent="red" />
        <x-stat-card :value="$counts['scans_today']" label="Scans Today" icon="🔍" accent="mint" />
    </div>

    <div class="inspector-tools-grid">
        <section class="card">
            <div class="card-head">
                <div>
                    <h2 class="section">QR verification</h2>
                    <p class="muted mb-0">A scan opens the system verification result and is added to the shared scan history.</p>
                </div>
            </div>
            <div class="inspector-scan-actions">
                <button type="button" id="start-qr-scan" class="btn" data-verify-url="{{ route('verify.lookup') }}"><span aria-hidden="true">▦</span> Start camera scanner</button>
                <button type="button" id="stop-qr-scan" class="btn btn-secondary" hidden>Stop camera</button>
                <label for="qr-image-upload" class="btn btn-secondary inspector-upload-button"><span aria-hidden="true">↑</span> Upload QR image</label>
                <input type="file" id="qr-image-upload" accept="image/*" hidden>
            </div>
            <p id="qr-scan-status" class="inspector-scan-status" role="status" aria-live="polite">
                Use your camera or upload a clear photo/screenshot of a permit QR code. You can also enter its code below.
            </p>
            <div id="qr-scanner-panel" class="inspector-scanner-panel" hidden>
                <video id="qr-camera" class="inspector-camera" muted playsinline></video>
            </div>

            <form method="GET" action="{{ route('verify.lookup') }}" class="inspector-manual-verify">
                <div class="form-group">
                    <label for="inspector-code">Enter permit QR code or verification link</label>
                    <input type="text" id="inspector-code" name="code" autocomplete="off" placeholder="Paste the code or QR verification link">
                </div>
                <button type="submit" class="btn btn-mint">Verify Permit</button>
            </form>
            <p class="muted inspector-scan-note">The verification result is created by the same system that applicants and the public use. Its outcome appears below in Recent Scans.</p>
            <p class="muted inspector-upload-note">Uploaded images are read in your browser to decode the QR and are not saved to the server.</p>
        </section>

        <section class="card inspector-monitor-card">
            <div class="card-head">
                <h2 class="section">Permit monitoring</h2>
                <a href="{{ route('inspector.permits.index') }}" class="btn btn-sm btn-secondary">Open permit list</a>
            </div>
            <p>Check current validity, business details, and previous QR verification activity for any issued permit.</p>
            <ul class="inspector-monitor-list">
                <li><span class="badge badge-green">Active</span> Valid permit with an unexpired date.</li>
                <li><span class="badge badge-yellow">Expired</span> Permit is past its validity date.</li>
                <li><span class="badge badge-red">Revoked</span> Permit was withdrawn and is no longer valid.</li>
            </ul>
            <p class="muted mb-0">Permit records and QR outcomes come from the BPLO issuance and public verification flow.</p>
        </section>
    </div>

    <div class="card">
        <div class="card-head">
            <h2 class="section">Recent QR scans</h2>
            <span class="muted">{{ $counts['scans_today'] }} today</span>
        </div>
        @if ($recentScans->isEmpty())
            <x-empty-state icon="🔍" title="No scans yet"
                message="Each permit QR verification will be recorded here." />
        @else
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr><th>Time</th><th>Permit No.</th><th>Business</th><th>Result</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($recentScans as $scan)
                            <tr>
                                <td>{{ $scan->created_at?->format('M d, Y g:i A') }}</td>
                                <td>{{ $scan->permit?->permit_number ?? '—' }}</td>
                                <td>{{ $scan->permit?->business_name ?? '—' }}</td>
                                <td>
                                    @php
                                        $badge = match ($scan->result) {
                                            'VALID' => 'badge-green',
                                            'EXPIRED' => 'badge-yellow',
                                            'REVOKED', 'NOT_FOUND' => 'badge-red',
                                            default => 'badge-gray',
                                        };
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $scan->result }}</span>
                                </td>
                                <td>
                                    @if ($scan->permit)
                                        <a href="{{ route('inspector.permits.show', $scan->permit) }}" class="btn btn-sm btn-secondary">View permit</a>
                                    @else
                                        <span class="muted">No permit record</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection
