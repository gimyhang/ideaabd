/**
 * Bangladesh Complete Administrative Divisions, Districts, Upazilas & Post Offices
 * Used for Dynamic Chained Address Selectors in IDEA Publication Forms
 */

window.BD_GEO = {
    divisions: {
        "Dhaka": ["Dhaka", "Gazipur", "Narayanganj", "Narsingdi", "Tangail", "Manikganj", "Munshiganj", "Faridpur", "Gopalganj", "Madaripur", "Rajbari", "Shariatpur", "Kishoreganj"],
        "Chattogram": ["Chattogram", "Cox's Bazar", "Cumilla", "Feni", "Brahmanbaria", "Chandpur", "Noakhali", "Lakshmipur", "Rangamati", "Khagrachhari", "Bandarban"],
        "Rajshahi": ["Rajshahi", "Bogura", "Pabna", "Sirajganj", "Naogaon", "Natore", "Joypurhat", "Chapainawabganj"],
        "Rangpur": ["Rangpur", "Dinajpur", "Gaibandha", "Kurigram", "Lalmonirhat", "Nilphamari", "Panchagarh", "Thakurgaon"],
        "Khulna": ["Khulna", "Jashore", "Satkhira", "Bagerhat", "Jhenaidah", "Kushtia", "Magura", "Meherpur", "Narail", "Chuadanga"],
        "Barishal": ["Barishal", "Bhola", "Patuakhali", "Pirojpur", "Barguna", "Jhalokathi"],
        "Sylhet": ["Sylhet", "Moulvibazar", "Habiganj", "Sunamganj"],
        "Mymensingh": ["Mymensingh", "Jamalpur", "Netrokona", "Sherpur"]
    },
    
    upazilas: {
        // Dhaka Division
        "Dhaka": ["Dhanmondi", "Gulshan", "Banani", "Mirpur", "Uttara", "Mohammadpur", "Motijheel", "Tejgaon", "Badda", "Khilgaon", "Lalbagh", "Shahbagh", "Ramna", "Paltan", "Hazaribagh", "Keraniganj", "Savar", "Dhamrai", "Ashulia", "Cantonment", "Demra", "Jatrabari", "Kadamtali", "Kafrul", "Kamrangirchar", "Khilkhet", "Kotwali", "New Market", "Pallabi", "Rampura", "Sabujbagh", "Shyampur", "Sutrapur", "Turag", "Vatara", "Wari", "Dohar", "Nawabganj"],
        "Gazipur": ["Gazipur City / Sadar", "Kaliakair", "Kapasia", "Sreepur", "Kaliganj", "Tongi"],
        "Narayanganj": ["Narayanganj City / Sadar", "Bandar", "Araihazar", "Rupganj", "Sonargaon", "Fatullah", "Siddhirganj"],
        "Narsingdi": ["Narsingdi Sadar", "Belabo", "Monohardi", "Palash", "Raipura", "Shibpur"],
        "Tangail": ["Tangail Sadar", "Mirzapur", "Dhanbari", "Madhupur", "Gopalpur", "Ghatail", "Kalihati", "Sakhipur", "Basail", "Delduar", "Nagarpur", "Bhuapur"],
        "Manikganj": ["Manikganj Sadar", "Singair", "Shibalaya", "Saturia", "Harirampur", "Ghior", "Daulatpur"],
        "Munshiganj": ["Munshiganj Sadar", "Sreenagar", "Sirajdikhan", "Tongibari", "Lohajang", "Gazaria"],
        "Faridpur": ["Faridpur Sadar", "Boalmari", "Alfadanga", "Madhukhali", "Bhanga", "Nagarkanda", "Charbhadrasan", "Sadarpur", "Saltha"],
        "Gopalganj": ["Gopalganj Sadar", "Kashiani", "Kotalipara", "Muksudpur", "Tungipara"],
        "Madaripur": ["Madaripur Sadar", "Shibchar", "Kalkini", "Rajoir", "Dasar"],
        "Rajbari": ["Rajbari Sadar", "Pangsha", "Baliakandi", "Goalandaghat", "Kalukhali"],
        "Shariatpur": ["Shariatpur Sadar", "Naria", "Damudya", "Bhedarganj", "Gosairhat", "Zanjira"],
        "Kishoreganj": ["Kishoreganj Sadar", "Bhairab", "Bajitpur", "Katiadi", "Karimganj", "Hossainpur", "Pakundia", "Kuliarchar", "Tarail", "Itna", "Mithamain", "Austagram", "Nikli"],

        // Chattogram Division
        "Chattogram": ["Chattogram City / Sadar", "Kotwali", "Panchlaish", "Pahartali", "Double Mooring", "Halishahar", "Khulshi", "Bakalia", "Bayezid", "Chandgaon", "Patenga", "Hathazari", "Raozan", "Rangunia", "Fatikchhari", "Sitakunda", "Mirsharai", "Patiya", "Boalkhali", "Anwara", "Chandanaish", "Lohagara", "Satkania", "Banshkhali", "Sandwip", "Karnaphuli"],
        "Cox's Bazar": ["Cox's Bazar Sadar", "Chakaria", "Maheshkhali", "Teknaf", "Ukhia", "Ramu", "Pekua", "Kutubdia", "Eidgaon"],
        "Cumilla": ["Cumilla City / Adarsha Sadar", "Cumilla Sadar Dakshin", "Barura", "Brahmanpara", "Burichang", "Chandina", "Chauddagram", "Daudkandi", "Debidwar", "Homna", "Laksam", "Muradnagar", "Meghna", "Monohargonj", "Nangalkot", "Titas", "Lalmai"],
        "Feni": ["Feni Sadar", "Chhagalnaiya", "Daganbhuiyan", "Parshuram", "Fulgazi", "Sonagazi"],
        "Brahmanbaria": ["Brahmanbaria Sadar", "Ashuganj", "Nasirnagar", "Nabinagar", "Sarail", "Kasba", "Akhaura", "Bancharampur", "Bijoynagar"],
        "Chandpur": ["Chandpur Sadar", "Faridganj", "Haimchar", "Haziganj", "Kachua", "Matlab Dakshin", "Matlab Uttar", "Shahrasti"],
        "Noakhali": ["Noakhali Sadar", "Begumganj", "Chatkhil", "Companiganj", "Hatiya", "Senbagh", "Sonaimuri", "Subarnachar", "Kabirhat"],
        "Lakshmipur": ["Lakshmipur Sadar", "Raipur", "Ramganj", "Ramgati", "Kamalnagar"],
        "Rangamati": ["Rangamati Sadar", "Kaptai", "Kawkhali", "Baghaichhari", "Barkal", "Belaichhari", "Juraichhari", "Langadu", "Naniarchar", "Rajasthali"],
        "Khagrachhari": ["Khagrachhari Sadar", "Dighinala", "Lakshmichhari", "Mahalchhari", "Manikchhari", "Matiranga", "Panchhari", "Ramgarh", "Guimara"],
        "Bandarban": ["Bandarban Sadar", "Ali Kadam", "Lama", "Naikhongchhari", "Rowangchhari", "Ruma", "Thanchi"],

        // Rajshahi Division
        "Rajshahi": ["Rajshahi City / Boalia", "Rajpara", "Motihar", "Shah Makhdum", "Paba", "Godagari", "Tanore", "Bagmara", "Durgapur", "Puthia", "Charghat", "Bagha", "Mohonpur"],
        "Bogura": ["Bogura City / Sadar", "Shajahanpur", "Sherpur", "Shibganj", "Kahaloo", "Nandigram", "Dupchanchia", "Adamdighi", "Gabtali", "Sonatala", "Sariakandi", "Dhunat"],
        "Pabna": ["Pabna Sadar", "Ishwardi", "Atgharia", "Bera", "Bhangura", "Chatmohar", "Faridpur", "Santhia", "Sujanagar"],
        "Sirajganj": ["Sirajganj Sadar", "Belkuchi", "Chauhali", "Kamarkhanda", "Kazipur", "Raiganj", "Shahjadpur", "Tarash", "Ullahpara"],
        "Naogaon": ["Naogaon Sadar", "Mohadevpur", "Manda", "Patnitala", "Dhamoirhat", "Badalgachhi", "Raninagar", "Atrai", "Porsha", "Sapahar", "Niamatpur"],
        "Natore": ["Natore Sadar", "Singra", "Baraigram", "Gurudaspur", "Lalpur", "Bagatipara", "Naldanga"],
        "Joypurhat": ["Joypurhat Sadar", "Panchbibi", "Kalai", "Khetlal", "Akkelpur"],
        "Chapainawabganj": ["Chapainawabganj Sadar", "Shibganj", "Gomastapur", "Nachole", "Bholahat"],

        // Rangpur Division
        "Rangpur": ["Rangpur City / Sadar", "Kotwali", "Badarganj", "Gangachara", "Kaunia", "Mithapukur", "Pirgachha", "Pirganj", "Taraganj"],
        "Dinajpur": ["Dinajpur Sadar", "Birganj", "Biral", "Bochaganj", "Chirirbandar", "Fulbari", "Ghoraghat", "Hakimpur", "Kaharole", "Khansama", "Nawabganj", "Parbatipur", "Setabganj"],
        "Gaibandha": ["Gaibandha Sadar", "Gobindaganj", "Palashbari", "Sadullapur", "Saghata", "Sundarganj", "Fulchhari"],
        "Kurigram": ["Kurigram Sadar", "Nageshwari", "Bhurungamari", "Phulbari", "Rajarhat", "Ulipur", "Chilmari", "Roumari", "Char Rajibpur"],
        "Lalmonirhat": ["Lalmonirhat Sadar", "Aditmari", "Kaliganj", "Hatibandha", "Patgram"],
        "Nilphamari": ["Nilphamari Sadar", "Saidpur", "Jaldhaka", "Kishoreganj", "Domar", "Dimla"],
        "Panchagarh": ["Panchagarh Sadar", "Boda", "Debiganj", "Atwari", "Tetulia"],
        "Thakurgaon": ["Thakurgaon Sadar", "Pirganj", "Ranisankail", "Baliadangi", "Haripur"],

        // Khulna Division
        "Khulna": ["Khulna City / Sadar", "Sonadanga", "Khalishpur", "Daulatpur", "Khan Jahan Ali", "Batiaghata", "Dacope", "Dumuria", "Dighalia", "Koyra", "Paikgachha", "Phultala", "Rupsha", "Terokhada"],
        "Jashore": ["Jashore Sadar", "Jhikargachha", "Sharsha", "Manirampur", "Keshabpur", "Abhaynagar", "Bagherpara", "Chaugachha", "Benapole"],
        "Satkhira": ["Satkhira Sadar", "Kalaroa", "Tala", "Kaliganj", "Shyamnagar", "Assasuni", "Debhata"],
        "Bagerhat": ["Bagerhat Sadar", "Mongla", "Morrelganj", "Rampal", "Sarankhola", "Kachua", "Fakirhat", "Chitalmari", "Mollahat"],
        "Jhenaidah": ["Jhenaidah Sadar", "Kaliganj", "Kotchandpur", "Maheshpur", "Shailkupa", "Harinakunda"],
        "Kushtia": ["Kushtia Sadar", "Kumarkhali", "Khoksa", "Mirpur", "Bheramara", "Daulatpur"],
        "Magura": ["Magura Sadar", "Sreepur", "Mohammadpur", "Shalikha"],
        "Meherpur": ["Meherpur Sadar", "Gangni", "Mujibnagar"],
        "Narail": ["Narail Sadar", "Lohagara", "Kalia"],
        "Chuadanga": ["Chuadanga Sadar", "Alamdanga", "Damurhuda", "Jibannagar"],

        // Barishal Division
        "Barishal": ["Barishal City / Kotwali", "Barishal Sadar", "Bakerganj", "Babuganj", "Wazirpur", "Banaripara", "Gournadi", "Agailjhara", "Mehendiganj", "Muladi", "Hizla"],
        "Bhola": ["Bhola Sadar", "Burhanuddin", "Char Fasson", "Daulatkhan", "Lalmohan", "Manpura", "Tazumuddin"],
        "Patuakhali": ["Patuakhali Sadar", "Galachipa", "Kalapara", "Bauphal", "Dashmina", "Mirzaganj", "Dumki", "Rangabali"],
        "Pirojpur": ["Pirojpur Sadar", "Mathbaria", "Bhandaria", "Nesarabad (Swarupkati)", "Nazirpur", "Kawkhali", "Zianagar (Indurkani)"],
        "Barguna": ["Barguna Sadar", "Amtali", "Patharghata", "Betagi", "Bamna", "Taltali"],
        "Jhalokathi": ["Jhalokathi Sadar", "Nalchity", "Rajapur", "Kathalia"],

        // Sylhet Division
        "Sylhet": ["Sylhet City / Kotwali", "Sylhet Sadar", "Beanibazar", "Golapganj", "Companiganj", "Fenchuganj", "Bishwanath", "Gowainghat", "Jaintiapur", "Kanaighat", "Zakiganj", "Dakshin Surma", "Osmani Nagar"],
        "Moulvibazar": ["Moulvibazar Sadar", "Sreemangal", "Kamalganj", "Kulaura", "Barlekha", "Juri", "Rajnagar"],
        "Habiganj": ["Habiganj Sadar", "Bahubal", "Madhabpur", "Chunarughat", "Lakhai", "Nabiganj", "Ajmiriganj", "Baniachang", "Shayestaganj"],
        "Sunamganj": ["Sunamganj Sadar", "Chhatak", "Jagannathpur", "Dowarabazar", "Tahirpur", "Dharampasha", "Jamalganj", "Shantiganj", "Derai", "Sullah", "Bishwamvarpur"],

        // Mymensingh Division
        "Mymensingh": ["Mymensingh City / Kotwali", "Mymensingh Sadar", "Muktagachha", "Trishal", "Bhaluka", "Fulbaria", "Gafargaon", "Haluaghat", "Ishwarganj", "Dhobaura", "Nandail", "Phulpur", "Tara Khanda"],
        "Jamalpur": ["Jamalpur Sadar", "Melandaha", "Islampur", "Dewanganj", "Sarishabari", "Madarganj", "Baksiganj"],
        "Netrokona": ["Netrokona Sadar", "Mohanganj", "Kendua", "Purbadhala", "Durgapur", "Barhatta", "Kalmakanda", "Atpara", "Madan", "Khaliajuri"],
        "Sherpur": ["Sherpur Sadar", "Nakla", "Nalitabari", "Jhenaigati", "Sreebardi"]
    },

    postOffices: {
        "Dinajpur": ["Dinajpur Head Post Office (5200)", "Setabganj (5216)", "Birol (5210)", "Birganj (5220)", "Bochaganj (5215)", "Chirirbandar (5240)", "Fulbari (5260)", "Parbatipur (5250)", "Ghoraghat (5290)", "Hakimpur (5270)", "Kaharole (5230)", "Khansama (5233)", "Nawabganj (5280)"],
        "Dhaka": ["Dhaka GPO (1000)", "Dhanmondi (1209)", "Gulshan (1212)", "Banani (1213)", "Mirpur (1216)", "Uttara (1230)", "Mohammadpur (1207)", "Motijheel (1000)", "Tejgaon (1215)", "Savar (1340)", "Dhamrai (1350)", "Keraniganj (1310)"],
        "Chattogram": ["Chattogram GPO (4000)", "Agrabad (4100)", "Panchlaish (4203)", "Hathazari (4330)", "Patiya (4370)", "Sitakunda (4310)", "Mirsharai (4320)", "Raozan (4340)"],
        "Rajshahi": ["Rajshahi GPO (6000)", "Rajshahi University (6205)", "Godagari (6290)", "Paba (6210)", "Puthia (6260)", "Bagmara (6250)", "Charghat (6270)"],
        "Khulna": ["Khulna GPO (9000)", "Daulatpur (9202)", "Khalishpur (9000)", "Phultala (9210)", "Rupsha (9240)", "Batiaghata (9260)"],
        "Barishal": ["Barishal Head Post Office (8200)", "Gournadi (8230)", "Bakerganj (8280)", "Babuganj (8210)", "Wazirpur (8220)", "Banaripara (8250)"],
        "Sylhet": ["Sylhet Head Post Office (3100)", "Shahjalal University (3114)", "Beanibazar (3170)", "Golapganj (3160)", "Bishwanath (3130)", "Sreemangal (3210)"],
        "Rangpur": ["Rangpur Head Post Office (5400)", "Badarganj (5420)", "Mithapukur (5460)", "Pirgachha (5450)", "Pirganj (5470)", "Kaunia (5440)"],
        "Mymensingh": ["Mymensingh Head Post Office (2200)", "Agriculture University (2202)", "Trishal (2220)", "Muktagachha (2210)", "Bhaluka (2240)"],
        "Bogura": ["Bogura Head Post Office (5800)", "Sherpur (5840)", "Shibganj (5810)", "Dupchanchia (5880)", "Kahaloo (5870)"],
        "Cumilla": ["Cumilla Head Post Office (3500)", "Cumilla Cantonment (3501)", "Laksam (3570)", "Daudkandi (3516)", "Chandina (3510)"],
        "Jashore": ["Jashore Head Post Office (7400)", "Benapole (7432)", "Jhikargachha (7420)", "Sharsha (7430)", "Manirampur (7440)"]
    }
};

