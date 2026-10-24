{{--
    Scholarship application preview (admin). English, one-word section titles, same order as the printed form.
    Needs: $reg (EventRegistration), $campaign, optional $photoSrc.
--}}
@php
    $d = $reg->form_data ?? [];
    $v = fn ($k) => trim((string) (is_array($d[$k] ?? null) ? json_encode($d[$k]) : ($d[$k] ?? '')));
    $geo = \Illuminate\Support\Facades\Cache::rememberForever('geo_en_map_' . @filemtime(public_path('js/bd-geo-en.js')), function () {
        $raw = @file_get_contents(public_path('js/bd-geo-en.js')) ?: '';
        $json = trim(preg_replace('/^.*?window\.BD_GEO_EN\s*=\s*/s', '', $raw), " \n\r\t;");
        return json_decode($json, true) ?: [];
    });
    $en = fn ($x) => $geo[$x] ?? $x;
    $money = fn ($x) => $x !== '' ? number_format((float) $x) . ' Tk' : '';
    $statusClass = in_array($reg->status, ['confirmed', 'selected', 'approved'], true) ? 'bg-success' : ($reg->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark');
    $prev = collect($d['prev_scholarships'] ?? [])->filter(fn ($r) => is_array($r) && (trim($r['year'] ?? '') !== '' || trim($r['amount'] ?? '') !== ''))->values();
    $row = function (string $label, $value) {
        $value = trim((string) $value);
        return '<tr><th class="text-muted fw-semibold" style="width:42%">' . e($label) . '</th><td>' . ($value === '' ? '<span class="text-muted">—</span>' : e($value)) . '</td></tr>';
    };
    $sections = [
        'Applicant' => [
            ['Name', $reg->name], ['Mobile', $reg->phone], ['Email', $v('email') ?: $reg->email], ['Birth', $v('birth_date')],
            ['Gender', $v('gender')], ['Religion', $v('religion')], ['NID', $v('national_id')], ['Marital', $v('marital_status')],
            ['Disability', trim($v('disability') . ' ' . $v('disability_type') . ' ' . $v('disability_details'))],
        ],
        'Family' => [
            ['Father', trim($v('father_name') . ' (' . $v('father_status') . ')', ' ()')], ['Father job', $v('father_occupation')], ['Father income', $money($v('father_income'))], ['Father mobile', $v('father_phone')],
            ['Mother', trim($v('mother_name') . ' (' . $v('mother_status') . ')', ' ()')], ['Mother job', $v('mother_occupation')], ['Mother income', $money($v('mother_income'))], ['Mother mobile', $v('mother_phone')],
            ['Guardian', trim($v('guardian_name') . ' ' . $v('guardian_relation'))], ['Guardian mobile', $v('guardian_phone')], ['Guardian income', $money($v('guardian_income'))],
            ['Family income', $money($v('annual_income'))], ['Members', $v('family_count') . ($v('earning_count') !== '' ? ' / ' . $v('earning_count') . ' earning' : '')], ['Orphan', $v('orphan_status')],
        ],
        'Academic' => [
            ['Degree', $v('scholarship_degree')], ['Institute', $v('college_name') ?: $reg->institution_or_org], ['Group', $v('group')], ['Subject', $v('assigned_subject')],
            ['Roll', $v('admission_roll')], ['Merit', $v('merit_position')], ['Year', $v('current_year')], ['Admission fee', $money($v('admission_fee'))],
            ['Monthly fee', $money($v('monthly_fee'))], ['Hostel', $money($v('hostel_cost'))],
        ],
        'Address' => [
            ['Permanent', $v('permanent_address') ?: $reg->address], ['District', $en($v('permanent_district') ?: (string) $reg->district)],
            ['Present', $v('present_address') ?: 'Same as permanent'],
        ],
        'Reading' => [['Books read', $v('books_read_count')], ['Habit', $v('reading_habit')], ['Book names', $v('books_read_names')]],
        'Payment' => [
            ['Method', $v('pay_method')], ['Owner', $v('pay_account_owner')], ['Holder', $v('pay_account_name')], ['Account', $v('pay_account_no')],
            ['Bank', trim($v('pay_bank_name') . ' ' . $v('pay_bank_branch'))],
        ],
    ];
@endphp

<div class="modal fade" id="viewDetailsModal{{ $reg->id }}" tabindex="-1" aria-hidden="true" aria-labelledby="schPreviewTitle{{ $reg->id }}">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable text-start">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #fff;">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    @if(!empty($photoSrc))
                        <img src="{{ $photoSrc }}" alt="" style="width: 56px; height: 68px; border-radius: 8px; object-fit: cover; border: 2px solid #fff;">
                    @endif
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-white" id="schPreviewTitle{{ $reg->id }}">{{ $reg->name }}</h5>
                            <span class="badge {{ $statusClass }}">{{ ucfirst($reg->status) }}</span>
                            @if($reg->status === 'selected' || !empty($d['is_scholarship_awarded']))<span class="badge bg-info text-dark">Awarded</span>@endif
                        </div>
                        <small class="text-light opacity-75 font-monospace">No. {{ $reg->registration_number }} &bull; {{ $reg->created_at->format('d M Y, h:i A') }}@if($v('viva_total') !== '') &bull; Viva {{ $v('viva_total') }}/50 @endif</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <a href="{{ route('admin.event-campaigns.registrations.pdf', $reg->id) }}" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold">PDF</a>
                    <a href="{{ route('admin.event-campaigns.registrations.print', $reg->id) }}" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3 fw-semibold">Print</a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-3 p-md-4 bg-light">
                <div class="row g-3">
                    @foreach($sections as $title => $rows)
                        <div class="col-12 col-lg-6">
                            <div class="bg-white rounded-3 border h-100">
                                <h6 class="fw-bold text-dark border-bottom px-3 py-2 mb-0" style="font-size: 13.5px;">{{ $title }}</h6>
                                <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
                                    @foreach($rows as [$label, $value]){!! $row($label, $value) !!}@endforeach
                                </table>
                            </div>
                        </div>
                    @endforeach

                    <div class="col-12 col-lg-6">
                        <div class="bg-white rounded-3 border h-100">
                            <h6 class="fw-bold text-dark border-bottom px-3 py-2 mb-0" style="font-size: 13.5px;">Education</h6>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0" style="font-size: 12.5px;">
                                    <thead class="table-light"><tr><th>Level</th><th>Roll</th><th>Board</th><th>Institute</th><th>Year</th><th>GPA</th></tr></thead>
                                    @foreach(['SSC' => ['ssc', 'ssc_gpa'], 'HSC' => ['hsc', 'hsc_gpa'], 'Honours' => ['grad', 'grad_cgpa'], 'Masters' => ['other', 'other_cgpa']] as $lvl => [$p, $g])
                                        @if($v($p . '_roll') !== '' || $v($p . '_institute') !== '' || $v($g) !== '')
                                            <tr><td class="fw-semibold">{{ $lvl }}</td><td>{{ $v($p . '_roll') }}</td><td>{{ $v($p . '_board') }}</td><td>{{ $v($p . '_institute') }}</td><td>{{ $v($p . '_year') }}</td><td class="fw-bold">{{ $v($g) }}</td></tr>
                                        @endif
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="bg-white rounded-3 border h-100">
                            <h6 class="fw-bold text-dark border-bottom px-3 py-2 mb-0" style="font-size: 13.5px;">Scholarships</h6>
                            <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
                                {!! $row('Joyee before', $v('prev_joyee') ?: ($prev->isNotEmpty() ? 'Yes' : '')) !!}
                                @foreach($prev as $r){!! $row('Year ' . ($r['year'] ?? ''), trim(($r['times'] ?? '') . ' x ' . ($r['amount'] ?? '') . ' Tk', ' x')) !!}@endforeach
                                {!! $row('Other', trim($v('other_scholarship') . ' ' . $v('other_scholarship_details'))) !!}
                            </table>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="bg-white rounded-3 border">
                            <h6 class="fw-bold text-dark border-bottom px-3 py-2 mb-0" style="font-size: 13.5px;">Statement</h6>
                            <p class="px-3 py-2 mb-0" style="font-size: 13.5px; white-space: pre-line;">{{ $v('scholarship_reason') ?: '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#editRegModal{{ $reg->id }}">Edit</button>
            </div>
        </div>
    </div>
</div>
