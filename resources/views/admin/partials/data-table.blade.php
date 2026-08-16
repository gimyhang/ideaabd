{{--
    Generic admin list table.

    Rows may be Eloquent models or plain stdClass rows from the query builder, so
    every field is read with data_get() and missing columns render as "—".

    @param \Illuminate\Contracts\Pagination\Paginator $rows
    @param array  $columns  [ ['key'=>'title','label'=>'শিরোনাম','type'=>'strong'] ]
                            types: text | strong | muted | money | date | bool | pill | index
                            'pill' expects 'map' => ['paid' => ['পরিশোধিত','ok']]
    @param string $empty       message shown when there are no rows
    @param string $emptyIcon   font-awesome name, defaults to inbox
    @param string $emptyHint   optional secondary line
--}}
@php
    $cols = $columns;
    $last = count($cols) - 1;
@endphp

<div class="adm-card">
    @if ($rows->isEmpty())
        <div class="empty-state">
            <i class="fas fa-{{ $emptyIcon ?? 'inbox' }}"></i>
            <div>{{ $empty ?? 'কোনো তথ্য পাওয়া যায়নি' }}</div>
            @isset($emptyHint)
                <div class="small mt-1">{{ $emptyHint }}</div>
            @endisset
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle">
                <thead>
                    <tr>
                        @foreach ($cols as $i => $col)
                            <th class="{{ $i === 0 ? 'ps-3' : '' }} {{ empty($contentType) && $i === $last ? 'pe-3' : '' }} {{ $col['align'] ?? '' }}">
                                {{ $col['label'] }}
                            </th>
                        @endforeach
                        @if(!empty($contentType))
                            <th class="text-end pe-3" style="min-width: 110px;">অ্যাকশন</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $n => $row)
                        <tr>
                            @foreach ($cols as $i => $col)
                                @php
                                    $value = data_get($row, $col['key'] ?? '');
                                    $blank = $value === null || $value === '';
                                @endphp
                                <td class="{{ $i === 0 ? 'ps-3' : '' }} {{ empty($contentType) && $i === $last ? 'pe-3' : '' }} {{ $col['align'] ?? '' }}">
                                    @switch($col['type'] ?? 'text')
                                        @case('index')
                                            <span class="text-muted small">@bn($rows->firstItem() + $n)</span>
                                            @break

                                        @case('strong')
                                            @if ($blank)
                                                <span class="text-muted">—</span>
                                            @else
                                                <span class="fw-semibold">{{ $value }}</span>
                                                @isset($col['sub'])
                                                    <div class="text-muted" style="font-size:.75rem">{{ data_get($row, $col['sub']) }}</div>
                                                @endisset
                                            @endif
                                            @break

                                        @case('muted')
                                            <span class="text-muted small">{{ $blank ? '—' : $value }}</span>
                                            @break

                                        @case('money')
                                            @taka($value ?? 0)
                                            @break

                                        @case('date')
                                            <span class="text-muted small">@bnDate($value)</span>
                                            @break

                                        @case('bool')
                                            <span class="pill {{ $value ? 'pill--ok' : 'pill--muted' }}">
                                                {{ $value ? ($col['on'] ?? 'সক্রিয়') : ($col['off'] ?? 'নিষ্ক্রিয়') }}
                                            </span>
                                            @break

                                        @case('pill')
                                            @php [$text, $tone] = $col['map'][$value] ?? [$value ?: '—', 'muted']; @endphp
                                            <span class="pill pill--{{ $tone }}">{{ $text }}</span>
                                            @break

                                        @default
                                            {{ $blank ? '—' : $value }}
                                    @endswitch
                                </td>
                            @endforeach

                            @if(!empty($contentType))
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1 align-items-center">
                                        <a href="{{ route('admin.content.edit', ['type' => $contentType, 'id' => data_get($row, 'id')]) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="সম্পাদনা করুন"
                                           style="padding: 0.2rem 0.5rem;">
                                            <i class="fas fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('admin.content.destroy', ['type' => $contentType, 'id' => data_get($row, 'id')]) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('আপনি কি নিশ্চিত যে এটি মুছে ফেলতে চান?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="মুছে ফেলুন"
                                                    style="padding: 0.2rem 0.5rem;">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($rows->hasPages())
            <div class="adm-card__foot d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="text-muted small">
                    মোট @bn($rows->total())টির মধ্যে @bn($rows->firstItem())–@bn($rows->lastItem()) দেখানো হচ্ছে
                </span>
                {{ $rows->onEachSide(1)->links() }}
            </div>
        @else
            <div class="adm-card__foot text-muted small">মোট @bn($rows->total())টি</div>
        @endif
    @endif
</div>
