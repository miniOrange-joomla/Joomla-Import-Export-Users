
function moCancelForm() {
    jQuery('#cancel_form').submit();
}

function mo_login_page() {
    jQuery('#customer_login_form').submit();
}
function showmodal(){
    jQuery('#myModal').css("display","block");
}
function hidemodal(){
    jQuery('#myModal').css("display","none");
}

function add_css_tab(element) {
    // Remove active class from all tabs
    jQuery(".mo_nav_tab_active").removeClass("mo_nav_tab_active");
    jQuery(".tab-pane").removeClass("active");
    
    // Add active class to clicked tab
    jQuery(element).addClass("mo_nav_tab_active");
    
    // Show corresponding tab content
    var targetId = jQuery(element).attr('href').substring(1);
    jQuery('#' + targetId).addClass('active');
}

function displayFileName() {
    var fileInput = document.getElementById('fileInput');
    var file = fileInput.files[0];

    if (file && file.name.endsWith('.json')) {
        document.getElementById('fileName').textContent = file.name; 
    } else {
        document.getElementById('fileName').textContent = "Please select a .json file.";
    }
}



function toggleCallTimeField() {
    var callDateField = document.getElementById('call_date_field');
    var callTimeField = document.getElementById('call_time_field');
    var callDateInput = document.getElementById('call_date');
    var callTimeInput = document.getElementById('call_time');
    var setupCallRadio = document.getElementById('support_call');
    
    // Update button visual states
    updateButtonStates();
    
    if (setupCallRadio.checked) {
        callDateField.style.display = 'flex';
        callTimeField.style.display = 'flex';
        callDateInput.setAttribute('required', 'required');
        callTimeInput.setAttribute('required', 'required');
    } else {
        callDateField.style.display = 'none';
        callTimeField.style.display = 'none';
        callDateInput.removeAttribute('required');
        callTimeInput.removeAttribute('required');
        callDateInput.value = '';
        callTimeInput.value = '';
    }
}

function updateButtonStates() {
    var generalBtn = document.getElementById('general_query_btn');
    var callBtn = document.getElementById('setup_call_btn');
    var generalRadio = document.getElementById('support_general');
    var callRadio = document.getElementById('support_call');
    
    if (generalRadio.checked) {
        generalBtn.classList.add('active');
        callBtn.classList.remove('active');
    } else if (callRadio.checked) {
        callBtn.classList.add('active');
        generalBtn.classList.remove('active');
    }
}


document.addEventListener('DOMContentLoaded', function () {

    const list = document.getElementById('countryList');
    const select = document.getElementById('countrySelect');
    const hiddenInput = document.getElementById('countryCode');

    // If the current page doesn't have the phone dropdown, do nothing.
    if (!list || !select || !hiddenInput) {
        return;
    }

    // countries is defined in assets/js/countries.js
    if (typeof countries === 'undefined' || !Array.isArray(countries)) {
        // Avoid breaking the page if countries.js wasn't loaded for some reason.
        return;
    }

    function getFlagEmoji(countryCode) {
        if (!countryCode || typeof countryCode !== 'string' || countryCode.length !== 2) {
            return '';
        }
        const code = countryCode.toUpperCase();
        const A = 65;
        const REGIONAL_INDICATOR_A = 0x1F1E6; // 🇦
        const first = code.charCodeAt(0) - A + REGIONAL_INDICATOR_A;
        const second = code.charCodeAt(1) - A + REGIONAL_INDICATOR_A;
        try {
            return String.fromCodePoint(first, second);
        } catch (e) {
            return '';
        }
    }

    function setSelectedCountry(country) {
        const flagEl = select.querySelector('.flag');
        const dialEl = select.querySelector('.dial-code');
        if (!flagEl || !dialEl) {
            return;
        }

        // Selected view: ONLY flag + dial code (no country name)
        flagEl.className = 'flag';
        flagEl.textContent = getFlagEmoji(country.code);
        dialEl.textContent = `+${country.dial}`;
        hiddenInput.value = String(country.dial);
    }

    function normalizeForSearch(value) {
        return String(value || '').trim().toLowerCase();
    }

    // Search box (sticky at top of dropdown)
    const searchLi = document.createElement('li');
    searchLi.className = 'mo-country-search';
    searchLi.innerHTML = `
        <input
            type="text"
            id="moCountrySearch"
            class="mo-country-search-input"
            placeholder="Search country or code…"
            autocomplete="off"
            spellcheck="false"
        />
    `;
    list.appendChild(searchLi);

    const searchInput = searchLi.querySelector('input');

    // Build dropdown: show country name + dial code
    countries.forEach(country => {
        const li = document.createElement('li');
        li.dataset.name = normalizeForSearch(country.name);
        li.dataset.code = normalizeForSearch(country.code);
        li.dataset.dial = normalizeForSearch(country.dial);

        li.innerHTML = `
            <span class="flag" aria-hidden="true">${getFlagEmoji(country.code)}</span>
            <span class="name">${country.name}</span>
            <span class="dial">+${country.dial}</span>
        `;

        li.onclick = function () {
            setSelectedCountry(country);
            list.classList.remove('open');
        };

        list.appendChild(li);
    });

    // Initialize selected from hidden value (dial code) if present, else first country.
    const currentDial = String(hiddenInput.value || '').replace(/\D/g, '');
    const initial = countries.find(c => String(c.dial) === currentDial) || countries[0];
    if (initial) {
        setSelectedCountry(initial);
    }

    function applyFilter() {
        if (!searchInput) {
            return;
        }
        const q = normalizeForSearch(searchInput.value);
        const items = list.querySelectorAll('li');
        items.forEach(function (li) {
            if (li === searchLi) {
                return;
            }
            // divider/empty li safety
            if (!li.dataset) {
                return;
            }
            if (q === '') {
                li.style.display = '';
                return;
            }
            const haystack = `${li.dataset.name || ''} ${li.dataset.code || ''} ${li.dataset.dial || ''}`;
            li.style.display = haystack.includes(q) ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilter);
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                searchInput.value = '';
                applyFilter();
                list.classList.remove('open');
                select.focus && select.focus();
            }
        });
    }

    select.onclick = () => {
        const isOpening = !list.classList.contains('open');
        list.classList.toggle('open');
        if (isOpening && searchInput) {
            // reset filter on open and focus search
            searchInput.value = '';
            applyFilter();
            setTimeout(() => searchInput.focus(), 0);
        }
    };

    // Close on outside click
    document.addEventListener('click', function (e) {
        if (!select.contains(e.target) && !list.contains(e.target)) {
            list.classList.remove('open');
        }
    });
});
