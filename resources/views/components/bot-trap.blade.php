{{-- Robot trap: people never see or fill the first field and need a moment before submitting; scripts fail on both. --}}
<div aria-hidden="true" style="position: absolute; left: -10000px; top: auto; width: 1px; height: 1px; overflow: hidden;">
    <label>Leave this field empty <input type="text" name="website_url" tabindex="-1" autocomplete="off" value=""></label>
</div>
<input type="hidden" name="_ts" value="{{ \Illuminate\Support\Facades\Crypt::encryptString((string) time()) }}">
