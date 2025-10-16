<script setup>
import { camelToSpaced, postData, toSlashDate } from '@/assets/script'

const props = defineProps({
    data: Object
});

const emit = defineEmits(["updateTitolo"]);
const titolo = ref(props.data.titolo);
const endpoint = ref(props.data.endpoint);
const name = ref(props.data.name);
const showBtnSub = ref(props.data.showBtnSub ?? false);

const showModal = ref(false);
const elementi = ref([])
const search = ref("");
const sortBy = ref("");
const sortDir = ref("");
const page = ref(1);
const perPage = 20;
const colonne = ref(null);
const idToPass = ref(null);
const idEventoSoloXIscrizioniModal = ref(null);
const mode = ref(null);
load();

function load() {
    document.body.classList.add("cursorWait");
    postData(`/getAdmin${endpoint.value}`, {
        search: search.value,
        sortBy: sortBy.value,
        sortDir: sortDir.value,
        limit: perPage,
        offset: (page.value - 1) * perPage
    }).then(function (esito) {
        if (!esito.ok) {
            alert(esito.msg);
        } else {
            if (esito.data.length > 0) {
                if (page.value === 1) {
                    elementi.value = esito.data;
                } else {
                    elementi.value.push(...esito.data);
                }

                colonne.value = Object.keys(elementi.value[0]).filter(el => el !== "Id");
            } else {
                elementi.value = [];
            }
        }
        document.body.classList.remove("cursorWait");
    });
}

function ordina(col) {
    if (sortBy.value === col) {
        sortDir.value = sortDir.value === "ASC" ? "DESC" : "ASC";
    } else {
        sortBy.value = col;
        sortDir.value = "ASC";
    }
    page.value = 1;
    load();
}

function cerca() {
    page.value = 1;
    load();
}

function avantiPagina() {
    if ((page.value * perPage) <= elementi.value.length) {
        page.value++;
        load();
    }
}

function modifica(id) {
    idToPass.value = id;
    mode.value = "Modifica";
    showModal.value = true;
}

async function elimina(id) {
    if (confirm("Sei sicuro di voler eliminare?")) {

        let params = { id };

        if (titolo.value == "Eventi") {
            if (!confirm("Eliminando l'evento verranno eliminati anche TUTTI gli iscritti relativi a quell'evento. Continuare?")) {
                return;
            }
        } else if (titolo.value == "Iscrizioni") {
            if (elementi.value.find(el => el.Id == id).MetodoPagamento == "Satispay") {
                params.rimborso = confirm("Stai per eliminare un'iscrizione fatta su satispay.\nSchiaccia Ok per rimborsare.\nSchiaccia Annulla per NON rimborsare.")
            }
        }

        document.body.classList.add("cursorWait");
        let esito = await postData("/del" + name.value, params);
        document.body.classList.remove("cursorWait");

        if (!esito.ok) {
            alert(esito.msg);
        } else {
            load();
            alert("Azione avvenuta con successo");
        }
    }
}

function sub(evento) {
    showBtnSub.value = false;
    titolo.value = "Iscrizioni";
    name.value = "Iscrizione";
    endpoint.value = `Iscrizioni?costo=${evento.Costo}&idEvento=${evento.Id}`;
    elementi.value = [];
    idEventoSoloXIscrizioniModal.value = evento.Id;

    search.value = "";
    sortBy.value = "";
    sortDir.value = "";
    page.value = 1;

    load();

    emit("updateTitolo", "Iscritti a " + evento.Titolo);
}

function aggiungi() {
    showModal.value = true;
    mode.value = "Aggiungi";
}

function renderizza(text, col) {
    let t;

    if (col == "Costo") {
        if (titolo.value == "Vini") {
            if (text.includes("-")) {
                const [prezzo, offerta] = text.split("-");
                t = `<div class="offerta-vini">
                        <div>${prezzo}€</div>
                        <div>${offerta}€</div>
                     </div>`;
            } else {
                t = text + "€";
            }
        } else if (text == "0.00") {
            t = "GRATIS";
        } else {
            t = text + "€";
        }
    } else if (titolo.value == "Vini" && (
        col == "Evidenzia" || col == "Offerta"
    )) {
        t = text ? "Sì✅" : "No❌";
    } else if (/^\d{4}-\d{2}-\d{2}$/.test(text)) {
        t = toSlashDate(text);
    } else if (titolo.value == "Iscrizioni" && col == "Data") {
        const date = new Date(text.replace(" ", "T"));
        t = date.toLocaleString("it-IT", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
            hour12: false
        });
    } else {
        t = text;
    }

    return t;
}

