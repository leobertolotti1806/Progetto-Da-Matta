<script setup>
import { postFormData } from "~/assets/script";

const props = defineProps({
    page: String
});
const page = props.page;

const showModal = ref(false);

const paragraphId = ref(null);
const reloadCount = ref(0);
const titolo = page.toLowerCase();

const selectedpage = defineAsyncComponent(() => import(`~/pages/${titolo == "events" ? "eventi" : titolo}.vue`));

const images = ref([]);

if (titolo == "Events") {
    images.value = ["/img/event0.webp"];
} else {
    images.value = [
        `/img/${titolo}/${titolo}0.webp`,
        `/img/${titolo}/${titolo}1.webp`,
        `/img/${titolo}/${titolo}2.webp`,
        `/img/${titolo}/${titolo}3.webp`,
        `/img/${titolo}/${titolo}4.webp`
    ];
}

let savedImg = [];

// Funzione per cambiare un'immagine
function changeImage(key) {
    const input = document.createElement("input");
    input.type = "file";
    input.accept = "image/*";
    input.onchange = (e) => {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();

        reader.onload = (ev) => {
            images.value[key] = ev.target.result;
            savedImg[`${titolo}${key}`] = file;
        };

        reader.readAsDataURL(file);
    };
    input.click();
}

function changeParagraph(e) {
    paragraphId.value = e;
    setTimeout(() => {
        showModal.value = true;
    }, 0);
}

// Salvataggio finale
async function saveChanges() {
    document.body.classList.add("cursorWait");

    let esito = await postFormData(`/mod${page}Images`, {}, savedImg);

    document.body.classList.remove("cursorWait");
    if (!esito.ok) {
        alert(esito.msg ?? "Errore sconosciuto (codice 6969)");
    } else {
        alert("Azione avvenuta con successo!");
    }

}
</script>

<template>
    <div class="admin-preview">

        <div class="save-box">
            <h2>Qui puoi modificare paragrafi e immagini relativi alla pagina {{ page == "About" ? "Visitaci" :
                page }}</h2>
            <br>
            <p>Per confermare le tue scelte premi il bottone in fondo a questa pagina</p>
            <br>
            <p>(Per modifica le immagini fare doppio click)</p>
            <p>(Per modifica i paragrafi basta cliccare)</p>
        </div>

        <!-- ANTEPRIMA PAGINA -->
        <div class="page-preview">
            <!-- Passiamo le immagini alla Home -->
            <selectedpage :images="images" @changeImage="changeImage" @changeParagraph="changeParagraph"
                :editMode="true" :key="reloadCount" />
        </div>

        <!-- FINESTRA DI CONFERMA -->
        <div class="save-box">
            <p>Hai finito di modificare la pagina.</p>
            <button @click="saveChanges" class="btn">Salva Modifiche</button>
        </div>
    </div>
    <myAdminModal v-if="showModal" :id="paragraphId" :mode="'Modifica'" :name="page" @close="showModal = false"
        @reload="reloadCount++" />
</template>
<style scoped>
.save-box:first-child p {
    font-size: 18px;
    margin: 0;
}

.admin-preview {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 30px;
}

h2,
p {
    color: #fff;
}

/* Anteprima pagina */
.page-preview {
    width: 100%;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

/* Box salvataggio */
.save-box {
    color: #fff !important;
    width: 100%;
    text-align: center;
    background: #2c3e50;
    border-radius: 12px;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.07);
    padding: 35px 40px;
}

.save-box p {
    font-size: 16px;
    margin-bottom: 20px;
}

/* Pulsante coerente con dashboard/myadmintable */
.save-box .btn {
    background: #800000;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 12px 25px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease;
}

.save-box .btn:hover {
    background: #a00000;
}
</style>