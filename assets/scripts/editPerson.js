import {triggerErrorPopup, triggerSuccessPopup} from "./popup/popups.js";

const field = {
    form: document.querySelector(".edit-person"),
    bio: document.querySelector("#bio-field"),
    about: document.querySelector("#about-field"),
    colors: document.querySelector("#color-field"),
    contact: document.querySelector("#contact-field"),
    picture: document.querySelector("#img-uploader"),
    lastName: document.querySelector("#lastname-field"),
    firstName: document.querySelector("#firstname-field"),
    colorPicker: document.querySelector("#color-picker"),
    radioColorPicker: document.querySelector("#radio-color-picker"),
    characteristics: document.querySelector(".information__contact__characteristics"),
    sync: () => action.sync.classList.contains("active")
}

const preview = {
    bio: document.querySelector(".card__description"),
    color: document.querySelector(".card__banner"),
    contacts: document.querySelector(".card__social-network"),
    picture: document.querySelector(".card__picture"),
    overlayPicture: document.querySelector(".card__picture__overlay"),
    lastName: document.querySelector(".card__last-name"),
    firstName: document.querySelector(".card__first-name"),

    characteristicType: document.querySelectorAll(".social-network"),
    characteristics: document.querySelector(".card__social-network"),
}

const pictureModal = {
    container: document.querySelector(".edit-picture"),
    imagePreview: document.querySelector(".edit-picture__preview__picture"),
    imageUploader: document.querySelector("#img-uploader"),
    closeButton: document.querySelector(".edit-picture__close"),
}

const action = {
    delete: document.querySelector("#delete-person"),
    save: document.querySelector("#save-person"),
    download: document.querySelector("#download-person"),
    sync: document.querySelector(".sync"),
    invert: document.querySelector(".invert")
}

const initialPicture = preview.picture.src;
const personId = Number(document.querySelector(".card").id);

/**
 * Encode every field of the form in JSON
 * @returns {Promise<string>} The JSON string
 */
async function formEncodeJson() {

    let formData = new FormData(field.form);

    await new Promise((resolve) => {
        let reader = new FileReader();

        if (!field.picture.files[0]) {
            resolve();
        }

        reader.readAsDataURL(field.picture.files[0]);

        reader.onload = async (e) => {
            formData.append("person-picture", e.target.result);
            resolve();
        }
    });


    return JSON.stringify(Object.fromEntries(formData));
}

/**
 * Call method to update the preview and init the color picker
 */
function init() {
    initColor();
    updatePreview();

    if (personId === 0) {
        action.save.textContent = "Créer";
        action.delete.style.display = "none";
    }
}

/**
 * Select the color of the banner in the color picker
 */
function initColor() {

    let bannerColor = getComputedStyle(preview.color).getPropertyValue("background-color")
    pictureModal.imagePreview.style.backgroundColor = bannerColor;

    let find = false;

    for (const color of field.colors.children) {
        let selectorColor = getComputedStyle(color).getPropertyValue("background-color")
        if (selectorColor === bannerColor) {
            color.children[0].checked = true;
            find = true;
        }
    }

    if (!find) {

        let rgb = bannerColor.match(/\d+/g);
        let bannerColorHex = "#" + rgb.map((x) => {
            x = parseInt(x, 10).toString(16);
            return (x.length === 1) ? "0" + x : x;
        }).join("");

        field.radioColorPicker.checked = true;
        field.radioColorPicker.value = bannerColorHex;
        field.colorPicker.value = bannerColorHex;
        field.colorPicker.parentElement.style.backgroundColor = bannerColorHex;
    }

}

/**
 * Update the preview with the characteristics of the person
 */
function updateCharacteristicPreview() {

    let characteristicsCounter = 0

    for (let i = 0; i < field.characteristics.children.length; i++) {
        let characteristic = field.characteristics.children[i];
        let visibility = characteristic.querySelector("input[type=checkbox]");

        if (visibility.checked && characteristicsCounter >= 4) {
            visibility.checked = false;
            triggerErrorPopup(["Vous ne pouvez pas avoir plus de 4 réseaux sociaux affichés"]);
        } else if (visibility.checked) {
            preview.characteristicType[i].classList.remove('social-network--invisible')
            characteristicsCounter++;
        } else {
            preview.characteristicType[i].classList.add('social-network--invisible')
        }
    }

}

/**
 * Update the card preview with the values of the form fields
 */
function updatePreview() {
    preview.bio.textContent = field.bio.value;

    if(field.sync()){
        field.about.value = field.bio.value;
    }

    let bannerColor = field.colors.querySelector("input:checked").parentElement.style.backgroundColor

    preview.color.style.backgroundColor = bannerColor;
    pictureModal.imagePreview.style.backgroundColor = bannerColor;

    if (field.lastName && field.firstName) {
        preview.lastName.textContent = field.lastName.value;
        preview.firstName.textContent = field.firstName.value;
    }

    updateCharacteristicPreview();
}

