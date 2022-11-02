
document.querySelector("#login").addEventListener("click", event => {
    event.preventDefault(switchHiden("loginClass"));});
document.querySelector("#signUp").addEventListener("click",event => {
    event.preventDefault(switchHiden("signUpClass"));});

function switchHiden(class1){
    let class2;
    if (class1 === "loginClass") {
        class2 = "signUpClass";
    } else {
        class2 = "loginClass";
    }
    if (document.getElementById(class2).classList.contains("active")) {
        document.getElementById(class2).classList.remove("active");
        document.getElementById(class2).classList.add("hide");
    }
    document.getElementById(class1).classList.add("active");
    document.getElementById(class1).classList.remove("hide");
}