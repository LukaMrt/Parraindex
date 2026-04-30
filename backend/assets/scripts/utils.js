export function byId(id) {
  // Wait for the DOM to be loaded

  return document.getElementById(id);
}

export function byClass(classNames) {
  return document.getElementsByClassName(classNames)[0];
}
