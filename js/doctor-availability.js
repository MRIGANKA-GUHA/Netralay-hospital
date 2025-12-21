// doctor-availability.js
// Restrict appointment date picker to only allow days when the selected doctor is available

document.addEventListener('DOMContentLoaded', function() {
    var doctorSelect = document.getElementById('doctor_id');
    var dateInput = document.getElementById('appointment_date');
    if (!doctorSelect || !dateInput) return;

    // This object will be filled by PHP
    var doctorAvailability = window.doctorAvailability || {};

    function getAllowedDays(doctorId) {
        if (!doctorAvailability[doctorId]) return [];
        var days = doctorAvailability[doctorId];
        var dayMap = {"Sunday": 0, "Monday": 1, "Tuesday": 2, "Wednesday": 3, "Thursday": 4, "Friday": 5, "Saturday": 6};
        return days.map(function(day) { return dayMap[day]; });
    }

    function validateDate() {
        var doctorId = doctorSelect.value;
        var allowedDays = getAllowedDays(doctorId);
        var d = new Date(dateInput.value);
        if (dateInput.value && allowedDays.length && allowedDays.indexOf(d.getDay()) === -1) {
            dateInput.setCustomValidity('Doctor is not available on this day.');
            dateInput.reportValidity();
            dateInput.value = '';
        } else {
            dateInput.setCustomValidity('');
        }
    }

    doctorSelect.addEventListener('change', function() {
        dateInput.value = '';
        dateInput.setCustomValidity('');
    });
    dateInput.addEventListener('input', validateDate);
    dateInput.addEventListener('blur', validateDate);
});
