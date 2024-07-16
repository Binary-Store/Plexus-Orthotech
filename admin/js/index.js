// Function to get a cookie by name
function getCookie(name) {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; ${name}=`);
  if (parts.length === 2) return parts.pop().split(';').shift();
  return null;
}

// Function to set a cookie
function setCookie(name, value, days) {
  let expires = "";
  if (days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    expires = "; expires=" + date.toUTCString();
  }
  document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

// Function to check for a specific cookie and redirect if not found
function checkCookieAndRedirect(cookieName, loginPageUrl) {
  const cookieValue = getCookie(cookieName);
  if (!cookieValue) {
    window.location.href = loginPageUrl;
  }
}

// Function to check for a specific cookie and redirect if found
function checkCookieAndRedirectIfFound(cookieName, homePageUrl) {
  const cookieValue = getCookie(cookieName);
  if (cookieValue) {
    window.location.href = homePageUrl;
  }
}
