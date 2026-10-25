{{-- Progress --}}
<div class="modal fade" id="progressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content text-center p-4">
            <div class="spinner-border text-primary mx-auto mb-3" role="status"></div>
            <h6 class="mb-1" id="progressTitle">ব্যাকআপ হচ্ছে</h6>
            <small class="text-muted">বন্ধ করবেন না</small>
        </div>
    </div>
</div>

{{-- Preview, compare, verify --}}
<div class="modal fade" id="infoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h6 class="modal-title mb-0" id="infoTitle"></h6>
                    <small class="text-muted font-monospace" id="infoFile"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ"></button>
            </div>
            <div class="modal-body" id="infoBody"></div>
            <div class="modal-footer d-none" id="infoFooter"></div>
        </div>
    </div>
</div>

{{-- Email --}}
<div class="modal fade" id="emailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title mb-0">ইমেইলে পাঠান</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ"></button>
            </div>
            <div class="modal-body">
                <div class="font-monospace small mb-3 text-break" id="emailFile"></div>
                <label class="form-label small" for="emailTo">প্রাপক</label>
                <input type="email" id="emailTo" class="form-control" placeholder="name@example.com">
                <small class="text-muted">সর্বোচ্চ {{ $mailMaxMb }} MB পর্যন্ত সংযুক্ত হয়।</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn btn-primary" id="emailSend">পাঠান</button>
            </div>
        </div>
    </div>
</div>

{{-- Restore --}}
<div class="modal fade" id="restoreModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="restoreForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title mb-0 text-danger">রিস্টোর</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">লাইভ ডাটাবেজ (ও মিডিয়া) এই ফাইল থেকে বদলে যাবে:</p>
                    <div class="font-monospace fw-semibold text-break mb-3" id="restoreFile"></div>
                    <small class="text-muted"><i class="fa-solid fa-shield-halved me-1 text-success"></i>শুরুর আগে বর্তমান ডাটাবেজের সেফটি কপি নেওয়া হয়।</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-danger">রিস্টোর করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Pick tables --}}
