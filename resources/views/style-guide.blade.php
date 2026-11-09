@extends('layouts.app')

@section('title', 'Style Guide - Techcare')

@push('styles')
<style>
    .sg-swatch { border: 1px solid var(--tc-border); border-radius: var(--tc-radius); overflow: hidden;
                 background: var(--tc-surface); cursor: pointer; height: 100%; transition: transform .12s; }
    .sg-swatch:hover { transform: translateY(-2px); }
    .sg-chip { height: 90px; display: flex; align-items: flex-end; padding: .5rem .75rem; font-weight: 600; }
    .sg-meta { padding: .6rem .75rem; font-size: .85rem; }
    .sg-meta code { color: var(--tc-text); background: transparent; padding: 0; }
    .sg-meta .muted { color: var(--tc-text-muted); }
    .sg-section { margin-bottom: 1.5rem; }
    .sg-demo { background: var(--tc-surface); border: 1px solid var(--tc-border); border-radius: var(--tc-radius); padding: 1.25rem; }
    .sg-rule { border-left: 4px solid; padding: .5rem .9rem; border-radius: 0 var(--tc-radius-sm) var(--tc-radius-sm) 0;
               background: var(--tc-surface); border-top: 1px solid var(--tc-border); border-right: 1px solid var(--tc-border);
               border-bottom: 1px solid var(--tc-border); }
    .sg-copied { position: fixed; bottom: 24px; right: 24px; background: var(--tc-primary); color: var(--tc-secondary);
                 padding: .5rem 1rem; border-radius: var(--tc-radius-sm); opacity: 0; transition: opacity .2s; z-index: 9999; }
    .sg-copied.show { opacity: 1; }
    .sg-ratio { font-variant-numeric: tabular-nums; }
</style>
@endpush

@section('content')
@php
    $brand = [
        ['Primary',   '--tc-primary',   '#0A0A0A', 'Black. Headings, primary buttons, navbar/sidebar in light mode.'],
        ['Secondary', '--tc-secondary', '#FFFFFF', 'White. Page/card backgrounds and text on dark surfaces.'],
        ['Accent',    '--tc-accent',    '#2563EB', 'Blue. Links, focus rings, highlights, and the one CTA per screen.'],
    ];
    $neutrals = [
        ['Gray 50',  '--tc-gray-50',  '#F9FAFB'], ['Gray 100', '--tc-gray-100', '#F3F4F6'],
        ['Gray 200', '--tc-gray-200', '#E5E7EB'], ['Gray 300', '--tc-gray-300', '#D1D5DB'],
        ['Gray 500', '--tc-gray-500', '#6B7280'], ['Gray 700', '--tc-gray-700', '#374151'],
        ['Gray 900', '--tc-gray-900', '#111827'],
    ];
    $semantic = [
        ['Success', '--tc-success', '#16A34A', 'Completed service, paid, approved'],
        ['Warning', '--tc-warning', '#D97706', 'Pending, waiting for parts, reminders'],
        ['Danger',  '--tc-danger',  '#DC2626', 'Errors, cancelled, destructive actions'],
        ['Info',    '--tc-info',    '#2563EB', 'Same as Accent. Neutral information'],
    ];
@endphp

<div class="app-page-title">
    <div class="page-title-wrapper">
        <div class="page-title-heading">
            <div class="page-title-icon"><i class="pe-7s-paint-bucket icon-gradient bg-mean-fruit"></i></div>
            <div>
                Style Guide
                <div class="page-title-subheading">
                    Design rules for developers. Click any swatch to copy its CSS variable.
                    Tokens live in <code>public/css/techcare-tokens.css</code>.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 1. Principles --}}
<div class="sg-section">
    <h5 class="mb-3">1. Principles</h5>
    <div class="row g-3">
        <div class="col-md-4"><div class="sg-rule" style="border-color:var(--tc-primary)">
            <strong>Black &amp; white first.</strong><br>Most of the UI should be black, white, and gray. Keep it clean.</div></div>
        <div class="col-md-4"><div class="sg-rule" style="border-color:var(--tc-accent)">
            <strong>One accent.</strong><br>Blue is for emphasis only: links, focus, and one main action per screen.</div></div>
        <div class="col-md-4"><div class="sg-rule" style="border-color:var(--tc-success)">
            <strong>Color means status.</strong><br>Green/amber/red only for service and payment status, never decoration.</div></div>
    </div>
</div>