preview.picture.addEventListener("mouseover", () => {
    preview.overlayPicture.classList.add("overlay--active");
    preview.picture.classList.add("overlay--active");
})

preview.picture.addEventListener("mouseout", () => {
    preview.overlayPicture.classList.remove("overlay--active");
    preview.picture.classList.remove("overlay--active");
})

/**
 * Open the picture modal and add event listeners to close it
 */
preview.picture.addEventListener("click", (event) => {

    pictureModal.container.classList.add("edit-picture--active");
    event.stopPropagation();

    pictureModal.closeButton.addEventListener("click", closeByButton);
    document.addEventListener("keydown", closeByKey);
    document.addEventListener("click", closeByClick);


    function closeByButton(e) {
        e.preventDefault();
        pictureModal.container.classList.remove("edit-picture--active");
        deleteEventListener();
    }

    function closeByKey(e) {
        if (e.key === "Escape") {
            pictureModal.container.classList.remove("edit-picture--active");
            deleteEventListener();
        }
    }

    function closeByClick(e) {
        if (!e.composedPath().includes(pictureModal.container)) {
            pictureModal.container.classList.remove("edit-picture--active");
            deleteEventListener();
        }
    }

    function deleteEventListener() {
        document.removeEventListener("keydown", closeByKey);
        document.removeEventListener("click", closeByClick);
        document.removeEventListener("click", closeByButton);
    }
});

/**
 * Syncronize the picture preview with the picture uploader
 */
field.picture.addEventListener("input", () => {

    let reader = new FileReader();
    let picture = field.picture.files[0];

    if (picture.size > 5_000 * 1024) {

        // temporary alert, will be replaced by a modal
        alert("Attention, la taille de l'image ne doit pas dépasser 5Mo");

        pictureModal.imageUploader.value = "";
        pictureModal.imagePreview.src = initialPicture;
        preview.picture.src = initialPicture;

        return;
    }

    reader.readAsDataURL(picture);
    reader.onload = function (e) {
        pictureModal.imagePreview.src = e.target.result;
        preview.picture.src = e.target.result;
    };
});

field.colorPicker.addEventListener("input", e => {
    field.radioColorPicker.checked = true;
    field.radioColorPicker.value = e.target.value;
    e.target.parentElement.style.backgroundColor = e.target.value;
});

action.download.addEventListener("click", async e => {

    e.preventDefault();

    let request = await fetch(action.download.href, {
        method: "GET",
    });

    if (request.ok) {

        let response = await request.json();

        if (response.code === 200) {

            const file = new Blob([response.content], {type: 'application/json'});

            let content = JSON.parse(response.content);

            let link = document.createElement("a");
            link.href = URL.createObjectURL(file);
            link.download = `${content.identity.firstName} ${content.identity.lastName} - ${Date.now()}.json`;
            link.click();

            triggerSuccessPopup(response.messages);
        } else {
            triggerErrorPopup(response.messages);
        }

    } else {
        triggerErrorPopup(["Une erreur est survenue lors de la requête au serveur"]);
    }
});

action.delete?.addEventListener("click", async e => {

    e.preventDefault();

    if (!e.target.classList.contains("sure")) {
        e.target.classList.add("sure");
        setTimeout(() => {
            e.target.textContent = "Êtes-vous sûr ?";
        }, 200);
        return;
    }

    let request = await fetch(window.location.href, {
        method: "DELETE",
    });

    if (request.ok) {

        let response = await request.json();

        if (response.code === 200) {
            triggerSuccessPopup(response.messages);

            setTimeout(() => {
                window.location.href = response.redirect;
            }, response.redirectDelay);

        } else {
            triggerErrorPopup(response.messages);
        }

    } else {
        triggerErrorPopup(["Une erreur est survenue lors de la requête au serveur"]);
    }
});

action.invert.addEventListener("click", () => {
    let tmp = field.bio.value;
    field.bio.value = field.about.value;
    field.about.value = tmp;

    updatePreview();
});

action.sync.addEventListener("click", async () => {
    if(action.sync.classList.toggle("active")){

        const isEmpty = field.about.value === "";
        const isSimilar = field.about.value === field.bio.value;

        console.log(isEmpty, isSimilar);

        if (!(isEmpty || isSimilar) && !confirm("Attention ! Vous risquez de perdre le contenu du champ 'A PROPOS'")){
            action.sync.classList.remove("active");
            return;
        }

        field.about.readOnly = true;
        updatePreview();
        return;
    }

    field.about.readOnly = false;
});

init();

document.addEventListener("input", updatePreview);
