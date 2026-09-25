/**
 * IdeaBD Event & Campaign Modern Dynamic JavaScript Engine
 */

// 1. Bangladesh 64 Districts and Upazilas dataset
const BD_DISTRICTS_DATA = {
    "Dhaka": ["Dhamrai", "Dohar", "Keraniganj", "Nawabganj", "Savar", "Tejgaon", "Mirpur", "Mohammadpur", "Gulshan", "Dhanmondi", "Uttara", "Motijheel", "Jatrabari", "Badda"],
    "Faridpur": ["Alfadanga", "Bhangga", "Boalmari", "Charbhadrasan", "Faridpur Sadar", "Madhukhali", "Nagarkanda", "Sadarpur", "Saltha"],
    "Gazipur": ["Gazipur Sadar", "Kaliakair", "Kaliganj", "Kapasia", "Sreepur", "Tongi"],
    "Gopalganj": ["Gopalganj Sadar", "Kashiani", "Kotalipara", "Muksudpur", "Tungipara"],
    "Kishoreganj": ["Austagram", "Bajitpur", "Bhairab", "Hossainpur", "Itna", "Karimganj", "Katiadi", "Kishoreganj Sadar", "Kuliarchar", "Mithamain", "Nikli", "Pakundia", "Tarail"],
    "Madaripur": ["Barhamganj", "Kalkini", "Madaripur Sadar", "Rajoir", "Shibchar"],
    "Manikganj": ["Daulatpur", "Ghior", "Harirampur", "Manikganj Sadar", "Saturia", "Shivalaya", "Singair"],
    "Munshiganj": ["Gazaria", "Lohajang", "Munshiganj Sadar", "Sirajdikhan", "Sreenagar", "Tongibari"],
    "Narayanganj": ["Araihazar", "Bandar", "Narayanganj Sadar", "Rupganj", "Sonargaon", "Fatullah", "Siddhirganj"],
    "Narsingdi": ["Belabo", "Monohardi", "Narsingdi Sadar", "Palash", "Raipura", "Shibpur"],
    "Rajbari": ["Baliakandi", "Goalandaghat", "Pangsha", "Rajbari Sadar", "Kalukhali"],
    "Shariatpur": ["Bhedarganj", "Damudya", "Gosairhat", "Naria", "Shariatpur Sadar", "Zanjira"],
    "Tangail": ["Basail", "Bhuapur", "Delduar", "Ghatail", "Gopalpur", "Kalihati", "Madhupur", "Mirzapur", "Nagarpur", "Sakhipur", "Tangail Sadar", "Dhanbari"],
    "Chattogram": ["Anwara", "Banshkhali", "Boalkhali", "Chandanaish", "Fatikchhari", "Hathazari", "Karnaphuli", "Lohagara", "Mirsharai", "Patiya", "Rangunia", "Raozan", "Sandwip", "Satkania", "Sitakunda", "Kotwali", "Panchlaish", "Pahartali", "Double Mooring"],
    "Cox's Bazar": ["Chakaria", "Cox's Bazar Sadar", "Kutubdia", "Maheshkhali", "Ramu", "Teknaf", "Ukhia", "Pekua"],
    "Cumilla": ["Barura", "Brahmanpara", "Burichang", "Chandina", "Chauddagram", "Cumilla Adarsha Sadar", "Cumilla Sadar Dakshin", "Daudkandi", "Debidwar", "Homna", "Laksam", "Muradnagar", "Nangalkot", "Meghna", "Monohargonj", "Titas", "Lalmai"],
    "Feni": ["Chhagalnaiya", "Daganbhuiyan", "Feni Sadar", "Parshuram", "Sonagazi", "Fulgazi"],
    "Khagrachhari": ["Dighinala", "Khagrachhari Sadar", "Lakshmichhari", "Mahalchhari", "Manikchhari", "Matiranga", "Panchhari", "Ramgarh", "Guimara"],
    "Lakshmipur": ["Kamalnagar", "Lakshmipur Sadar", "Raipur", "Ramganj", "Ramgati"],
    "Noakhali": ["Begumganj", "Chatkhil", "Companiganj", "Hatiya", "Kabirhat", "Noakhali Sadar", "Senbagh", "Sonaimuri", "Subarnachar"],
    "Rangamati": ["Baghaichhari", "Barkal", "Belaichhari", "Juraichhari", "Kaptai", "Kawkhali", "Langadu", "Naniarchar", "Rajasthali", "Rangamati Sadar"],
    "Brahmanbaria": ["Akhaura", "Ashuganj", "Bacchus", "Brahmanbaria Sadar", "Kasba", "Nabinagar", "Nasirnagar", "Sarail", "Bijoynagar"],
    "Chandpur": ["Chandpur Sadar", "Faridganj", "Haimchar", "Haziganj", "Kachua", "Matlab Dakshin", "Matlab Uttar", "Shahrasti"],
    "Bandarban": ["Ali Kadam", "Bandarban Sadar", "Lama", "Naikhongchhari", "Rowangchhari", "Ruma", "Thanchi"],
    "Sylhet": ["Balaganj", "Beanibazar", "Bishwanath", "Companiganj", "Dakshin Surma", "Fenchuganj", "Golapganj", "Gowainghat", "Jaintiapur", "Kanaighat", "Sylhet Sadar", "Zakiganj", "Osmani Nagar"],
    "Moulvibazar": ["Barlekha", "Juri", "Kamalganj", "Kulaura", "Moulvibazar Sadar", "Rajnagar", "Sreemangal"],
    "Habiganj": ["Ajmiriganj", "Bahubal", "Baniyachong", "Chunarughat", "Habiganj Sadar", "Lakhai", "Madhabpur", "Nabiganj", "Shayestaganj"],
    "Sunamganj": ["Bishwamvarpur", "Chhatak", "Derai", "Dharampasha", "Dowarabazar", "Jagannathpur", "Jamalganj", "Sullah", "Sunamganj Sadar", "Tahirpur", "South Sunamganj"],
    "Rajshahi": ["Bagha", "Bagmara", "Charghat", "Durgapur", "Godagari", "Mohanpur", "Paba", "Puthia", "Tanore", "Boalia", "Rajpara", "Motihar", "Shah Makhdum"],
    "Bogura": ["Adamdighi", "Bogura Sadar", "Dhunat", "Dhupchanchia", "Gabtali", "Kahaloo", "Nandigram", "Sariakandi", "Shajahanpur", "Sherpur", "Shibganj", "Sonatala"],
    "Joypurhat": ["Akkelpur", "Joypurhat Sadar", "Kalai", "Khetlal", "Panchbibi"],
    "Naogaon": ["Atrai", "Badalgachhi", "Dhamoirhat", "Manda", "Mohadevpur", "Naogaon Sadar", "Niamatpur", "Patnitala", "Porsha", "Raninagar", "Sapahar"],
    "Natore": ["Bagatipara", "Baraigram", "Gurudaspur", "Lalpur", "Natore Sadar", "Singra", "Naldanga"],
    "Chapainawabganj": ["Bholahat", "Chapainawabganj Sadar", "Gomastapur", "Nachole", "Shibganj"],
    "Pabna": ["Atgharia", "Bera", "Bhangura", "Chatmohar", "Faridpur", "Ishwardi", "Pabna Sadar", "Santhia", "Sujanagar"],
    "Sirajganj": ["Belkuchi", "Chauhali", "Kamarkhanda", "Kazipur", "Raiganj", "Shahjadpur", "Sirajganj Sadar", "Tarash", "Ullahpara"],
    "Rangpur": ["Badarganj", "Gangachhara", "Kaunia", "Mithapukur", "Pirgachha", "Pirganj", "Rangpur Sadar", "Taraganj"],
    "Dinajpur": ["Birampur", "Birganj", "Birol", "Bochaganj", "Chirirbandar", "Dinajpur Sadar", "Fulbari", "Ghoraghat", "Hakimpur", "Kaharole", "Khansama", "Nawabganj", "Parbatipur"],
    "Gaibandha": ["Fulchhari", "Gaibandha Sadar", "Gobindaganj", "Palashbari", "Sadullapur", "Saghata", "Sundarganj"],
    "Kurigram": ["Bhurungamari", "Char Rajibpur", "Chilmari", "Kurigram Sadar", "Nageshwari", "Phulbari", "Rajarhat", "Roumari", "Ulipur"],
    "Lalmonirhat": ["Aditmari", "Hatibandha", "Kaliganj", "Lalmonirhat Sadar", "Patgram"],
    "Nilphamari": ["Dimla", "Domar", "Jaldhaka", "Kishoreganj", "Nilphamari Sadar", "Saidpur"],
    "Panchagarh": ["Atwari", "Boda", "Debiganj", "Panchagarh Sadar", "Tetulia"],
    "Thakurgaon": ["Baliadangi", "Haripur", "Pirganj", "Ranisankail", "Thakurgaon Sadar"],
    "Khulna": ["Batiaghata", "Dacope", "Dumuria", "Dighalia", "Koyra", "Paikgachha", "Phultala", "Rupsha", "Terokhada", "Kotwali", "Sonadanga", "Khalishpur", "Daulatpur", "Khan Jahan Ali"],
    "Bagerhat": ["Bagerhat Sadar", "Chitalmari", "Fakirhat", "Kachua", "Mollahat", "Mongla", "Morrelganj", "Rampal", "Sarankhola"],
    "Chuadanga": ["Alamdanga", "Chuadanga Sadar", "Damurhuda", "Jibannagar"],
    "Jashore": ["Abhaynagar", "Bagherpara", "Chaugachha", "Jashore Sadar", "Jhikargachha", "Keshabpur", "Manirampur", "Sharsha"],
    "Jhenaidah": ["Harinakunda", "Jhenaidah Sadar", "Kaliganj", "Kotchandpur", "Maheshpur", "Shailkupa"],
    "Kushtia": ["Bheramara", "Daulatpur", "Khoksa", "Kumarkhali", "Kushtia Sadar", "Mirpur"],
    "Magura": ["Magura Sadar", "Mohammadpur", "Shalikha", "Sreepur"],
    "Meherpur": ["Gangni", "Meherpur Sadar", "Mujibnagar"],
    "Narail": ["Kalia", "Lohagara", "Narail Sadar"],
    "Satkhira": ["Assasuni", "Debhata", "Kalaroa", "Kaliganj", "Satkhira Sadar", "Shyamnagar", "Tala"],
    "Barishal": ["Agailjhara", "Babuganj", "Bakerganj", "Banaripara", "Barishal Sadar", "Gaurnadi", "Hizla", "Mehendiganj", "Muladi", "Wazirpur"],
    "Barguna": ["Amtali", "Bamna", "Barguna Sadar", "Betagi", "Patharghata", "Taltali"],
    "Bhola": ["Bhola Sadar", "Burhanuddin", "Char Fasson", "Daulatkhan", "Lalmohan", "Manpura", "Tazumuddin"],
    "Jhalokathi": ["Jhalokathi Sadar", "Kathalia", "Nalchity", "Rajapur"],
    "Patuakhali": ["Bauphal", "Dashmina", "Galachipa", "Kalapara", "Mirzaganj", "Patuakhali Sadar", "Rangabali", "Dumki"],
    "Pirojpur": ["Bhandaria", "Kawkhali", "Mathbaria", "Nazirpur", "Pirojpur Sadar", "Nesarabad", "Zianagar"],
    "Mymensingh": ["Bhaluka", "Dhobaura", "Fulbaria", "Gaffargaon", "Gauripur", "Haluaghat", "Ishwarganj", "Mymensingh Sadar", "Muktagachha", "Nandail", "Phulpur", "Trishal", "Tara Khanda"],
    "Jamalpur": ["Baksiganj", "Dewanganj", "Islampur", "Jamalpur Sadar", "Madarganj", "Melandaha", "Sarishabari"],
    "Netrokona": ["Atpara", "Barhatta", "Durgapur", "Khaliajuri", "Kalmakanda", "Kendua", "Madan", "Mohanganj", "Netrokona Sadar", "Purbadhala"],
    "Sherpur": ["Jhenaigati", "Nakla", "Nalitabari", "Sherpur Sadar", "Sreebardi"]
};