</script>
<template>
    <myAdminModal v-if="showModal" @close="showModal = false" @reload="load()" :mode="mode" :id="idToPass" :name="name"
        :idEvento="idEventoSoloXIscrizioniModal ?? null" />
    <div class="wrapper">
        <div class="top-bar">
            <div>
                <h2 class="table-title">{{ titolo }}</h2>
                <button class="btn-aggiungi" v-if="!showBtnSub" @click="aggiungi()">➕ Aggiungi</button>
            </div>
            <div>
                <MyInput placeholder="Cerca..." v-model="search" @invioPremuto="cerca()" />
                <button @click="cerca()" id="cerca">Cerca</button>
            </div>
        </div>

        <div class="table-responsive">
            <table v-if="elementi.length > 0" :class="titolo">
                <thead>
                    <tr>
                        <th v-for="col in colonne" :key="col" @click="ordina(col)">
                            {{ camelToSpaced(col) }}
                        </th>
                        <th>{{ showBtnSub ? "Vedi iscritti" : "Azioni" }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in elementi" :key="item.id">
                        <td v-for="col in colonne" :key="col" v-html="renderizza(item[col], col)">
                        </td>
                        <td class="actions" v-if="showBtnSub" style="max-width: unset;">
                            <div>
                                <button class="showBtnSub" @click="sub(item)">Vedi iscritti</button>
                            </div>
                        </td>
                        <td class="actions" v-else>
                            <div>
                                <button @click="modifica(item.Id)">✏️</button>
                                <button class="delete" @click="elimina(item.Id)">🗑️</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <h1 v-else>Nessun risultato per ' {{ search }} '</h1>
        </div>

        <div v-if="(page * perPage) <= elementi.length" class="loadMoreDiv">
            <button @click="avantiPagina">
                <v-icon class="icon" style="margin-right: 8px;">mdi-arrow-down</v-icon>
                Carica altri
            </button>
        </div>
    </div>
</template>
<style scoped>
.wrapper {
    display: flex;
    flex-direction: column;
    flex: 1;
    row-gap: 3vh;
    padding: 20px;
    border-radius: 12px;
    background-color: #fff;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
}

.loadMoreDiv,
.loadMoreDiv button {
    display: flex;
    align-items: center;
    justify-content: center;
}

.loadMoreDiv button {
    padding: 15px 25px !important;
    min-width: fit-content;
}

:deep(.offerta-vini)>div:first-child {
    text-decoration: line-through;
    color: rgba(51, 51, 51, 0.8);

}

:deep(.offerta-vini) {
    color: #c40000;
}

.table-title {
    font-size: 25px;
    color: #333;
}

.table-responsive {
    width: 100%;
    overflow-x: auto;
    flex: 1;
}

.table-responsive>h1 {
    font-size: 35px;
    padding: 2vh 2vw;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
}

th,
td {
    text-align: center;
    padding: 12px 0.8%;
    border-bottom: 1px solid #eee;
    width: 100px;
}

tr:nth-child(even) {
    background-color: #FAFAFA;
}

th {
    font-weight: 600;
    color: #444;
    height: 67px;
    cursor: pointer;
    user-select: none;
    background-color: #efefef;
}

tbody tr {
    height: 110px;
}

button {
    padding: 6px 12px;
    font-size: 14px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    cursor: pointer;
    background: #a1864f;
    width: 50px;
    box-sizing: border-box;
    color: white;
    transition: background 0.3s;
}

#cerca,
.btn-aggiungi {
    width: fit-content;
    padding: 8px 16px;
}

.top-bar {
    display: flex;
    flex-direction: column;
    flex-wrap: wrap;
    row-gap: 30px;
}

.top-bar>div {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}


.top-bar>div:last-child>div:first-child {
    flex: 1;
}

#cerca {
    min-width: 10%;
}

.btn-aggiungi,
.showBtnSub {
    border-radius: 6px;
    border: none;
    width: fit-content !important;
    background-color: #008c4f;
    color: white;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-aggiungi:hover,
.showBtnSub:hover {
    background-color: #007341;
}

button:hover {
    background: #8c7540;
}

button.delete {
    background: #b00020;
}

button.delete:hover {
    background: #a00018;
}

.actions,
th:last-of-type {
    max-width: 50px;
}

th:last-of-type {
    padding: 0;
}

.actions>div {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 10px;
}


@media screen and (max-width: 1024px) {
    .wrapper {
        padding: 0;
        box-shadow: none;
    }

    td,
    th {
        font-size: 14px;
        padding: 12px 0.5%;
    }
}

@media (max-width: 768px) {
    table {
        min-width: 100%;
    }

    .top-bar {
        padding: 0 10px;
    }

    h2 {
        text-align: center;
    }

    .actions {
        padding: 10px;
        width: fit-content;
    }

    :deep(.actions button) {
        width: 100%;
    }

    th,
    td {
        font-size: 12.5px;
        padding: 12px 0.5%;
    }

    .title {
        width: 80px;
    }
}

@media (max-width:480px) {
    th {
        font-size: 11px;
        padding: 10px 0 !important;
    }

    td,
    .actions button {
        font-size: 10px;
    }

    th,
    td {
        padding: 0;
        max-width: 75px;
    }

    .actions,
    thead th:last-of-type {
        padding: 0 5px 0 0;
    }
}

@media (max-width:380px) {
    th {
        font-size: 9px;
    }

    td,
    .actions button {
        font-size: 8px;
    }

    .top-bar>div:last-child>div:first-child {
        max-width: 60%;
    }
}
</style>