
const darkTheme = window.matchMedia("(prefers-color-scheme: dark)");
let favicon = document.querySelector('link[rel="icon"]');

function setFavicon() {
  if (darkTheme.matches) {
    favicon.href = "/images/icons/logo-white.svg";
  } else {
    favicon.href = "/images/icons/logo-blue.svg";
  }
}

darkTheme.addEventListener("change", setFavicon);
setFavicon();