<script setup>
import { getData, getIdKeyAsArrayKey, postData, postFormData, toSelectOptions } from '~/assets/script';

const emit = defineEmits(["close", "reload"]);
const props = defineProps({
    mode: String,
    id: { Type: Number, default: null },        // obbligatorio (a quale ID si riferisce)
    name: String,
    idEvento: { Type: Number, default: null },        // SERVE SOLO PER AGGIUNTA ISCRIIZONE
});

const dato = ref({});
const changedImage = ref(false);
const paragraph = ref("");
const editMode = props.mode == "Modifica";
const addMode = props.mode == "Aggiungi";
const titolo = props.name;
const showFieldFromLabel = ref(false);
const isParagraph = titolo == 'Home' || titolo == 'About' || titolo == 'Events';

if (editMode) {
    if (titolo != "Loghi") {
        getData(getRightEndopoint()).then(function (data) {
            if (!data.ok) {
                alert(data.msg ?? "ERRORE 222");
            } else {

                if (isParagraph) {
                    dato.value = data.paragrafo;
                    paragraph.value = fromHtml(data.paragrafo.Testo);

                } else if (titolo == "Contatti") {
                    dato.value = getIdKeyAsArrayKey(data.paragrafi);
                } else {
                    dato.value = data[titolo.toLowerCase()];
                }

                if (titolo == "Vino") {
                    showFieldFromLabel.value = dato.Offerta == null ? false : true;
                }
            }
        });
    } else {
        dato.value.loghi0 = null;
        dato.value.loghi1 = null;
    }
}

onMounted(() => {
    const handleKey = (e) => {
        if (e.key === "Escape") {
            close();
        }
    };

    window.addEventListener("keydown", handleKey);
    onBeforeUnmount(() => {
        window.removeEventListener("keydown", handleKey);
    });
});


function getRightEndopoint() {
    let endpoint;

    if (isParagraph || titolo == "Contatti") {
        endpoint = `/getParagraphs?page=${(titolo == "Contatti" ? "footer" : titolo.toLowerCase())}&id=${props.id}`;
    } else {
        endpoint = `/get${titolo}?id=${props.id}`;
    }

    return endpoint;
}

function toHtml(input) {
    // Escape base per sicurezza
    let text = input
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");

    // Bold: *testo* → <b>testo</b>
    text = text.replace(/\*(.*?)\*/g, "<b>$1</b>");

    // Italic: _testo_ → <i>testo</i>
    text = text.replace(/_(.*?)_/g, "<i>$1</i>");

    // Se contiene invii a capo → dividi in paragrafi
    if (text.includes("\n")) {
        const parts = text.split(/\r?\n/).map(line => line.trim()).filter(Boolean);
        return parts.map(line => `<p>${line}</p>`).join("\n");
    } else {
        return `<p>${text.trim()}</p>`;
    }
}

function fromHtml(html) {
    // 1. Sostituisco <b>...</b> con *...*
    let text = html?.replace(/<b>(.*?)<\/b>/gi, '*$1*');

    // 2. Sostituisco <i>...</i> con _..._
    text = text?.replace(/<i>(.*?)<\/i>/gi, '_$1_');

    // 3. Sostituisco i paragrafi <p>...</p> con invii a capo
    // rimuovo eventuali spazi iniziali/finali e sostituisco con \n
    text = text?.replace(/<p>(.*?)<\/p>/gi, '$1\n');

    // 4. Rimuovo eventuali spazi iniziali/finali in eccesso
    text = text?.trim();

    return text;
}

async function send() {
    let endpoint = "/" + (addMode ? "add" : "mod") + (
        titolo == "Loghi" ? "LoghiImages" : titolo
    );

    let file = null;
    let { myFile, ...params } = Object.assign({}, dato.value);

    if (editMode) {
        if (isParagraph) {
            params = {};
            params.testo = toHtml(paragraph.value);
        } else if (changedImage.value) {
            if (titolo == "Loghi") {
                console.log(dato.value);

                if (dato.value.loghi0 != null) {
                    file = {};
                    file.loghi0 = dato.value.loghi0;

                    if (dato.value.loghi1 != null) {
                        file.loghi1 = dato.value.loghi1;
                    }
                } else if (dato.value.loghi1 != null) {
                    file = {};
                    file.loghi1 = dato.value.loghi1;
                }
                console.log("FILEEEE => ", file);
            } else {
                file = dato.value.myFile;
                params.changeImage = true;
            }
        }

        params.Id = props.id;
    } else if (addMode) {
        if (titolo == "Iscrizione") {
            params.IdEvento = props.idEvento;
            params.forzaPosti = true;

            if (dato.MetodoPagamento != 'S' && dato.PaymentId) {
                delete params.PaymentId;
            }

            if (dato.PaymentId) {
                params.PaymentId = params.PaymentId.replaceAll(" ", "-");
            }
        } else {
            file = myFile;
        }
    }

    document.body.classList.add("cursorWait");
    let esito = await (
        (titolo == "Evento" || titolo == "Vino" || titolo == "Loghi")
            ? postFormData(endpoint, params, file)
            : postData(endpoint, params)
    );
    document.body.classList.remove("cursorWait");

    if (!esito.ok) {
        alert(esito.msg);
    } else {
        emit("reload");
        alert("Azione eseguita con successo!");
        close();
    }
}