window.BD_GEO_DATA = window.BD_GEO;

const DIVISION_BN_NAMES = {
    "Dhaka": "ঢাকা (Dhaka)",
    "Chattogram": "চট্টগ্রাম (Chattogram)",
    "Rajshahi": "রাজশাহী (Rajshahi)",
    "Rangpur": "রংপুর (Rangpur)",
    "Khulna": "খুলনা (Khulna)",
    "Barishal": "বরিশাল (Barishal)",
    "Sylhet": "সিলেট (Sylhet)",
    "Mymensingh": "ময়মনসিংহ (Mymensingh)"
};

/**
 * Initialize 4-tier Address Chaining:
 * Division -> District -> Upazila/Thana -> Post Office
 * Supports both initAddressChaining('perm') and initAddressChaining('writerDivision', 'writerDistrict', ...)
 */
function initAddressChaining(divArg, distArg, upazilaArg, poArg, defaultDiv, defaultDist) {
    let divSelect, distSelect, upazilaSelect, poSelect, prefix = '';

    if (divArg instanceof HTMLElement) {
        divSelect = divArg;
        distSelect = distArg instanceof HTMLElement ? distArg : document.getElementById(distArg);
        upazilaSelect = upazilaArg instanceof HTMLElement ? upazilaArg : document.getElementById(upazilaArg);
        poSelect = poArg instanceof HTMLElement ? poArg : document.getElementById(poArg);
    } else if (typeof divArg === 'string') {
        // Check if divArg is an exact element ID (e.g. 'writerDivision')
        const directDiv = document.getElementById(divArg);
        if (directDiv) {
            divSelect = directDiv;
            distSelect = document.getElementById(distArg);
            upazilaSelect = document.getElementById(upazilaArg);
            poSelect = document.getElementById(poArg);
            prefix = divArg;
        } else {
            // Check if divArg is a prefix like 'perm' or 'pres'
            prefix = divArg;
            divSelect = document.getElementById(prefix + 'Division');
            distSelect = document.getElementById(prefix + 'District');
            upazilaSelect = document.getElementById(prefix + 'Upazila');
            poSelect = document.getElementById(prefix + 'PostOffice');
        }
    }

    if (!divSelect) return;

    // Populate Divisions
    divSelect.innerHTML = '<option value="">-- বিভাগ নির্বাচন করুন (Select Division) --</option>';
    if (window.BD_GEO && window.BD_GEO.divisions) {
        Object.keys(window.BD_GEO.divisions).forEach(div => {
            const opt = document.createElement('option');
            opt.value = div;
            opt.textContent = DIVISION_BN_NAMES[div] || div;
            divSelect.appendChild(opt);
        });
    }

    // Division -> Districts
    divSelect.addEventListener('change', function() {
        const selectedDiv = this.value;
        if (distSelect) distSelect.innerHTML = '<option value="">-- জেলা নির্বাচন করুন (Select District) --</option>';
        if (upazilaSelect) upazilaSelect.innerHTML = '<option value="">-- মহানগর / উপজেলা নির্বাচন করুন --</option>';
        if (poSelect) poSelect.innerHTML = '<option value="">-- পোস্ট অফিস নির্বাচন করুন --</option>';

        if (selectedDiv && window.BD_GEO && window.BD_GEO.divisions[selectedDiv] && distSelect) {
            window.BD_GEO.divisions[selectedDiv].forEach(dist => {
                const opt = document.createElement('option');
                opt.value = dist;
                opt.textContent = dist;
                distSelect.appendChild(opt);
            });
        }
        assembleFormattedAddress(prefix);
    });

    // District -> Upazilas & Post Offices
    if (distSelect) {
        distSelect.addEventListener('change', function() {
            const selectedDist = this.value;
            if (upazilaSelect) upazilaSelect.innerHTML = '<option value="">-- মহানগর / উপজেলা নির্বাচন করুন --</option>';
            if (poSelect) poSelect.innerHTML = '<option value="">-- পোস্ট অফিস নির্বাচন করুন --</option>';

            if (selectedDist && window.BD_GEO && window.BD_GEO.upazilas[selectedDist] && upazilaSelect) {
                window.BD_GEO.upazilas[selectedDist].forEach(upa => {
                    const opt = document.createElement('option');
                    opt.value = upa;
                    opt.textContent = upa;
                    upazilaSelect.appendChild(opt);
                });
            }

            if (poSelect && selectedDist && window.BD_GEO && window.BD_GEO.postOffices[selectedDist]) {
                window.BD_GEO.postOffices[selectedDist].forEach(po => {
                    const opt = document.createElement('option');
                    opt.value = po;
                    opt.textContent = po;
                    poSelect.appendChild(opt);
                });
            }
            assembleFormattedAddress(prefix);
        });
    }

    // Upazila Change
    if (upazilaSelect) {
        upazilaSelect.addEventListener('change', function() {
            assembleFormattedAddress(prefix);
        });
    }

    if (poSelect) {
        poSelect.addEventListener('change', function() {
            assembleFormattedAddress(prefix);
        });
    }

    const villageInput = document.getElementById(prefix + 'Village') || document.getElementById('writerVillage');
    if (villageInput) {
        villageInput.addEventListener('input', function() {
            assembleFormattedAddress(prefix);
        });
    }

    // Handle default pre-selection
    if (defaultDiv && window.BD_GEO && window.BD_GEO.divisions[defaultDiv]) {
        divSelect.value = defaultDiv;
        divSelect.dispatchEvent(new Event('change'));
        if (defaultDist && distSelect) {
            setTimeout(() => {
                distSelect.value = defaultDist;
                distSelect.dispatchEvent(new Event('change'));
            }, 10);
        }
    }
}

