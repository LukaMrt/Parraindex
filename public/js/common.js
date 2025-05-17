
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)');

const updateFavicon = () => {
  const iconPath = prefersDark.matches
    ? '/img/icons/logo-white.svg'
    : '/img/icons/logo-blue.svg';

  let favicon = document.querySelector('link[rel="icon"]');
  favicon.href = iconPath;
};

prefersDark.addEventListener('change', updateFavicon);
updateFavicon();