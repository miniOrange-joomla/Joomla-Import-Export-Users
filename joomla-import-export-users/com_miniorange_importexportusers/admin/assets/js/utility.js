
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

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateButtonStates();
});

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