/**
 * Assembles full address text string for backend and printable form:
 * VILL- ..., POST- ..., THANA- ..., DIST- ..., DIV- ...
 */
function assembleFormattedAddress(prefix = '') {
    const div = document.getElementById(prefix + 'Division')?.value || '';
    const dist = document.getElementById(prefix + 'District')?.value || '';
    const upazila = document.getElementById(prefix + 'Upazila')?.value || '';
    const po = document.getElementById(prefix + 'PostOffice')?.value || '';
    const village = document.getElementById(prefix + 'Village')?.value || '';

    let parts = [];
    if (village) parts.push('VILL/ROAD- ' + village);
    if (po) parts.push('POST- ' + po);
    if (upazila) parts.push('THANA/UPAZILA- ' + upazila);
    if (dist) parts.push('DIST- ' + dist);
    if (div) parts.push('DIV- ' + div);

    const fullAddrHidden = document.getElementById(prefix + 'FullAddress');
    if (fullAddrHidden) {
        fullAddrHidden.value = parts.join(', ');
    }
}

/**
 * Copy Permanent Address to Present Address
 */
function syncPermanentToPresent() {
    const isChecked = document.getElementById('syncAddressCheck')?.checked;
    if (!isChecked) return;

    const permDiv = document.getElementById('permDivision')?.value || '';
    const permDist = document.getElementById('permDistrict')?.value || '';
    const permUpazila = document.getElementById('permUpazila')?.value || '';
    const permPo = document.getElementById('permPostOffice')?.value || '';
    const permVillage = document.getElementById('permVillage')?.value || '';

    const presDiv = document.getElementById('presDivision');
    const presDist = document.getElementById('presDistrict');
    const presUpazila = document.getElementById('presUpazila');
    const presPo = document.getElementById('presPostOffice');
    const presVillage = document.getElementById('presVillage');

    if (presDiv) {
        presDiv.value = permDiv;
        presDiv.dispatchEvent(new Event('change'));
    }

    setTimeout(() => {
        if (presDist) {
            presDist.value = permDist;
            presDist.dispatchEvent(new Event('change'));
        }
        setTimeout(() => {
            if (presUpazila) presUpazila.value = permUpazila;
            if (presPo) presPo.value = permPo;
            if (presVillage) presVillage.value = permVillage;
            assembleFormattedAddress('pres');
        }, 50);
    }, 50);
}
