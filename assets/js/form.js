
document.getElementById('notification').style.display = 'none';
document.getElementById('submitting').style.display = 'none';

document.getElementById('notificationforemail').style.display = 'none';
document.getElementById('submittingforrmail').style.display = 'none';


// funcation for the full contact us page;
function sendData(event){
    event.preventDefault();
    
    var formData = {
        name: document.forms["myForm"]["name"].value,
        number: document.forms["myForm"]["number"].value,
        email: document.forms["myForm"]["email"].value,
        message: document.forms["myForm"]["message"].value
    };

    document.getElementById('submitting').style.display='block';
     
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "submit.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {

            document.forms["myForm"].reset();
            document.getElementById('submitting').style.display = 'none';
            document.getElementById('notification').style.display = 'block';
            setTimeout(function() {
                document.getElementById('notification').style.display = 'none';
            }, 3000);
        }
    };
    xhr.send(JSON.stringify(formData));
}


//funcation for the email subscribe


function sendEmail(event){
    event.preventDefault();
    
    var formData = {
        email: document.forms["myForm2"]["email"].value
    };

    document.getElementById('submittingforrmail').style.display='block';
     
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "submit.php", true);
    xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {

            document.forms["myForm"].reset();
            document.getElementById('submittingforrmail').style.display = 'none';
            document.getElementById('notificationforemail').style.display = 'block';
            setTimeout(function() {
                document.getElementById('notificationforemail').style.display = 'none';
            }, 3000);
        }
    };
    xhr.send(JSON.stringify(formData));
}