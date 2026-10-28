{{-- Admin-defined extra fields (Event campaign → form customizer). Renders nothing when none are defined. --}}
@php
    $dynFields = collect((array) ($campaign->custom_fields ?? []))
        ->filter(fn ($f) => !empty($f['name']) && preg_match('/^[A-Za-z0-9_]{1,60}$/', (string) $f['name'])
            && !in_array($f['name'], ['name', 'phone', 'email', 'password', 'address', 'district', 'thana', 'amount_paid', 'transaction_id', 'payment_method', 'student_photo', 'optimized_photo_data'], true))
        ->reject(fn ($f) => $campaign->isScholarship()
            && in_array($f['name'], \App\Http\Controllers\PublicEventRegistrationController::SCHOLARSHIP_BUILTIN_FIELDS, true))
        ->values();
    $dynSaved = (array) (($existingRegistration ?? null)?->form_data ?? []);
    $dynTitle = $campaign->form_settings['custom_section_title'] ?? 'অতিরিক্ত তথ্য';
@endphp

@if ($dynFields->isNotEmpty())
    <table class="clean-grid-table ev-dynamic" id="evDynamicFields">
        <tr>
            <td class="grid-section-header" colspan="4">{{ $dynTitle }}</td>
        </tr>
        @foreach ($dynFields as $field)
            @php
                $key = $field['name'];
                $label = $field['label'] ?? ucfirst(str_replace('_', ' ', $key));
                $type = $field['type'] ?? 'text';
                $required = !empty($field['required']);
                $options = array_values(array_filter(array_map('trim', explode(',', (string) ($field['options'] ?? ''))), fn ($o) => $o !== ''));
                $value = old($key, $dynSaved[$key] ?? '');
                $inputId = 'ev_' . $key;
            @endphp
            <tr class="ev-dyn-row {{ $errors->has($key) ? 'has-error' : '' }}">
                <td class="c-label"><label for="{{ $inputId }}">{{ $label }}@if ($required) <span class="text-danger">*</span>@endif</label></td>
                <td class="c-val" colspan="3">
                    @if ($type === 'select')
                        <select id="{{ $inputId }}" name="{{ $key }}" class="c-select" @required($required)>
                            <option value="">{{ $label }}</option>
                            @foreach ($options as $opt)
                                <option value="{{ $opt }}" @selected((string) $value === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    @elseif ($type === 'textarea')
                        <textarea id="{{ $inputId }}" name="{{ $key }}" rows="3" maxlength="2000" class="c-input" @required($required)>{{ $value }}</textarea>
                    @else
                        <input id="{{ $inputId }}"
                               type="{{ in_array($type, ['number', 'email', 'tel', 'date'], true) ? $type : 'text' }}"
                               name="{{ $key }}"
                               class="c-input"
                               value="{{ $value }}"
                               @if ($type === 'number') inputmode="decimal" step="any" @endif
                               @if ($type === 'email') inputmode="email" autocapitalize="off" @endif
                               maxlength="500"
                               autocomplete="off"
                               @required($required)>
                    @endif
                    @error($key)<div class="ev-field-error">{{ $message }}</div>@enderror
                </td>
            </tr>
        @endforeach
    </table>
@endif