{{-- 2. Brand colors --}}
<div class="sg-section">
    <h5 class="mb-3">2. Brand colors</h5>
    <div class="row g-3">
        @foreach ($brand as [$name, $var, $hex, $use])
            <div class="col-md-4">
                <div class="sg-swatch" data-copy="var({{ $var }})">
                    <div class="sg-chip" data-hex="{{ $hex }}"
                         style="background:var({{ $var }});color:{{ $name === 'Secondary' ? '#0a0a0a' : '#fff' }};
                                {{ $name === 'Secondary' ? 'border-bottom:1px solid var(--tc-border)' : '' }}">{{ $name }}</div>
                    <div class="sg-meta">
                        <div><code>{{ $var }}</code></div>
                        <div class="muted">{{ $hex }}</div>
                        <div class="mt-1">{{ $use }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <p class="text-muted small mt-2 mb-0">
        Dark mode: tokens flip automatically (<code>--tc-primary</code> becomes white, surfaces become dark).
        Always use the variables, never hard-code hex values, so dark mode keeps working.
    </p>
</div>

{{-- 3. Neutrals --}}
<div class="sg-section">
    <h5 class="mb-3">3. Neutral grays</h5>
    <div class="row g-3">
        @foreach ($neutrals as [$name, $var, $hex])
            <div class="col-6 col-md-3 col-xl">
                <div class="sg-swatch" data-copy="var({{ $var }})">
                    <div class="sg-chip" style="background:var({{ $var }})"></div>
                    <div class="sg-meta"><strong>{{ $name }}</strong><div><code>{{ $var }}</code></div><div class="muted">{{ $hex }}</div></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- 4. Semantic --}}
<div class="sg-section">
    <h5 class="mb-3">4. Status colors</h5>
    <div class="row g-3">
        @foreach ($semantic as [$name, $var, $hex, $use])
            <div class="col-md-6 col-xl-3">
                <div class="sg-swatch" data-copy="var({{ $var }})">
                    <div class="sg-chip" style="background:var({{ $var }});color:#fff">{{ $name }}</div>
                    <div class="sg-meta"><div><code>{{ $var }}</code></div><div class="muted">{{ $hex }}</div><div class="mt-1">{{ $use }}</div></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- 5. Contrast --}}
<div class="sg-section">
    <h5 class="mb-3">5. Accessible pairings</h5>
    <div class="sg-demo">
        <p class="text-muted small">Calculated live from the actual swatch colors. Body text must reach <strong>4.5:1</strong> (WCAG AA).</p>
        <div class="table-responsive">
            <table class="table mb-0" id="contrast-table">
                <thead><tr><th>Text</th><th>On background</th><th>Preview</th><th>Ratio</th><th>Result</th></tr></thead>
                <tbody>
                    @foreach ([
                        ['#FFFFFF', '#0A0A0A', 'White on Primary'],
                        ['#0A0A0A', '#FFFFFF', 'Primary on White'],
                        ['#FFFFFF', '#2563EB', 'White on Accent'],
                        ['#2563EB', '#FFFFFF', 'Accent on White (links)'],
                        ['#6B7280', '#FFFFFF', 'Muted gray on White'],
                        ['#FFFFFF', '#16A34A', 'White on Success'],
                        ['#FFFFFF', '#D97706', 'White on Warning'],
                        ['#FFFFFF', '#DC2626', 'White on Danger'],
                    ] as [$fg, $bg, $label])
                        <tr data-fg="{{ $fg }}" data-bg="{{ $bg }}">
                            <td>{{ $fg }}</td><td>{{ $bg }}</td>
                            <td><span class="px-2 py-1 rounded" style="background:{{ $bg }};color:{{ $fg }};border:1px solid var(--tc-border)">{{ $label }}</span></td>
                            <td class="sg-ratio"></td><td class="sg-result"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- 6. Buttons --}}
<div class="sg-section">
    <h5 class="mb-3">6. Buttons</h5>
    <div class="sg-demo">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button class="tc-btn tc-btn-primary">Primary</button>
            <button class="tc-btn tc-btn-accent">Accent (main CTA)</button>
            <button class="tc-btn tc-btn-outline">Outline</button>
            <button class="tc-btn tc-btn-danger">Danger</button>
        </div>
        <ul class="mb-0 small">
            <li><strong>Max one Accent button per screen</strong> (e.g. "Book Service"). Everything else uses Primary or Outline.</li>
            <li>Danger is only for destructive actions (cancel booking, delete). Always confirm first.</li>
            <li>Classes: <code>tc-btn tc-btn-primary | tc-btn-accent | tc-btn-outline | tc-btn-danger</code></li>
        </ul>
    </div>