// 2. Initialize BD District & Thana Autocomplete/Chaining
function initBdGeoChaining() {
    const districtInputs = document.querySelectorAll('input[name="district"], input[name="permanent_district"], input[name="present_district"]');
    const districtList = Object.keys(BD_DISTRICTS_DATA);

    // Create Datalist for District
    let dlDist = document.getElementById('bdDistrictsDatalist');
    if (!dlDist) {
        dlDist = document.createElement('datalist');
        dlDist.id = 'bdDistrictsDatalist';
        districtList.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d;
            dlDist.appendChild(opt);
        });
        document.body.appendChild(dlDist);
    }

    districtInputs.forEach(inp => {
        inp.setAttribute('list', 'bdDistrictsDatalist');
        inp.addEventListener('input', function() {
            updateThanaDatalist(this.value);
        });
    });

    const thanaInputs = document.querySelectorAll('input[name="thana"]');
    let dlThana = document.getElementById('bdThanaDatalist');
    if (!dlThana) {
        dlThana = document.createElement('datalist');
        dlThana.id = 'bdThanaDatalist';
        document.body.appendChild(dlThana);
    }
    thanaInputs.forEach(inp => inp.setAttribute('list', 'bdThanaDatalist'));
}

function updateThanaDatalist(selectedDistrict) {
    const dlThana = document.getElementById('bdThanaDatalist');
    if (!dlThana) return;
    dlThana.innerHTML = '';

    const matchedDist = Object.keys(BD_DISTRICTS_DATA).find(
        d => d.toLowerCase() === (selectedDistrict || '').trim().toLowerCase()
    );

    if (matchedDist && BD_DISTRICTS_DATA[matchedDist]) {
        BD_DISTRICTS_DATA[matchedDist].forEach(t => {
            const opt = document.createElement('option');
            opt.value = t;
            dlThana.appendChild(opt);
        });
    }
}

