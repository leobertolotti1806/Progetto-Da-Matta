<script setup>
import { toSlashDate, postData } from "~/assets/script";

const selectedDate = ref(new Date().toISOString().slice(0, 10)); // default oggi
const isLoading = ref(true);
const payments = ref([]), totaleEntrate = ref(0), totaleRimborsi = ref(0), totaleNetto = ref(0), totaleAltro = ref(0);

watch(selectedDate, () => {
    load();
});

load();

function load() {
    isLoading.value = true;
    payments.value = [];

    totaleEntrate.value = 0;
    totaleNetto.value = 0;
    totaleRimborsi.value = 0;
    totaleAltro.value = 0;

    document.body.classList.add("cursorWait");
    postData(`/getDailyPagamenti`, {
        date: selectedDate.value
    }).then(function (esito) {
        totaleEntrate.value = 0;
        totaleNetto.value = 0;
        totaleRimborsi.value = 0;
        totaleAltro.value = 0;

        if (!esito.ok) {
            alert(esito.msg);
        } else {
            payments.value = esito.payments;

            for (const p of payments.value) {
                if (p.Status == "ACCEPTED") {
                    if (p.Tipo == "E") {
                        totaleEntrate.value += parseInt(p.Importo);
                    } else {
                        totaleRimborsi.value += parseInt(p.Importo);
                    }
                } else {
                    totaleAltro.value += parseInt(p.Importo);
                }
            }

            totaleNetto.value = totaleEntrate.value - totaleRimborsi.value;

            isLoading.value = false;
        }
        document.body.classList.remove("cursorWait");
    });
}
</script>

<template>
    <div class="pagamenti-wrapper">
        <div class="filters">
            <div class="date-refresh-wrapper">
                <MyInput v-model="selectedDate" type="date" label="Seleziona giorno" class="date-picker" />
                <button class="refresh-btn" @click="load()" title="Aggiorna">
                    <v-icon class="icon">mdi-refresh</v-icon>
                </button>
            </div>
            <div class="totali">
                <div class="tot-box e">
                    💰 Entrate del {{ toSlashDate(selectedDate) }}: <h2>{{ totaleEntrate.toFixed(2) }} €</h2>
                </div>
                <div class="tot-box r">
                    🔻 Rimborsi del {{ toSlashDate(selectedDate) }}: <h2>{{ totaleRimborsi.toFixed(2) }} €</h2>
                </div>
                <div class="tot-box netto">
                    ⚖️ Netto (entrate - rimborsi)<h2>{{ totaleNetto.toFixed(2) }} €</h2>
                </div>
                <div class="tot-box altroStatus">
                    🔄 (Cancellati / in attesa)<h2>{{ totaleAltro.toFixed(2) }} €</h2>
                </div>
            </div>
        </div>

        <div v-if="isLoading">
            <h1>Caricamento...</h1>
        </div>
        <div class="table-responsive" v-if="!isLoading && payments.length > 0">
            <table>
                <thead>
                    <tr>
                        <th>Iscritto</th>
                        <th>Cellulare</th>
                        <th>Evento</th>
                        <th>Importo</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="p in payments" :key="p.id">
                        <td>{{ p.Iscritto }}</td>
                        <td>{{ p.Cellulare }}</td>
                        <td>{{ p.Evento.Titolo }} {{ toSlashDate(p.Evento.Data) }}</td>
                        <td class="importo-cell">
                            <span class="importo">
                                <v-icon class="icon"
                                    :color="p.Status !== 'ACCEPTED' ? '#2f3208' : (p.Tipo === 'E' ? 'green' : 'red')">
                                    {{ p.Status !== 'ACCEPTED' ? 'mdi-cash-lock' : (p.Tipo === 'E' ? 'mdi-cash-check' :
                                    'mdi-cash-refund') }}
                                </v-icon>


                                <span :class="p.Status == 'ACCEPTED' ? p.Tipo.toLowerCase() : 'altroColor'">
                                    &nbsp;{{ parseInt(p.Importo).toFixed(2) }}€
                                </span>

                                <span v-if="p.Tipo === 'R' || p.Status !== 'ACCEPTED'" class="rimborso">
                                    {{p.Tipo === 'R' ? '(RIMBORSO)' : p.Status == 'PENDING' ? '(IN ATTESA)' : p.Status}}</span>
                            </span>

                            <!-- <span v-if="p.Tipo === 'ENTRATA'" class="entrata">➡️ +{{ p.Importo.toFixed(2) }} €</span>
                            <span v-else class="uscita">⬅️ -{{ p.Importo.toFixed(2) }} € (RIMBORSO)</span> -->
                        </td>
                        <td>{{ new Date(p.Data).toLocaleString("it-IT", {
                            day: "2-digit",
                            month: "2-digit",
                            year: "numeric",
                            hour: "2-digit",
                            minute: "2-digit",
                            hour12: false
                        }) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="!isLoading && payments.length == 0">
            <h1>In questa data non sono presenti pagamenti!</h1>
        </div>
    </div>
</template>

<style scoped>
.date-refresh-wrapper {
    display: flex;
    align-items: center;
    column-gap: 16px;
}

.importo{
    font-weight: bold;
}

.refresh-btn {
    padding: 8px 12px;
    border-radius: 6px;
    border: none;
    color: #333;
    margin-top: 26px;
    background-color: #F0F0F0 !important;
    cursor: pointer;
    transition: background 0.3s;
}

.refresh-btn:hover {
    background-color: #007341;
}

h2 {
    color: inherit;
}

:deep(input) {
    max-width: 400px;
}

.cursorWait .pagamenti-wrapper {
    opacity: 0.6 !important;
    cursor: not-allowed;
}

.pagamenti-wrapper {
    display: flex;
    flex-direction: column;
    width: 100%;
    gap: 25px;
    padding: 25px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    font-family: Arial, sans-serif;
}

.filters {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.date-picker {
    min-width: 180px;
}

.totali {
    display: flex;
    gap: 15px;
    width: 100%;
    flex-wrap: wrap;
}

.tot-box {
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
    font-size: 16px;
}

.e {
    background: #e8f8ef;
    color: #006b2d;
}

.altroStatus {
    background-color: #FFFFCC;
    color: #2f3208;
}

tr .e,
tr .r {
    background: unset;
}

.altroColor{
 color: #2f3208;   
}

tr:nth-child(even) {
    background-color: #FAFAFA !important;
}

.r {
    background: #fdeaea;
    color: #b00020;
}

.netto {
    background: #F3F1ED;
    color: #583627;
}

.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
}

th,
td {
    padding: 14px 12px;
    text-align: center;
    border-bottom: 1px solid #eee;
}

thead th {
    background: #f5f5f5;
    font-weight: 600;
}

tr.entrata {
    background: #f6fff9;
}

tr.uscita {
    background: #fff8f8;
}

.importo-cell .entrata {
    color: #006b2d;
    font-weight: bold;
}

.importo-cell .uscita {
    color: #b00020;
    font-weight: bold;
}

@media (max-width:768px) {

    th,
    td {
        font-size: 13px;
        padding: 10px 6px;
    }

    .tot-box {
        font-size: 14px;
        padding: 10px 15px;
    }

    .filters {
        flex-direction: column;
        align-items: flex-start;
    }

    .totali {
        justify-content: center;
    }

    .pagamenti-wrapper {
        padding: 25px 8px;
    }
}

@media (max-width:480px) {
    td {
        font-size: 10.5px;
    }

    .rimborso {
        display: none;
    }

    td {
        padding: 10px 0;
    }


    .pagamenti-wrapper {
        padding: 25px 0px;
    }
}
</style>