</div>

{{-- 7. Badges --}}
<div class="sg-section">
    <h5 class="mb-3">7. Status badges</h5>
    <div class="sg-demo">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="tc-badge tc-badge-warning">Pending</span>
            <span class="tc-badge tc-badge-accent">Scheduled</span>
            <span class="tc-badge tc-badge-accent">In Service</span>
            <span class="tc-badge tc-badge-success">Completed</span>
            <span class="tc-badge tc-badge-danger">Cancelled</span>
            <span class="tc-badge tc-badge-dark">Admin</span>
        </div>
        <p class="small mb-0">Suggested booking status mapping: Pending → Warning, Scheduled / In Service → Accent,
            Completed → Success, Cancelled → Danger. Role badges use <code>tc-badge-dark</code>.</p>
    </div>
</div>

{{-- 8. Forms / cards --}}
<div class="sg-section">
    <h5 class="mb-3">8. Forms &amp; cards</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <div class="sg-demo">
                <label class="form-label">Laptop model</label>
                <input class="form-control mb-3" placeholder="e.g. ASUS ROG Strix G15">
                <label class="form-label">Problem</label>
                <input class="form-control is-invalid mb-1" value="">
                <div class="invalid-feedback d-block mb-3">This field is required.</div>
                <button class="tc-btn tc-btn-accent">Book Service</button>
            </div>
        </div>
        <div class="col-md-6">
            <div class="sg-demo">
                <ul class="small mb-0">
                    <li>Labels above inputs, sentence case ("Laptop model", not "LAPTOP MODEL").</li>
                    <li>Errors appear under the field, in Danger color, in plain English.</li>
                    <li>Cards: surface background, 1px <code>--tc-border</code>, radius <code>--tc-radius</code> (10px).</li>
                    <li>Spacing: use multiples of 4px (Bootstrap <code>m-1 … m-5</code>).</li>
                    <li>Use the template's <code>pe-7s-*</code> icons for menus and <code>fa</code> for small inline icons.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- 9. Do / Don't --}}
<div class="sg-section">
    <h5 class="mb-3">9. Do &amp; Don't</h5>
    <div class="row g-3">
        <div class="col-md-6"><div class="sg-rule" style="border-color:var(--tc-success)">
            <strong class="text-success">Do</strong>
            <ul class="mb-0 small">
                <li>Use <code>var(--tc-*)</code> variables for every color.</li>
                <li>Test new pages in both light and dark mode.</li>
                <li>Keep text contrast at 4.5:1 or higher.</li>
                <li>Use English for all UI text.</li>
            </ul></div></div>
        <div class="col-md-6"><div class="sg-rule" style="border-color:var(--tc-danger)">
            <strong class="text-danger">Don't</strong>
            <ul class="mb-0 small">
                <li>Hard-code hex values or add new colors without updating this page.</li>
                <li>Use blue for decoration or red/green for anything but status.</li>
                <li>Put more than one Accent button on a screen.</li>
                <li>Use gradient backgrounds on app pages (login page is the only exception).</li>
            </ul></div></div>
    </div>
</div>

<div class="sg-copied" id="sg-copied">Copied!</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Click swatch to copy CSS variable
    const toast = document.getElementById('sg-copied');
    document.querySelectorAll('[data-copy]').forEach(function (el) {
        el.addEventListener('click', function () {
            navigator.clipboard && navigator.clipboard.writeText(el.dataset.copy);
            toast.textContent = 'Copied ' + el.dataset.copy;
            toast.classList.add('show');
            setTimeout(function () { toast.classList.remove('show'); }, 1400);
        });
    });

    // WCAG contrast ratio
    function lum(hex) {
        const c = [1, 3, 5].map(function (i) {
            const v = parseInt(hex.substr(i, 2), 16) / 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
    }
    document.querySelectorAll('#contrast-table tbody tr').forEach(function (tr) {
        const a = lum(tr.dataset.fg), b = lum(tr.dataset.bg);
        const ratio = (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05);
        tr.querySelector('.sg-ratio').textContent = ratio.toFixed(2) + ':1';
        const ok = ratio >= 4.5;
        const cell = tr.querySelector('.sg-result');
        cell.innerHTML = ok
            ? '<span class="tc-badge tc-badge-success">AA pass</span>'
            : (ratio >= 3 ? '<span class="tc-badge tc-badge-warning">Large text only</span>'
                          : '<span class="tc-badge tc-badge-danger">Fail</span>');
    });
});
</script>
@endpush