// 3. Bangladesh Phone Formatter & Operator Detector
function formatBdPhone(input) {
    let val = input.value.replace(/[^0-9]/g, '');
    if (val.startsWith('880')) val = val.substring(3);
    if (val.length > 11) val = val.substring(0, 11);
    input.value = val;

    const badge = input.parentElement.querySelector('.phone-operator-badge');
    if (!badge) return;

    badge.className = 'phone-operator-badge';
    if (val.length >= 3) {
        const pfx = val.substring(0, 3);
        if (pfx === '017' || pfx === '013') {
            badge.textContent = 'GP';
            badge.classList.add('gp');
        } else if (pfx === '019' || pfx === '014') {
            badge.textContent = 'BL';
            badge.classList.add('bl');
        } else if (pfx === '018') {
            badge.textContent = 'Robi';
            badge.classList.add('robi');
        } else if (pfx === '016') {
            badge.textContent = 'Airtel';
            badge.classList.add('airtel');
        } else if (pfx === '015') {
            badge.textContent = 'Teletalk';
            badge.classList.add('tt');
        }
    }
}

// 4. Client-side Instant Canvas Photo Optimizer
function optimizeEventPhoto(fileInput, targetHiddenId, previewImgId, promptId, badgeId, targetW = 320, targetH = 380) {
    if (!fileInput.files || !fileInput.files[0]) return;
    const file = fileInput.files[0];
    const initialSizeKB = (file.size / 1024).toFixed(0);

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            canvas.width = targetW;
            canvas.height = targetH;

            const srcRatio = img.width / img.height;
            const targetRatio = targetW / targetH;
            let cropW = img.width;
            let cropH = img.height;
            let cropX = 0;
            let cropY = 0;

            if (srcRatio > targetRatio) {
                cropW = img.height * targetRatio;
                cropX = (img.width - cropW) / 2;
            } else {
                cropH = img.width / targetRatio;
                cropY = (img.height - cropH) / 2;
            }

            ctx.drawImage(img, cropX, cropY, cropW, cropH, 0, 0, targetW, targetH);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

            if (targetHiddenId) {
                const hiddenInput = document.getElementById(targetHiddenId);
                if (hiddenInput) hiddenInput.value = dataUrl;
            }

            if (previewImgId) {
                const preview = document.getElementById(previewImgId);
                if (preview) {
                    preview.src = dataUrl;
                    preview.style.display = 'block';
                }
            }

            if (promptId) {
                const prompt = document.getElementById(promptId);
                if (prompt) prompt.style.display = 'none';
            }

            if (badgeId) {
                const badge = document.getElementById(badgeId);
                if (badge) {
                    const optSizeKB = ((dataUrl.length * 0.75) / 1024).toFixed(0);
                    badge.textContent = `✓ ${initialSizeKB} KB → ${optSizeKB} KB (Auto Optimized)`;
                    badge.style.display = 'inline-block';
                }
            }
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// 5. Live Countdown Timer Engine
function initCountdown(endsAtIsoString, containerId = 'eventCountdownTimer') {
    const container = document.getElementById(containerId);
    if (!container || !endsAtIsoString) return;

    const targetTime = new Date(endsAtIsoString).getTime();

    function updateTick() {
        const now = new Date().getTime();
        const diff = targetTime - now;

        if (diff <= 0) {
            container.innerHTML = '<span class="text-danger fw-bold">রেজিস্ট্রেশনের সময়সীমা সমাপ্ত</span>';
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);

        const dEl = document.getElementById('cntDays');
        const hEl = document.getElementById('cntHours');
        const mEl = document.getElementById('cntMins');
        const sEl = document.getElementById('cntSecs');

        if (dEl) dEl.textContent = String(days).padStart(2, '0');
        if (hEl) hEl.textContent = String(hours).padStart(2, '0');
        if (mEl) mEl.textContent = String(minutes).padStart(2, '0');
        if (sEl) sEl.textContent = String(seconds).padStart(2, '0');
    }

    updateTick();
    setInterval(updateTick, 1000);
}

// 6. Copy text with feedback
function copyTextToClipboard(text, btnElement) {
    navigator.clipboard.writeText(text).then(() => {
        if (btnElement) {
            const orig = btnElement.innerHTML;
            btnElement.innerHTML = '<i class="fa-solid fa-check text-success"></i> Copied!';
            setTimeout(() => {
                btnElement.innerHTML = orig;
            }, 2000);
        }
    }).catch(() => {
        // Fallback
        const ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        if (btnElement) {
            btnElement.textContent = 'Copied!';
            setTimeout(() => { btnElement.textContent = 'Copy'; }, 2000);
        }
    });
}

// 7. Donation Preset Chips Handler
function selectDonationPreset(amount, chipElement) {
    const amountInput = document.getElementById('donationAmountInput');
    if (amountInput) {
        amountInput.value = amount;
        amountInput.dispatchEvent(new Event('input'));
    }
    document.querySelectorAll('.donation-chip').forEach(c => c.classList.remove('active'));
    if (chipElement) chipElement.classList.add('active');
}

// 8. Payment Method Card Selector
function selectPaymentMethod(methodKey) {
    const input = document.getElementById('paymentMethodInput');
    if (input) input.value = methodKey;

    document.querySelectorAll('.payment-method-card').forEach(c => c.classList.remove('active'));
    const activeCard = document.querySelector(`.payment-method-card[data-method="${methodKey}"]`);
    if (activeCard) activeCard.classList.add('active');

    // Show method specific instructions
    document.querySelectorAll('.payment-instruction-box').forEach(b => b.style.display = 'none');
    const targetBox = document.getElementById(`payInstructions_${methodKey}`);
    if (targetBox) targetBox.style.display = 'block';
}

// 9. Auto Init on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    initBdGeoChaining();

    // Phone formatting listener
    const phones = document.querySelectorAll('input[type="tel"]');
    phones.forEach(p => {
        p.addEventListener('input', function() { formatBdPhone(this); });
    });

    // Form submission spinner & double-click protection
    const eventForm = document.getElementById('eventRegForm');
    if (eventForm) {
        eventForm.addEventListener('submit', function() {
            const btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> সাবমিট হচ্ছে...';
                btn.disabled = true;
            }
        });
    }
});