function close() {
    emit("close");
}
</script>
<template>
    <div class="modal-overlay">
        <div class="modal-box">
            <h2>{{ props.mode }} {{ isParagraph ? "Paragrafo" : titolo }}</h2>
            <form @submit.prevent="send()">
                <div v-if="titolo == 'Iscrizione'">
                    <MyInput label="Nome" placeholder="Nome" v-model="dato.Nome" required
                        :default-value="editMode ? dato?.Nome : null" />

                    <MyInput label="Cognome" placeholder="Cognome" v-model="dato.Cognome" required
                        :default-value="editMode ? dato?.Cognome : null" />

                    <MyInput label="Cellulare" placeholder="Cellulare" type="tel" v-model="dato.Cellulare"
                        :default-value="editMode ? dato?.Cellulare : null" />

                    <MyInput label="Come ha pagato" placeholder="Metodo di pagamento" type="select" :data="[
                        { text: 'Satispay', value: 'S' },
                        { text: 'Contanti', value: 'C' },
                        { text: '/', value: '/' }
                    ]" v-model="dato.MetodoPagamento" required
                        :default-value="editMode ? dato?.MetodoPagamento : null" />

                    <MyInput v-if="dato.MetodoPagamento == 'S'"
                        label="Codice pagamento (andare sulla transazione -> ricevuta -> UID pagamento)"
                        placeholder="Codice pagamento" v-model="dato.PaymentId" required />

                    <MyInput label="Data" type="datetime-local" v-model="dato.Data"
                        :default-value="editMode ? dato?.Data : new Date().toISOString().slice(0, 16)" required />
                </div>

                <div v-else-if="titolo == 'Evento'">
                    <MyInput label="Titolo" placeholder="Titolo" v-model="dato.Titolo" required
                        :default-value="editMode ? dato?.Titolo : null" />

                    <MyInput label="Descrizione" placeholder="Descrizione" type="textarea" v-model="dato.Descrizione"
                        required :default-value="editMode ? dato?.Descrizione : null" />

                    <MyInput label="Data" type="date" v-model="dato.Data"
                        :default-value="editMode ? dato?.Data : null" />

                    <MyInput label="Scadenza iscrizione (se vuota sarà la data dell'evento)" type="date"
                        v-model="dato.ScadenzaIscrizione" :default-value="editMode ? dato?.ScadenzaIscrizione : null" />

                    <MyInput label="Ora d'inizio" type="time" v-model="dato.OraInizio" required=""
                        :default-value="editMode ? dato?.OraInizio : null" />

                    <MyInput label="Ora di fine" type="time" v-model="dato.OraFine" required=""
                        :default-value="editMode ? dato?.OraFine : null" />

                    <MyInput label="Citta" placeholder="Citta" v-model="dato.Citta"
                        :default-value="editMode ? dato?.Citta : 'Busca'" />

                    <MyInput label="Indirizzo" placeholder="Indirizzo" v-model="dato.Indirizzo"
                        :default-value="editMode ? dato?.Indirizzo : `Via Roberto d'azeglio 13, 12022 Busca`" />

                    <MyInput type="image" label="Immagine (consigliato in formato 9 : 16)" v-model="dato.myFile"
                        :required="addMode" :default-value="editMode ? '/event/' + props.id : null"
                        @changed-image="changedImage = true" />

                    <MyInput label="Posti totali" type="number" placeholder="Posti disponibili"
                        v-model="dato.PostiTotali" required :default-value="editMode ? dato?.PostiTotali : null" />

                    <MyInput label="Prezzo" type="price" placeholder="Prezzo (10.99)" v-model="dato.Costo" required
                        :default-value="editMode ? dato?.Costo : null" />
                </div>

                <div v-else-if="titolo == 'Vino'">
                    <MyInput label="Nome" placeholder="Nome" v-model="dato.Nome"
                        :default-value="editMode ? dato?.Nome : null" required />

                    <MyInput label="Marca" placeholder="Marca" v-model="dato.Marca"
                        :default-value="editMode ? dato?.Marca : null" required />

                    <MyInput label="Descrizione" placeholder="Descrizione" type="textarea" v-model="dato.Descrizione"
                        :default-value="editMode ? dato?.Descrizione : null" required />

                    <MyInput label="Colore" placeholder="Colore" type="select" :data="toSelectOptions([
                        'Rosso', 'Bianco', 'Rosato', 'Arancione', 'Grigio'
                    ])" v-model="dato.Colore" :default-value="editMode ? dato?.Colore : null" required />

                    <MyInput label="Anno" placeholder="Anno" type="number" v-model="dato.Anno"
                        :default-value="editMode ? dato?.Anno : null" required />

                    <MyInput label="Regione" placeholder="Regione" v-model="dato.Regione"
                        :default-value="editMode ? dato?.Regione : null" />

                    <MyInput label="Quantità in litri" placeholder="Quantità in litri" type="price"
                        v-model="dato.Quantita" :default-value="editMode ? dato?.Quantita : null" required />

                    <MyInput type="image" label="Immagine (consigliato 220 x 780)" v-model="dato.myFile"
                        :default-value="editMode ? '/vini/' + props.id : null" :required="addMode"
                        @changed-image="changedImage = true" />

                    <MyInput label="Prezzo" type="price" placeholder="Prezzo (10.99)" v-model="dato.Costo"
                        :default-value="editMode ? dato?.Costo : null" required />

                    <MyInput label="Prezzo in offerta" type="price"
                        :placeholder="editMode && dato.Offerta == null ? 'Nessun offerta inserita' : 'Se non in offerta lasciare vuoto'"
                        v-model="dato.Offerta" :default-value="editMode ? (dato.Offerta ?? null) : null" />

                    <MyInput label="Gradazione" type="price" placeholder="Gradazione (8.5)" v-model="dato.Gradazione"
                        :default-value="editMode ? (dato.Gradazione ?? null) : null" />

                    <MyInput label="Effervescenza" type="select" placeholder="Effervescenza"
                        v-model="dato.Effervescenza" :default-value="editMode ? dato?.Effervescenza : null" required
                        :data="toSelectOptions([
                            'Mosso', 'Frizzante', 'Spumante', 'Fermo'
                        ])" />

                    <MyInput label="Denominazione"
                        :placeholder="editMode && dato.Denominazione == null ? 'Nessuna denominazione inserita' : 'Se non si vuole inserire lasciare vuoto'"
                        v-model="dato.Denominazione" :default-value="editMode ? dato?.Denominazione : null" />

                    <MyInput label="Spuntare se è da evidenziare" type="checkbox" v-model="dato.Evidenzia"
                        placeholder="In evidenzia" :default-value="editMode ? dato?.Evidenzia : false" />
                </div>

                <div v-else-if="isParagraph">
                    <p>Per mettere in grassetto circondare il testo tra _ : *<b>testo</b>*</p>
                    <p>Per mettere in corsivo circondare il testo tra _ : _<i>testo</i>_</p>

                    <MyInput label="Paragrafo" placeholder="Scrivi qui il paragrafo" type="textarea" v-model="paragraph"
                        required :default-value="fromHtml(dato.Testo)" class="paragraphTextArea" />
                </div>

                <!-- CONTATTI -->
                <div v-else-if="titolo == 'Contatti'">
                    <MyInput label="Nome cantina" placeholder="Nome cantina" v-model="dato[33]" required
                        :default-value="dato[33]" />

                    <MyInput type="tel" label="Numero di telefono" placeholder="Numero di telefono" v-model="dato[34]"
                        required :default-value="dato[34]" />

                    <MyInput type="mail" label="Email" placeholder="Email" v-model="dato[35]" required
                        :default-value="dato[35]" />

                    <MyInput label="Indirizzo" placeholder="Indirizzo" v-model="dato[38]" required
                        :default-value="dato[38]" />

                    <MyInput label="Link profilo Instagram" placeholder="Link profilo Instagram" v-model="dato[36]"
                        required :default-value="dato[36]" />

                    <MyInput label="Link profilo Facebook" placeholder="Link profilo Facebook" v-model="dato[37]"
                        required :default-value="dato[37]" />
                </div>

                <!-- LOGHI -->
                <div v-else>
                    <!-- LOGO MAIN -->
                    <MyInput type="image" label="Logo grande (consigliato in formato 16 : 9)" v-model="dato.loghi1"
                        :required="addMode" default-value="/loghi/loghi1" @changed-image="changedImage = true" />

                    <!-- ICONA -->
                    <MyInput type="image" label="Icona (consigliato in formato piccolo 1 : 1)" v-model="dato.loghi0"
                        :required="addMode" default-value="/loghi/loghi0" @changed-image="changedImage = true" />
                </div>

                <div class="btns">
                    <button type="submit">💾 Salva</button>
                    <button class="cancel" @click="close()" type="button">❌ Annulla</button>
                </div>
            </form>
        </div>
    </div>
</template>

<style scoped>
.paragraphTextArea {
    min-height: 350px;
}

:deep(.paragraphTextArea textarea) {
    min-height: 300px !important;
}

.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-box {
    background: white;
    padding: 30px;
    width: 90%;
    max-width: 600px;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    max-height: 90vh;
    overflow-y: auto;
}

h2 {
    font-size: 22px;
    color: #333;
    text-align: center;
}

form>div:first-child {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.btns {
    margin-top: 32px;
    display: flex;
    justify-content: space-between;
}

button {
    padding: 12px 20px;
    font-size: 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    background: #a1864f;
    color: white;
}

form>div:first-child>p {
    text-align: center;
}

button.cancel {
    background: #b00020;
}

button:hover {
    opacity: 0.9;
}
</style>