<div class="modal fade" id="selectiveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h6 class="modal-title mb-0">টেবিল বাছাই</h6>
                    <small class="text-muted font-monospace" id="selectiveFile"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="selAll">সব</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="selNone">কোনোটি না</button>
                    <span class="badge text-bg-light border" id="selCount">০ নির্বাচিত</span>
                    <input type="search" id="selSearch" class="form-control form-control-sm ms-auto" style="max-width:200px" placeholder="খুঁজুন">
                </div>
                <div class="bkp-picklist" id="pickList">
                    @foreach($db['tables'] as $t)
                        <label class="bkp-pick" data-name="{{ strtolower($t['name']) }}">
                            <input type="checkbox" class="form-check-input pick-check" value="{{ $t['name'] }}">
                            <span class="text-truncate">{{ $t['name'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted me-auto">শুধু বাছাই করা টেবিল বদলাবে</small>
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn btn-danger" id="selectiveRun">রিস্টোর করুন</button>
            </div>
        </div>
    </div>
</div>

{{-- Settings --}}
<div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.backup.settings') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title mb-0">সেটিংস</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="বন্ধ"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav bkp-tabs mb-3" role="tablist">
                        <li class="nav-item"><button type="button" class="nav-link active" data-bs-toggle="pill" data-bs-target="#setAuto">অটো</button></li>
                        <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#setTelegram">টেলিগ্রাম</button></li>
                        <li class="nav-item"><button type="button" class="nav-link" data-bs-toggle="pill" data-bs-target="#setOffsite">অফসাইট</button></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="setAuto">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="autoOn" name="auto_backup_enabled" value="1" @checked($settings['auto_backup_enabled'])>
                                <label class="form-check-label" for="autoOn">অটো-ব্যাকআপ চালু</label>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small" for="freq">কত দিন পরপর</label>
                                    <select name="backup_frequency" id="freq" class="form-select">
                                        <option value="daily" @selected($settings['backup_frequency'] === 'daily')>প্রতিদিন</option>
                                        <option value="weekly" @selected($settings['backup_frequency'] === 'weekly')>প্রতি সপ্তাহে ({{ ['রবি', 'সোম', 'মঙ্গল', 'বুধ', 'বৃহস্পতি', 'শুক্র', 'শনি'][(int) config('backup.weekly_day', 5)] }}বার)</option>
                                        <option value="monthly" @selected($settings['backup_frequency'] === 'monthly')>প্রতি মাসে (১ তারিখ)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small" for="atTime">সময়</label>
                                    <input type="time" name="backup_time" id="atTime" class="form-control" value="{{ $settings['backup_time'] }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small" for="mode">ধরন</label>
                                    <select name="backup_mode" id="mode" class="form-select">
                                        <option value="db_only" @selected($settings['backup_mode'] === 'db_only')>ডাটাবেজ</option>
                                        <option value="data_media" @selected($settings['backup_mode'] === 'data_media')>ডাটাবেজ + মিডিয়া</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small" for="keep">কতটি রাখবেন</label>
                                    <input type="number" name="retention_days" id="keep" class="form-control" min="1" max="{{ $maxRetention }}" value="{{ $settings['retention_days'] }}" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small" for="bkEmail">নোটিফিকেশন ইমেইল</label>
                                    <input type="email" name="backup_email" id="bkEmail" class="form-control" value="{{ $settings['backup_email'] }}" placeholder="খালি রাখলে ইমেইল যাবে না">
                                </div>
                            </div>
                            <small class="text-muted d-block mt-3">সার্ভারের ক্রনে <code>* * * * * php artisan schedule:run</code> থাকলে অটো-ব্যাকআপ চলে।</small>
                        </div>

                        <div class="tab-pane fade" id="setTelegram">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="tgOn" name="telegram_alerts_enabled" value="1" @checked($settings['telegram_alerts_enabled'])>
                                <label class="form-check-label" for="tgOn">ব্যাকআপ হলে টেলিগ্রামে জানান</label>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-label small" for="tgToken">বট টোকেন</label>
                                    <input type="password" name="telegram_bot_token" id="tgToken" class="form-control font-monospace" autocomplete="new-password" placeholder="{{ $hasToken ? '•••••••• (সংরক্ষিত)' : '123456789:ABC…' }}">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small" for="tgChat">চ্যাট আইডি</label>
                                    <input type="text" name="telegram_chat_id" id="tgChat" class="form-control font-monospace" value="{{ $settings['telegram_chat_id'] }}" placeholder="-100123… বা @channel">
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-3" id="tgTest"><i class="fa-solid fa-paper-plane me-1"></i>টেস্ট</button>
                        </div>

                        <div class="tab-pane fade" id="setOffsite">
                            @if(count($disks))
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label small" for="offDisk">ডিস্ক</label>
                                        <select name="offsite_disk" id="offDisk" class="form-select">
                                            <option value="none" @selected($settings['offsite_disk'] === 'none')>বন্ধ</option>
                                            @foreach($disks as $d)
                                                <option value="{{ $d }}" @selected($settings['offsite_disk'] === $d)>{{ $d }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-7">
                                        <label class="form-label small" for="offPath">ফোল্ডার</label>
                                        <input type="text" name="offsite_path" id="offPath" class="form-control font-monospace" value="{{ $settings['offsite_path'] }}">
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-3">প্রতিটি নতুন ব্যাকআপ এই ডিস্কেও কপি হয়।</small>
                            @else
                                <input type="hidden" name="offsite_disk" value="none">
                                <input type="hidden" name="offsite_path" value="{{ $settings['offsite_path'] }}">
                                <p class="text-muted mb-0">কোনো রিমোট ডিস্ক সেট করা নেই। <code>config/filesystems.php</code>-এ S3/FTP/SFTP ডিস্ক যোগ করলে এখানে দেখা যাবে।</p>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary">সংরক্ষণ</button>
                </div>
            </form>
        </div>
    </div>
</div>
