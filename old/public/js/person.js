
const family = {
  'container': document.querySelector('.person-family__wrapper'),
  'godFathers': document.getElementsByClassName("person-family__portrait--godfather"),
  'godChildrens': document.getElementsByClassName("person-family__portrait--godchild"),
}

function circlePositioning(elements, position = 'top') {

  let count = elements.length + 1;
  let size = family.container.clientHeight;

  let halfPerimeter = Math.PI * (size / 2);
  let pictureSize = halfPerimeter / (count + 3);

  if (pictureSize > size*0.2) {
    pictureSize = size*0.2;
  }

  for (let i = 0; i < elements.length; i++) {

    let element = elements[i];
    let theta = (Math.PI) / count * (i + 1);
  
    let x = Math.round(50 + 50 * Math.cos(theta));
    let y = Math.round(50 - 50 * Math.sin(theta));
  
    element.style.width = pictureSize + "px";

    switch (position) {
      case 'top':
        element.style.left = x + "%";
        element.style.top = y + "%";
        break;

      case 'bottom':
        element.style.left = x + "%";
        element.style.bottom = y + "%";
        break;

      default:
        console.log('Position not implemented');
    }
  }
}

function linkPositioning() {
  circlePositioning(family.godFathers, 'top');
  circlePositioning(family.godChildrens, 'bottom');
}

window.addEventListener('resize', linkPositioning);

linkPositioning();