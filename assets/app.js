
var phone = document.querySelector('input[name="phone"]');

function checkPhone() {

  phone.value = phone.value.replace(/\D/g, '').slice(0, 11);

  if (phone.value.length >= 2 && phone.value.slice(0, 2) != '09') {
    phone.setCustomValidity('Phone number must start with 09');
  } else if (phone.value.length != 11) {
    phone.setCustomValidity('Phone number must be 11 digits');
  } else {
    phone.setCustomValidity('');   
  }
}

if (phone) {
  phone.addEventListener('input', checkPhone);
  checkPhone();
}

var checkIn = document.querySelector('input[name="check_in"]');
var checkOut = document.querySelector('input[name="check_out"]');

function checkDates() {
  if (checkOut.value <= checkIn.value) {
    checkOut.setCustomValidity('Check-out must be after check-in');
  } else {
    checkOut.setCustomValidity('');
  }
}

if (checkIn && checkOut) {
  checkIn.addEventListener('change', function () {

    var nextDay = new Date(checkIn.value);
    nextDay.setUTCDate(nextDay.getUTCDate() + 1);
    checkOut.min = nextDay.toISOString().slice(0, 10);
    checkDates();
  });
  checkOut.addEventListener('change', checkDates);
  checkDates();
}

var password = document.querySelector('input[name="password"]');
var confirmPassword = document.querySelector('input[name="confirm"]');

function checkPasswords() {
  if (confirmPassword.value != password.value) {
    confirmPassword.setCustomValidity('Passwords do not match');
  } else {
    confirmPassword.setCustomValidity('');
  }
}

if (password && confirmPassword) {
  password.addEventListener('input', checkPasswords);
  confirmPassword.addEventListener('input', checkPasswords);
}

var showPassword = document.getElementById('showPassword');

if (showPassword) {
  showPassword.addEventListener('change', function () {
    var fields = document.querySelectorAll('input[name="password"], input[name="confirm"]');
    for (var i = 0; i < fields.length; i++) {
      fields[i].type = showPassword.checked ? 'text' : 'password';
    }
  });
}

var messages = document.querySelectorAll('.alert-success');

for (var i = 0; i < messages.length; i++) {
  hideLater(messages[i]);
}

function hideLater(box) {
  setTimeout(function () {
    box.remove();
  }, 4000);
}
