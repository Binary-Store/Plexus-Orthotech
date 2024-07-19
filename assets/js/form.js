// funcation for the full contact us page;
function sendData(event) {
    document.getElementById('notification').style.display = 'none';
    document.getElementById('submitting').style.display = 'none';

    event.preventDefault();

    var formData = {
        name: document.forms["myForm"]["name"].value,
        number: document.forms["myForm"]["number"].value,
        email: document.forms["myForm"]["email"].value,
        message: document.forms["myForm"]["message"].value,
        profession: document.forms["myForm"]["profession"].value,
        city: document.forms["myForm"]["city"].value,
        country: document.forms["myForm"]["country"].value,
        subject: document.forms["myForm"]["subject"].value,
        state: document.forms["myForm"]["state"].value,
    };

    document.getElementById('submitting').style.display = 'block';

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "submit.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {

            document.forms["myForm"].reset();
            document.getElementById('submitting').style.display = 'none';
            document.getElementById('notification').style.display = 'block';
            setTimeout(function () {
                document.getElementById('notification').style.display = 'none';
            }, 3000);
        }
    };
    xhr.send(JSON.stringify(formData));
}

//funcation for the email subscribe

function sendEmail(event) {
    event.preventDefault();

    // Identify the form that triggered the event
    const form = event.target;
    const emailField = form.querySelector('[name="email"]');
    const subscribeBtnText = form.querySelector('.subscribeBtnText');
    const loadingSpinner = form.querySelector('.loadingSpinner');
    const arrow = form.querySelector('.arrow');

    // Hide button text and arrow, show loading spinner
    subscribeBtnText.style.display = 'none';
    loadingSpinner.style.display = 'inline-block';
    arrow.style.display = 'none';

    var formData = {
        email: emailField.value
    };

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "submit.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            form.reset();
            subscribeBtnText.style.display = 'inline';
            loadingSpinner.style.display = 'none';
            arrow.style.display = 'inline';
        }
    };
    xhr.send(JSON.stringify(formData));
}


function sendCareer(event) {
    event.preventDefault();

    const form = document.getElementById('appointmentform');
    const formData = new FormData(form);

    fetch('./career.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(result => {
            document.getElementById('submittingforcareer').style.display = 'none';
            document.getElementById('notificationforcareer').style.display = 'block';
            // reset form 
            form.reset();
        })
        .catch(error => {
            console.error('Error:', error);
        });

    document.getElementById('submittingforcareer').style.display = 'block';
}

function sendChannelPartnerData(event) {
    event.preventDefault();
    document.getElementById('notificationforemail').style.display = 'none';
    document.getElementById('submittingforrmail').style.display = 'none';

    const formData = {
        name: document.forms["channelPartnerForm"]["name"].value,
        number: document.forms["channelPartnerForm"]["number"].value,
        email: document.forms["channelPartnerForm"]["email"].value,
        city: document.forms["channelPartnerForm"]["city"].value,
        country: document.forms["channelPartnerForm"]["country"].value,
        state: document.forms["channelPartnerForm"]["state"].value,
        subject: "Channel Partner Request",
    }

    document.getElementById('submittingforrmail').style.display = 'block';

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "submit.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {

            document.forms["channelPartnerForm"].reset();
            document.getElementById('submittingforrmail').style.display = 'none';
            document.getElementById('notificationforemail').style.display = 'block';
            setTimeout(function () {
                document.getElementById('notificationforemail').style.display = 'none';
            }, 3000);
        }
    };
    xhr.send(JSON.stringify(formData));
}