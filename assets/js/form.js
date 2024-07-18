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
    document.getElementById('notificationforemail').style.display = 'none';
    document.getElementById('submittingforrmail').style.display = 'none';

    event.preventDefault();

    var formData = {
        email: document.forms["myForm2"]["email"].value
    };

    document.getElementById('submittingforrmail').style.display = 'block';

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "submit.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {

            document.forms["myForm2"].reset();
            document.getElementById('submittingforrmail').style.display = 'none';
            document.getElementById('notificationforemail').style.display = 'block';
            setTimeout(function () {
                document.getElementById('notificationforemail').style.display = 'none';
            }, 3000);
        }
    };
    xhr.send(JSON.stringify(formData));
}

function sendCareer(event) {
    event.preventDefault();

    const form = document.getElementById('appointmentform');
    const formData = new FormData(form);

    fetch('send_mail.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(result => {
            document.getElementById('submittingforcareer').style.display = 'none';
            document.getElementById('notificationforcareer').style.display = 'block';
            console.log(result);
        })
        .catch(error => {
            console.error('Error:', error);
        });

    document.getElementById('submittingforcareer').style.display = 'block';
}
